<?php

namespace DeltaLab\CustomPixelQRCode;

class CodePreprocessor {

    public $markers = array();
    public $subMarkers = array();
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    private $rawFrame = false;
    private $rawSize = false;
    private $frame = false;
    private $size = false;
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    private static $SYMBOL_EMPTY = "\x00";
    private static $SYMBOL_BORDER = "\x01";
    private static $SYMBOL_SUB_MARKER = "\xa1";
    private static $SYMBOL_PIXEL_OFF = "\x02";
    private static $SYMBOL_PIXEL_ON = "\x03";

    //--------------------------------------------------------------------------

    public function __construct($codeContents = false, $eccLevel = QR_ECLEVEL_H)
    {
        if ($codeContents !== false) {
            $this->initWithRawFrame(\QRcode::raw($codeContents, false, $eccLevel));
        }
        $this->buildExtendedFrame();
    }

    //--------------------------------------------------------------------------

    public function initWithRawFrame($rawFrame)
    {
        $this->rawFrame = $rawFrame;
        $this->rawSize = count($this->rawFrame);
    }

    //--------------------------------------------------------------------------

    public function getFrame()
    {
        return $this->frame;
    }

    //##########################################################################

    private function buildExtendedFrame()
    {
        $this->size = $this->rawSize + 2;
        $this->buildFrameFromRawFrame();
        $this->mapRawPixelsAndGatherSubMarkers();

        $this->findAndCutOutMarkers();
    }

    //--------------------------------------------------------------------------

    private function buildFrameFromRawFrame()
    {
        $this->frame = array(str_repeat(CodePreprocessor::$SYMBOL_BORDER, $this->size));
        for ($y = 0; $y < $this->rawSize; $y++) {
            $this->frame[] = CodePreprocessor::$SYMBOL_BORDER . $this->rawFrame[$y] . CodePreprocessor::$SYMBOL_BORDER;
        }
        $this->frame[] = str_repeat(CodePreprocessor::$SYMBOL_BORDER, $this->size);
    }

    //--------------------------------------------------------------------------
    /**
     * Iterate over frame to convert raw pixels and gather marker locations
     */
    private function mapRawPixelsAndGatherSubMarkers()
    {
        $this->subMarkers = array();

        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {

                if ($this->frame[$y][$x] == CodePreprocessor::$SYMBOL_SUB_MARKER) {
                    $this->subMarkers[] = array($x, $y);
                    CodePreprocessor::wipeSquare($this->frame, $x, $y, 5);
                }


                if (ord($this->frame[$y][$x]) > 1) {
                    if (ord($this->frame[$y][$x]) % 2 == 0) {
                        $this->frame[$y][$x] = CodePreprocessor::$SYMBOL_PIXEL_OFF;
                    } else {
                        $this->frame[$y][$x] = CodePreprocessor::$SYMBOL_PIXEL_ON;
                    }
                }
            }
        }
    }

    //--------------------------------------------------------------------------
    /**
     * Removes main markers, but remembers it's position.
     */
    private function findAndCutOutMarkers()
    {
        $markerPos = $this->size - 9;
        CodePreprocessor::wipeSquare($this->frame, 0, 0, 9);
        CodePreprocessor::wipeSquare($this->frame, $markerPos, 0, 9);
        CodePreprocessor::wipeSquare($this->frame, 0, $markerPos, 9);

        $this->markers = array(array(0, 0), array($markerPos, 0), array(0, $markerPos));
    }

    //##########################################################################

    private static function wipeSquare(&$arr, $fromX, $fromY, $size)
    {
        for ($y = $fromY; $y < $fromY + $size; $y++) {
            for ($x = $fromX; $x < $fromX + $size; $x++) {
                $arr[$y][$x] = CodePreprocessor::$SYMBOL_EMPTY;
            }
        }
    }

}
