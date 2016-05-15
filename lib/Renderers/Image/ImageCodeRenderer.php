<?php

namespace DeltaLab\CustomPixelQRCode\Renderers\Image;

use DeltaLab\CustomPixelQRCode\Renderers\CodeRenderer;

class ImageCodeRenderer extends CodeRenderer {

    public function __construct($preProcessedFrame = false, ImageRendererConfig $config = null)
    {
        parent::__construct($preProcessedFrame, $config);
    }
    
    //--------------------------------------------------------------------------
    
    public function render()
    {
        if ($this->config == null) {
            throw new Exception("Renderer config need to be set before rendering");
        }
    }
  
}
