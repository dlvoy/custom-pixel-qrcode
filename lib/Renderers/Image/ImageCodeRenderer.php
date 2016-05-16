<?php

namespace DeltaLab\CustomPixelQRCode\Renderers\Image;

use DeltaLab\CustomPixelQRCode\CodeFrame;
use DeltaLab\CustomPixelQRCode\Renderers\CodeRenderer;

class ImageCodeRenderer extends CodeRenderer {

    public function __construct(CodeFrame $preProcessedFrame = null, ImageRendererConfig $config = null)
    {
        parent::__construct($preProcessedFrame, $config);
    }

    //--------------------------------------------------------------------------

    public function render()
    {
        if ($this->config == null) {
            throw new \Exception("Renderer config need to be set before rendering");
        }

        $imgW = $this->frame->size * $this->config->pixelPerPoint;
        $imgH = $this->frame->size * $this->config->pixelPerPoint;

        $image = imagecreatetruecolor($imgW, $imgH);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $color = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefilledrectangle($image, 0, 0, $imgW, $imgH, $color);

        $this->renderPixels($image);
        $this->renderSpecialElement($image, $this->frame->markers, 'markerMain', 9);
        $this->renderSpecialElement($image, $this->frame->subMarkers, 'markerSub', 5);

        return $image;
    }

    //--------------------------------------------------------------------------

    private function renderPixels(&$image)
    {
        for ($y = 0; $y < $this->frame->size; $y++) {
            for ($x = 0; $x < $this->frame->size; $x++) {
                $themeElement = false;
                switch ($this->frame->pixels[$y][$x]) {
                    case CodeFrame::$symbolBorder : $themeElement = 'pixelBorder';
                        break;
                    case CodeFrame::$symbolPixelOn : $themeElement = 'pixelOn';
                        break;
                    case CodeFrame::$symbolPixelOff : $themeElement = 'pixelOff';
                        break;
                    default:
                        break;
                }
                if ($themeElement == false) {
                    continue;
                }
                imagecopy($image, $this->config->$themeElement, $x * $this->config->pixelPerPoint, $y * $this->config->pixelPerPoint, 0, 0, $this->config->pixelPerPoint, $this->config->pixelPerPoint);
            }
        }
    }

    //--------------------------------------------------------------------------

    private function renderSpecialElement(&$image, $elements, $themeElement, $imagePointSize)
    {
        foreach ($elements as $element) {
            imagecopy($image, $this->config->$themeElement, $element[0] * $this->config->pixelPerPoint, $element[1] * $this->config->pixelPerPoint, 0, 0, $imagePointSize * $this->config->pixelPerPoint, $imagePointSize * $this->config->pixelPerPoint);
        }
    }

}
