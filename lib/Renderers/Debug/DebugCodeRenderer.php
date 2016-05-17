<?php

namespace DeltaLab\CustomPixelQRCode\Renderers\Debug;

use DeltaLab\CustomPixelQRCode\CodeFrame;
use DeltaLab\CustomPixelQRCode\Renderers\CodeRenderer;

class DebugCodeRenderer extends CodeRenderer {

    private $colorTarget = array();
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    private $colorSpec = array(
        "\x00" => array(255, 255, 0), // no data   
        "\x01" => array(255, 0, 0), // border
        "\x02" => array(220, 220, 220), // 0      
        "\x03" => array(0, 0, 0), // 1 
    );
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    private $colorLegend = array(
        "\x00" => "no data - marker",
        "\x01" => "border          ",
        "\x02" => "data bit 0      ",
        "\x03" => "data bit 1      ",
    );

    //##########################################################################

    public function __construct(CodeFrame $preProcessedFrame = null, DebugRendererConfig $config = null)
    {
        parent::__construct($preProcessedFrame, $config);

        if ($config == null) {
            $this->setConfig(new DebugRendererConfig());
        }
    }

    //--------------------------------------------------------------------------

    public function render()
    {
        if ($this->config == null) {
            throw new \Exception("Renderer config need to be set before rendering");
        }

        // rendering frame with GD2 (that should be function by real impl.!!!) 
        $logicalH = $this->frame->size;
        $logicalW = $this->frame->size;

        $imgW = $logicalW + 2 * $this->config->outerFrame;
        $imgH = $logicalH + 2 * $this->config->outerFrame;

        $baseImage = \imagecreate($imgW, $imgH);

        $colBg = \imagecolorallocate($baseImage, 255, 255, 255); // BG, white  

        foreach ($this->colorSpec as $colorKey => $colorDef) {
            $colorBase[$colorKey] = \imagecolorallocate(
                    $baseImage, $colorDef[0], $colorDef[1], $colorDef[2]
            );
        }

        \imagefill($baseImage, 0, 0, $colBg);

        for ($y = 0; $y < $logicalH; $y++) {
            for ($x = 0; $x < $logicalW; $x++) {
                \imagesetpixel(
                        $baseImage, $x + $this->config->outerFrame, $y + $this->config->outerFrame, $colorBase[$this->frame->pixels[$y][$x]]
                );
            }
        }

        $legendSize = $this->config->legendVisible ? $this->config->legendSize : 0;

        // creating zoomed version 
        $targetImage = \imagecreate(
                $imgW * $this->config->pixelPerPoint + $legendSize, max($imgH * $this->config->pixelPerPoint, 250)
        );

        \imagecolorallocate($targetImage, 255, 255, 255); // BG, white  

        foreach ($this->colorSpec as $colorKey => $colorDef) {
            $this->colorTarget[$colorKey] = \imagecolorallocate(
                    $targetImage, $colorDef[0], $colorDef[1], $colorDef[2]
            );
        }
        \imagecopyresized(
                $targetImage, $baseImage, 0, 0, 0, 0, $imgW * $this->config->pixelPerPoint, $imgH * $this->config->pixelPerPoint, $imgW, $imgH);
        \imagedestroy($baseImage);

        if ($this->config->legendVisible) {
            $this->renderLegend($targetImage, $imgW);
        }

        return $targetImage;
    }

    //--------------------------------------------------------------------------

    private function renderLegend(&$targetImage, $imgW)
    {
        $coltTxt = \imagecolorallocate($targetImage, 0, 0, 0); // TXT, black  
        $pos = 0;
        foreach ($this->colorLegend as $colKey => $colName) {
            $px = $imgW * $this->config->pixelPerPoint + 25;
            $py = $this->config->outerFrame * $this->config->pixelPerPoint + $pos * 16;
            \imagefilledrectangle(
                    $targetImage, $px - 20, $py + 3, $px - 10, $py + 13, $this->colorTarget[$colKey]
            );
            \imagerectangle($targetImage, $px - 20, $py + 3, $px - 10, $py + 13, $coltTxt);
            \imagestring($targetImage, 2, $px, $py + 1, $colName, $coltTxt);
            $pos++;
        }
    }

}
