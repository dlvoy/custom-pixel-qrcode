<?php

namespace DeltaLab\CustomPixelQRCode;

use DeltaLab\CustomPixelQRCode\Choreography;
use DeltaLab\CustomPixelQRCode\Renderers\CodeRenderer;
use DeltaLab\CustomPixelQRCode\Renderers\Image\ImageCodeRenderer;

class CodeChoreographer extends CodeRenderer {

    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    public $modeFit;
    public $modeBox;
    public $boxSize = false;
    public $boxMargin;
    public $alignV;
    public $alignH;
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    private $renderer = null;
    private $backgroundImage = null;
    private $backgroundDim = array(0, 0);
    private $codeDim = 0;
    private $codeOrigDim = 0;
    private $codeOffset = array(0, 0);
    private $globalOffset = array(0, 0, 0, 0);

    //##########################################################################
    /**
     * Code Choreographer is special kind of Code Renderer that merges background image with QR Code overlay,
     * controlling how those two are laid and positioned.
     * @param ImageCodeRenderer $renderer Source Image Renderer, provide raw QRCode render.
     */
    function __construct(ImageCodeRenderer $renderer)
    {
        parent::__construct(null, null);
        $this->renderer = $renderer;
        $this->align(Choreography::$alignMiddle, Choreography::$alignMiddle);
        $this->codeSizeFit();
        $this->boundingBoxPadding();
    }

    //--------------------------------------------------------------------------

    function __destruct()
    {
        if ($this->renderer != null) {
            $this->renderer->dispose();
        }
        if (isset($this->backgroundImage) && ($this->backgroundImage != null)) {
            imagedestroy($this->backgroundImage);
            $this->backgroundImage = null;
        }
    }

    //--------------------------------------------------------------------------
    /**
     * Sets background image from PNG or JPEG file
     * @param String $backgroundFile full path to background image
     * @return \DeltaLab\CustomPixelQRCode\CodeChoreographer itself, for method chaining
     * @throws Exception when specified file is not found or cannot be read or is NOT either JPEG or PNG
     */
    public function background($backgroundFile)
    {
        if (!file_exists($backgroundFile)) {
            throw new \Exception("Background image file not found: " . $backgroundFile);
        }
        $bgInfo = \getimagesize($backgroundFile);
        $image = null;

        switch ($bgInfo[2]) {
            case IMAGETYPE_JPEG:
                $image = \imagecreatefromjpeg($backgroundFile);
                break;
            case IMAGETYPE_PNG:
                $image = \imagecreatefrompng($backgroundFile);
                break;
            default:
                throw new \Exception("Unknown file type of file: " . $backgroundFile);
        }

        \imagealphablending($image, false);
        \imagesavealpha($image, true);

        $this->backgroundDim = array($bgInfo[0], $bgInfo[1]);
        $this->backgroundImage = $image;

        return $this;
    }

    //--------------------------------------------------------------------------
    /**
     * Specifies exact box within which code will be fit.
     * @param int $posX box top-left corner X coordinate
     * @param int $posY box top-left corner Y coordinate
     * @param int $width box width
     * @param int $height box height
     * @return \DeltaLab\CustomPixelQRCode\CodeChoreographer itself, for method chaining
     */
    public function boundingBoxExactly($posX, $posY, $width, $height)
    {
        $this->modeBox = Choreography::$boxModeExact;
        $this->boxSize = array($posX, $posY, $width, $height);
        return $this;
    }

    //--------------------------------------------------------------------------
    /**
     * Specifies box within which code will be fit, based on background size
     * but with speciffied padding.
     * @param int $top top padding, if false then 0
     * @param int $right right padding, when false it will be same as top padding
     * @param int $bottom bottom padding, if false it will be same as top padding
     * @param int $left left padding, if false it will be same as right padding
     * @return \DeltaLab\CustomPixelQRCode\CodeChoreographer itself, for method chaining
     */
    public function boundingBoxPadding($top = false, $right = false, $bottom = false, $left = false)
    {
        $this->modeBox = Choreography::$boxModeMargin;
        if ($top === false) {
            $top = 0;
        }
        if ($right === false) {
            $right = $top;
        }
        if ($bottom === false) {
            $bottom = $top;
        }
        if ($left === false) {
            $left = $right;
        }
        $this->boxMargin = array($top, $right, $bottom, $left);
        return $this;
    }

