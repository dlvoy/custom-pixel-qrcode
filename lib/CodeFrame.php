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
        public static $symbolEmpty = "\x00";
        public static $symbolBorder = "\x01";
        public static $symbolSubMarker = "\xa1";
        public static $symbolPixelOff = "\x02";
        public static $symbolPixelOn = "\x03";

        //--------------------------------------------------------------------------

        public function __construct($codeContents, $eccLevel = QR_ECLEVEL_H)
        {
            $this->initWithRawFrame(\QRcode::raw($codeContents, false, $eccLevel));
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
            $this->pixels = array(str_repeat(CodeFrame::$symbolBorder, $this->size));
            for ($y = 0; $y < $this->rawSize; $y++) {
                $this->pixels[] = CodeFrame::$symbolBorder.$this->rawFrame[$y].CodeFrame::$symbolBorder;
            }
            $this->pixels[] = str_repeat(CodeFrame::$symbolBorder, $this->size);
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

                    if ($this->pixels[$y][$x] == CodeFrame::$symbolSubMarker) {
                        $this->subMarkers[] = array($x, $y);
                        $this->wipeSquare($x, $y, 5);
                    }

                    $pixelValue = ord($this->pixels[$y][$x]);
                    if ($pixelValue > 1) {
                        $this->pixels[$y][$x] = ($pixelValue % 2 == 0) ? CodeFrame::$symbolPixelOff : CodeFrame::$symbolPixelOn;
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
            $this->wipeSquare(0, 0, 9);
            $this->wipeSquare($markerPos, 0, 9);
            $this->wipeSquare(0, $markerPos, 9);

            $this->markers = array(array(0, 0), array($markerPos, 0), array(0, $markerPos));
        }

        //--------------------------------------------------------------------------

        private function wipeSquare($fromX, $fromY, $size)
        {
            for ($y = $fromY; $y < $fromY + $size; $y++) {
                for ($x = $fromX; $x < $fromX + $size; $x++) {
                    $this->pixels[$y][$x] = CodeFrame::$symbolEmpty;
                }
            }
        }

    }
    