<?php
	//<img src="http://indoconx.com/EmailCountdown/gif.php?time=2016-03-28+20:00:00" alt="Countdown" border="0" style="display:block;">

	$script = explode('/',$_SERVER['SCRIPT_NAME']);
	$mod = explode('/',urldecode($_SERVER['REQUEST_URI']),count($script));
	$mod = explode('/',array_pop($mod));
	
	$now = time();
	$list = json_decode(file_get_contents('list.json'),true);
	foreach ($list as $k => $v) if ($v < $now) unset($list[$k]);
	$list = array_values($list);
	
	if ($mod[0] == 'create')
	{
		if (!isset($mod[1])) $time = 0;
		else $time = strtotime($mod[1]);
		
		if ($time == 0) $time = strtotime(date('Y-m-d 23:59:59'));
		
		if (!in_array($time,$list)) $list[] = $time;
		file_put_contents('list.json',json_encode($list));
		
		$script[count($script)-1] = dechex($time).'.gif';
		header('Location: '.implode('/',$script));
		die;
	}
	elseif (preg_match('/^(\w+)\.gif$/',$mod[0],$match))
	{
		$time = hexdec($match[1]);
		if (!in_array($time,$list)) $time = 0;
	}
	elseif (isset($_GET['time']))
		$time = strtotime($_GET['time']);
	else $time = 0;
	
	file_put_contents('list.json',json_encode($list));
	$time = date('Y-m-d H:i:s',$time);
	
	//Leave all this stuff as it is
	include 'GIFEncoder.class.php';
	include 'php52-fix.php';
	// $time = $_GET['time'];
	$future_date = new DateTime(date('r',strtotime($time)));
	$time_now = time();
	$now = new DateTime(date('r', $time_now));
	$frames = array();	
	$delays = array();


	// Your image link
	$image = imagecreatefrompng('images/back3.png');

	$delay = 100;// milliseconds

	$font = array(
		'size' => 34, // Font size, in pts usually.
		'angle' => 0, // Angle of the text
		'x-offset' => 16, // The larger the number the further the distance from the left hand side, 0 to align to the left.
		'y-offset' => 50, // The vertical alignment, trial and error between 20 and 60.
		'file' => __DIR__ . DIRECTORY_SEPARATOR . 'Futura.ttc', // Font path
		'color' => imagecolorallocate($image, 255, 255, 255), // RGB Colour of the text
	);
	for($i = 0; $i <= 60; $i++){
		
		$interval = date_diff($future_date, $now);
		
		if($future_date < $now){
			// Open the first source image and add the text.
			$image = imagecreatefrompng('images/back3.png');
			;
			$text = $interval->format('00:00:00:00');
			imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $text );
			ob_start();
			imagegif($image);
			$frames[]=ob_get_contents();
			$delays[]=$delay;
			$loops = 1;
			ob_end_clean();
			break;
		} else {
			// Open the first source image and add the text.
			$image = imagecreatefrompng('images/back3.png');
			
			$text = str_pad($interval->format("%a"),2,'0',STR_PAD_LEFT);
			$text = $interval->format("$text %H %I %S");
			imagettftext ($image , $font['size'] , $font['angle'] , $font['x-offset'] , $font['y-offset'] , $font['color'] , $font['file'], $text );
			ob_start();
			imagegif($image);
			$frames[]=ob_get_contents();
			$delays[]=$delay;
			$loops = 0;
			ob_end_clean();
		}

		$now->modify('+1 second');
	}

	//expire this image instantly
	header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Cache-Control: post-check=0, pre-check=0', false );
	header( 'Pragma: no-cache' );
	$gif = new AnimatedGif($frames,$delays,$loops);
	$gif->display();
