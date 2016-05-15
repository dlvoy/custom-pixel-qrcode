<?php 

	require __DIR__ .  '/../vendor/autoload.php';

	use DeltaLab\CustomPixelQRCode\CodePreprocessor;
	
	define('EXAMPLE_TMP_SERVERPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR);
    define('EXAMPLE_TMP_SERVERURL', 'output/');

    // custom colorfull debug renderer 

    $codeContents = 'Let see what the code structure looks like with a little bit bigger code'; 
    $tempDir = EXAMPLE_TMP_SERVERPATH; 
    $fileName = 'demo.png'; 
    $outerFrame = 4; 
    $pixelPerPoint = 6; 
	
	// this is FOR SURE NOT SAFE in production app
	// make sure all user inputs are filtered!!!
	
	if (isset($_REQUEST['contents'])) {
		$codeContents = $_REQUEST['contents'];
		$fileName = 'demo_'.md5($codeContents).'.png';
	}
     
    // generating Raw frame 
    $frame = QRcode::raw($codeContents, false, QR_ECLEVEL_H); 
	$frameSize = count($frame);  
	
	$preProcessor = new CodePreprocessor($codeContents);

	//$newFrame = $frame;
	
	$markers = array();
	$subMarkers = array();
	
	function wipeSquare(&$arr, $fromX, $fromY, $size) {
		for ($y=$fromY;$y<$fromY+$size; $y++) {
			for ($x=$fromX;$x<$fromX+$size; $x++) {
				$arr[$y][$x] = "\x00";
			}
		}
	}

	$extendedFrame = array(str_repeat("\x01",$frameSize+2));
	for ($y=0;$y<$frameSize; $y++) {
		$extendedFrame[] = "\x01".$frame[$y]."\x01";
	}
	$extendedFrame[] = str_repeat("\x01",$frameSize+2);
	
	$exFrameSize = count($extendedFrame); 
	
	for ($y=0;$y<$exFrameSize; $y++) {
		for ($x=0;$x<$exFrameSize; $x++) {
			
			
			if ($extendedFrame[$y][$x] == "\xa1") {
				$subMarkers[] = array($x,$y);
				wipeSquare($extendedFrame, $x, $y, 5);
			}
			
			if (ord($extendedFrame[$y][$x]) > 1) {
				if (ord($extendedFrame[$y][$x]) % 2 == 0) {
					$extendedFrame[$y][$x] =  "\x02";
				} else {
					$extendedFrame[$y][$x] =  "\x03";
				}
			}
		}
	}
	
	wipeSquare($extendedFrame, 0, 0, 9);
	wipeSquare($extendedFrame, $exFrameSize-9, 0, 9);
	wipeSquare($extendedFrame, 0, $exFrameSize-9, 9);
	
	$frame = $extendedFrame;
	
	
	 
    // rendering frame with GD2 (that should be function by real impl.!!!) 
    $h = count($frame); 
    $w = strlen($frame[0]); 
     
    $imgW = $w + 2*$outerFrame; 
    $imgH = $h + 2*$outerFrame; 
     
    $base_image = imagecreate($imgW, $imgH); 
     
    $colorSpec = array( 
	    "\x00" => array(255,255,0),   // no data   
		"\x01" => array(255,0,0),     // border
        "\x02" => array(220,220,220), // 0      
        "\x03" => array(0,0,0),       // 1 
    ); 
     
    $colorLegend = array( 
		"\x00" => "no data - marker", 
		"\x01" => "border          ", 
        "\x02" => "data bit 0      ", 
        "\x03" => "data bit 1      ", 
    ); 
     
    $colBg = imagecolorallocate($base_image,255,255,255); // BG, white  
     
    foreach($colorSpec as $colorKey=>$colorDef) { 
        $colorBase[$colorKey] = imagecolorallocate( 
            $base_image,  
            $colorDef[0],  
            $colorDef[1],  
            $colorDef[2] 
        ); 
    } 
                                 
    imagefill($base_image, 0, 0, $colBg); 

    for($y=0; $y<$h; $y++) { 
        for($x=0; $x<$w; $x++) { 
            imagesetpixel( 
                $base_image, 
                $x+$outerFrame, 
                $y+$outerFrame, 
                $colorBase[$frame[$y][$x]] 
            );  
        } 
    } 
     
    // creating zoomed version 
    $target_image = imagecreate( 
        $imgW * $pixelPerPoint + 150,  
        max($imgH * $pixelPerPoint, 250) 
    ); 
     
    $coltBg   = imagecolorallocate($target_image, 255, 255, 255); // BG, white  
    $coltTxt  = imagecolorallocate($target_image, 0, 0, 0);       // TXT, black  
     
    foreach($colorSpec as $colorKey=>$colorDef) { 
        $colorTarget[$colorKey] = imagecolorallocate( 
            $target_image,  
            $colorDef[0],  
            $colorDef[1],  
            $colorDef[2] 
        ); 
    }  
     
    imagecopyresized( 
        $target_image,  
        $base_image,  
        0, 0, 0, 0,  
        $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW, $imgH 
    ); 
    imagedestroy($base_image); 
     
    $pos = 0; 
    foreach($colorLegend as $colKey=>$colName) { 
        $px = $imgW * $pixelPerPoint + 25; 
        $py = $outerFrame * $pixelPerPoint + $pos * 16; 
        imagefilledrectangle( 
            $target_image,  
            $px-20, $py+3,  
            $px-10, $py+13,  
            $colorTarget[$colKey] 
        ); 
        imagerectangle($target_image, $px-20, $py+3, $px-10, $py+13, $coltTxt); 
        imagestring($target_image, 2, $px, $py+1, $colName, $coltTxt); 
        $pos++; 
    } 
     
    imagepng($target_image, $tempDir.$fileName); 
    imagedestroy($target_image); 

    // display & input
    echo '<img src="'.EXAMPLE_TMP_SERVERURL.$fileName.'" />';
	echo '<br /><br /><br /><form action="index.php" method="post"><input style="width:40%;margin:0.5em;padding:0.25em;font-size:1.3em" type="text" name="contents" value="'.htmlentities($codeContents).'">';
	echo '<br /><input style="margin:0.5em" type="submit"></form>';
	