    //--------------------------------------------------------------------------
    /**
     * Specifies code alignment in bounding box
     * It can take one of values:
     * - Choreography::$AlignStart - left or top alignment
     * - Choreography::$AlignMiddle - center alignment
     * - Choreography::$AlignEnd - right or bottom alignment
     * @param enum $horizontal horizontal aligment
     * @param enum $vertical vertical aligment
     * @return \DeltaLab\CustomPixelQRCode\CodeChoreographer itself, for method chaining
     */
    public function align($horizontal = false, $vertical = false)
    {
        if ($horizontal === false) {
            $horizontal = Choreography::$alignMiddle;
        }
        if ($vertical === false) {
            $vertical = Choreography::$alignMiddle;
        }
        $this->alignH = $horizontal;
        $this->alignV = $vertical;
        return $this;
    }

    //--------------------------------------------------------------------------
    /**
     * Set exact size, in screen pixels, of code image.
     * May be bigger than bounding box size - image will be extended if needed.
     * @param int $size
     * @return \DeltaLab\CustomPixelQRCode\CodeChoreographer itself, for method chaining
     */
    public function codeSizeExact($size)
    {
        $this->codeDim = $size;
        $this->modeFit = Choreography::$fitModeFixed;
        return $this;
    }

    //--------------------------------------------------------------------------
    /**
     * Fits code image within bounding box.
     * Some blurring may occur as resampling is used to use as much space of
     * bounding box as possible. For sharper codes use codeSizeSharp instead.
     * @return \DeltaLab\CustomPixelQRCode\CodeChoreographer itself, for method chaining
     */
    public function codeSizeFit()
    {
        $this->modeFit = Choreography::$fitModeResampled;
        return $this;
    }

    //--------------------------------------------------------------------------
    /**
     * Fits code but selects scale to keep it sharp and non blured, 
     * but resulting code area may not take all available space in bounding box.
     * For beter bounding box utilisation, use codeSizeFit.
     * 
     * Fits code image within bounding box, trying to scale only by non-fractional steps
     * For example, if orginal rendered code pixel size is 24, it will try to scale 
     * by 24, 12, 8, 6, 4, 3 and 2 to avoid fractional scalling and bluring.
     * @return \DeltaLab\CustomPixelQRCode\CodeChoreographer itself, for method chaining
     */
    public function codeSizeSharp()
    {
        $this->modeFit = Choreography::$fitModeInexact;
        return $this;
    }

    //--------------------------------------------------------------------------
    /**
     * Sets size of code calulated as multiplication of logical pixel.
     * It allows predictible (constant) size of code pixel.
     * Standard image pixel is 24 so multiplier is recomended to be some of it dividers 
     * to avoid blurring.
     * @param int $multiplier how much logical code pixel need to be scaled to get final pixel size.
     * @return \DeltaLab\CustomPixelQRCode\CodeChoreographer itself, for method chaining
     */
    public function codeSizePixel($multiplier)
    {
        $this->codeDim = $this->renderer->getDim() * $multiplier;
        $this->modeFit = Choreography::$fitModeFixed;
        return $this;
    }

    //--------------------------------------------------------------------------

