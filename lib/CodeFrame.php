<?php

namespace DeltaLab\CustomPixelQRCode;

class CodeFrame {

    public $pixels = false;
    public $size = false;
    public $markers = array();
    public $subMarkers = array();
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    private $rawFrame = false;
    private $rawSize = false;
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    public static $SYMBOL_EMPTY = "\x00";
    public static $SYMBOL_BORDER = "\x01";
    public static $SYMBOL_SUB_MARKER = "\xa1";
    public static $SYMBOL_PIXEL_OFF = "\x02";
    public static $SYMBOL_PIXEL_ON = "\x03";

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
        $this->pixels = array(str_repeat(CodeFrame::$SYMBOL_BORDER, $this->size));
        for ($y = 0; $y < $this->rawSize; $y++) {
            $this->pixels[] = CodeFrame::$SYMBOL_BORDER . $this->rawFrame[$y] . CodeFrame::$SYMBOL_BORDER;
        }
        $this->pixels[] = str_repeat(CodeFrame::$SYMBOL_BORDER, $this->size);
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

                if ($this->pixels[$y][$x] == CodeFrame::$SYMBOL_SUB_MARKER) {
                    $this->subMarkers[] = array($x, $y);
                    CodeFrame::wipeSquare($this->pixels, $x, $y, 5);
                }


                if (ord($this->pixels[$y][$x]) > 1) {
                    if (ord($this->pixels[$y][$x]) % 2 == 0) {
                        $this->pixels[$y][$x] = CodeFrame::$SYMBOL_PIXEL_OFF;
                    } else {
                        $this->pixels[$y][$x] = CodeFrame::$SYMBOL_PIXEL_ON;
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
        CodeFrame::wipeSquare($this->pixels, 0, 0, 9);
        CodeFrame::wipeSquare($this->pixels, $markerPos, 0, 9);
        CodeFrame::wipeSquare($this->pixels, 0, $markerPos, 9);

        $this->markers = array(array(0, 0), array($markerPos, 0), array(0, $markerPos));
    }

    //##########################################################################

    private static function wipeSquare(&$arr, $fromX, $fromY, $size)
    {
        for ($y = $fromY; $y < $fromY + $size; $y++) {
            for ($x = $fromX; $x < $fromX + $size; $x++) {
                $arr[$y][$x] = CodeFrame::$SYMBOL_EMPTY;
            }
        }
    }

}
