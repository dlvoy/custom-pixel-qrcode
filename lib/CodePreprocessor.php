<?php

namespace DeltaLab\CustomPixelQRCode;

class CodePreprocessor {
	
	public function __construct($codeContents, $eclevel = QR_ECLEVEL_L)
    {
         $frame = \QRcode::raw($codeContents, false, QR_ECLEVEL_H); 
    }
	
}