<?php

namespace DeltaLab\CustomPixelQRCode\Renderers\Debug;

use DeltaLab\CustomPixelQRCode\Renderers\RendererConfig;

class DebugRendererConfig extends RendererConfig {

    public $outerFrame = 4;
    public $legendSize = 150;
    public $legendVisible = true;

    function __construct()
    {
        $this->pixelPerPoint = 6;
    }

}