    public function render()
    {
        $this->renderer->renderIfNeeded();

        $this->calculateBox();
        $this->calculateCodeFit();
        $this->calculateAlignment();
        $this->calculateOverflow();

        $newW = $this->backgroundDim[0] + $this->globalOffset[0] + $this->globalOffset[2];
        $newH = $this->backgroundDim[1] + $this->globalOffset[1] + $this->globalOffset[3];

        $this->rendered = \imagecreatetruecolor($newW, $newH);
        \imagealphablending($this->rendered, false);
        \imagesavealpha($this->rendered, true);

        $color = \imagecolorallocatealpha($this->rendered, 255, 255, 255, 127);
        \imagefilledrectangle($this->rendered, 0, 0, $newW, $newH, $color);

        \imagecopy($this->rendered, $this->backgroundImage, $this->globalOffset[0], $this->globalOffset[1], 0, 0, $this->backgroundDim[0], $this->backgroundDim[1]);

        \imagealphablending($this->rendered, true);
        \imagecopyresampled($this->rendered, $this->renderer->rendered, $this->globalOffset[0] + $this->codeOffset[0], $this->globalOffset[1] + $this->codeOffset[1], 0, 0, $this->codeDim, $this->codeDim, $this->codeOrigDim, $this->codeOrigDim);
        \imagealphablending($this->rendered, false);

        return $this->rendered;
    }

    //--------------------------------------------------------------------------

    private function calculateBox()
    {
        if ($this->boxSize === false) {
            $this->boxSize = array(0, 0, $this->backgroundDim[0], $this->backgroundDim[1]);
        }
        if ($this->modeBox == Choreography::$boxModeMargin) {
            $this->boxSize[0] += $this->boxMargin[0];
            $this->boxSize[1] += $this->boxMargin[1];
            $this->boxSize[2] -= ($this->boxMargin[0] + $this->boxMargin[2]);
            $this->boxSize[3] -= ($this->boxMargin[1] + $this->boxMargin[3]);
        }
    }

    //--------------------------------------------------------------------------

    private function calculateCodeFit()
    {
        $this->codeOrigDim = $this->renderer->getDim();
        switch ($this->modeFit) {
            case Choreography::$fitModeResampled: {
                    $this->codeDim = min($this->boxSize[2], $this->boxSize[3]);
                } break;

            case Choreography::$fitModeInexact: {
                    $this->calculateInexactFit();
                } break;
        }
    }

    //--------------------------------------------------------------------------

    private function calculateInexactFit()
    {
        $bestPixelSize = 1;
        $detectedPixelSize = 1;
        $codePixelSize = $this->renderer->getPixelSize();
        $codeSize = $this->renderer->getSize();
        $maxRenderSize = min($this->boxSize[2], $this->boxSize[3]);

        if ($codeSize == 0) {
            throw new \Exception("Rendered code logical size cannot be 0!");
        }

        do {
            $renderSize = $codeSize * $detectedPixelSize;
            if ($renderSize <= $maxRenderSize) {

                $div = ($detectedPixelSize <= $codePixelSize) ? ($codePixelSize / $detectedPixelSize) : ($detectedPixelSize / $codePixelSize);

                if (floor($div) == $div) {
                    $bestPixelSize = $detectedPixelSize;
                }
            }
            $detectedPixelSize++;
        } while ($renderSize <= $maxRenderSize);

        $this->codeDim = $codeSize * $bestPixelSize;
    }

    //--------------------------------------------------------------------------

    private function calculateAlignment()
    {
        $this->codeOffset = array(0, 0);
        $this->codeOffset[0] = $this->boxSize[0] + floor(($this->boxSize[2] - $this->codeDim) * (0.5 * $this->alignH));
        $this->codeOffset[1] = $this->boxSize[1] + floor(($this->boxSize[3] - $this->codeDim) * (0.5 * $this->alignV));
    }

    //--------------------------------------------------------------------------

    private function calculateOverflow()
    {
        $this->globalOffset = array(0, 0, 0, 0);

        if ($this->codeOffset[0] < 0) {
            $this->globalOffset[0] = abs($this->codeOffset[0]);
        }

        if ($this->codeOffset[1] < 0) {
            $this->globalOffset[1] = abs($this->codeOffset[1]);
        }

        $this->fixOverflowSize(0);
        $this->fixOverflowSize(1);
    }

    private function fixOverflowSize($pos)
    {
        if (($this->codeOffset[0 + $pos] + $this->codeDim) > $this->backgroundDim[0 + $pos]) {
            $this->globalOffset[2 + $pos] = ($this->codeOffset[0 + $pos] + $this->codeDim) - $this->backgroundDim[0 + $pos];
        }
    }

}
