<?php

namespace DeltaLab\CustomPixelQRCode\Renderers;

use DeltaLab\CustomPixelQRCode\CodeFrame;
use DeltaLab\CustomPixelQRCode\Renderers\RendererConfig;

abstract class CodeRenderer {

    protected $frame = false;
    protected $config = null;

    //##########################################################################

    public abstract function render();

    //##########################################################################

    public function __construct(CodeFrame $preProcessedFrame = null, RendererConfig $config = null)
    {
        if ($preProcessedFrame !== null) {
            $this->setFrame($preProcessedFrame);
        }
        if ($config !== null) {
            $this->setConfig($config);
        }
    }

    //--------------------------------------------------------------------------

    public function setFrame(CodeFrame $preProcessedFrame)
    {
        $this->frame = $preProcessedFrame;
    }

    //--------------------------------------------------------------------------

    public function setConfig(RendererConfig $config)
    {
        $this->config = $config;
    }

    //--------------------------------------------------------------------------

    public function renderToFile($outputFileName)
    {
        $image = $this->render();
        imagepng($image, $outputFileName);
        imagedestroy($image);
    }

}
