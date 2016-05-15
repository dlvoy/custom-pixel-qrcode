<?php

namespace DeltaLab\CustomPixelQRCode;

use DeltaLab\CustomPixelQRCode\Renderers\CodeRenderer;
use DeltaLab\CustomPixelQRCode\Renderers\Image\ImageCodeRenderer;

class CodeStage extends CodeRenderer {

    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    public static $ALIGN_START = 0;
    public static $ALIGN_MIDDLE = 1;
    public static $ALIGN_END = 2;
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    public static $BOX_MODE_EXACT = 0;
    public static $BOX_MODE_MARGIN = 1;
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    public static $FIT_MODE_FIXED = 0;
    public static $FIT_MODE_RESAMPLED = 1;
    public static $FIT_MODE_INEXACT = 2;
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    public $modeBox;
    public $boxSize;
    public $boxMargin;
    public $modeFit;
    public $alignV;
    public $alignH;
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    private $renderer = null;
    private $backgroundImage = null;
    private $backgroundSize = array(0, 0);
    private $codeSize = array(0, 0);

    //##########################################################################

    function __construct(ImageCodeRenderer $renderer)
    {
        parent::__construct(null, null);
        $this->renderer = $renderer;
        $this->align(CodeStage::$ALIGN_MIDDLE, CodeStage::$ALIGN_MIDDLE);
        $this->codeSizeFit();
        $this->boundingBoxMargin();
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

    public function background($backgroundFile)
    {
        return $this;
    }

    //--------------------------------------------------------------------------

    public function boundingBoxExactly($x, $y, $w, $h)
    {
        $this->modeBox = CodeStage::$BOX_MODE_EXACT;
        $this->boxSize = array($x, $y, $x + w, $y + h);
        return $this;
    }

    //--------------------------------------------------------------------------

    public function boundingBoxMargin($top = false, $right = false, $bottom = false, $left = false)
    {
        $this->modeBox = CodeStage::$BOX_MODE_MARGIN;
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

    public function align($horizontal = false, $vertical = false)
    {
        if ($horizontal === false) {
            $horizontal = CodeStage::$ALIGN_MIDDLE;
        }
        if ($vertical === false) {
            $vertical = CodeStage::$ALIGN_MIDDLE;
        }
        $this->alignH = $horizontal;
        $this->alignV = $vertical;
        return $this;
    }

    //--------------------------------------------------------------------------

    public function codeSizeExact($w, $h)
    {
        $this->codeSize = array($w, $h);
        $this->modeFit = CodeStage::$FIT_MODE_FIXED;
        return $this;
    }

    //--------------------------------------------------------------------------

    public function codeSizeFit()
    {
        $this->modeFit = CodeStage::$FIT_MODE_RESAMPLED;
        return $this;
    }

    //--------------------------------------------------------------------------

    public function codeSizeSharp()
    {
        $this->modeFit = CodeStage::$FIT_MODE_INEXACT;
        return $this;
    }

    //--------------------------------------------------------------------------

    public function codeSizePixel($multiplier)
    {
        $scaledSize = $this->renderer->frame->size * $multiplier;
        $this->codeSize = array($scaledSize, $scaledSize);
        $this->modeFit = CodeStage::$FIT_MODE_FIXED;
        return $this;
    }

    //--------------------------------------------------------------------------

    public function render()
    {
        
    }

}
