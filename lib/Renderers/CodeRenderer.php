<?php

namespace DeltaLab\CustomPixelQRCode\Renderers;

use DeltaLab\CustomPixelQRCode\Renderers\RendererConfig;

abstract class CodeRenderer {
    protected $frame = false;
    protected $frameSize = false;
    protected $config = null;

    //##########################################################################
    
    public abstract function render();
    
    //##########################################################################
    
    public function __construct($preProcessedFrame = false, RendererConfig $config = null)
    {
        if ($preProcessedFrame !== false) {
            $this->setFrame($preProcessedFrame);
        }
        if ($config !== null) {
            $this->setConfig($config);
        }
    }
    
    //--------------------------------------------------------------------------

    public function setFrame($preProcessedFrame)
    {
        $this->frame = $preProcessedFrame;
        $this->frameSize = count($this->frame);
    }

    //--------------------------------------------------------------------------
    
    public function setConfig(RendererConfig $config)
    {
        $this->config = $config;
    }

    //--------------------------------------------------------------------------
    
    public function renderToFile($outputFileName)
    {
        $target_image = $this->render();
        imagepng($target_image, $outputFileName);
        imagedestroy($target_image);
    }    
    
}
