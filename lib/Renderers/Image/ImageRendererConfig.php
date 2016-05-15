<?php

namespace DeltaLab\CustomPixelQRCode\Renderers\Image;

use DeltaLab\CustomPixelQRCode\Renderers\RendererConfig;

class ImageRendererConfig extends RendererConfig {

    private static $THEME_CONTENTS = array('marker_main' => 9, 'marker_sub' => 5, 'pixel_on' => 1, 'pixel_off' => 1, 'pixel_border' => 1);
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    public $marker_main = null;
    public $marker_sub = null;
    public $pixel_on = null;
    public $pixel_off = null;
    //~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
    protected $themeDir;

    function __construct($themeDirOrThemeName)
    {
        $this->detectThemeDir($themeDirOrThemeName);
        $this->ensureThemeFilesExists();
        $this->detectPixelSize();
        $this->ensureThemeImagesHaveCorrectSize();
        $this->loadThemeImages();
    }

    function __destruct()
    {
        $this->dispose();
    }

    //--------------------------------------------------------------------------

    public function dispose()
    {
        foreach (array_keys(ImageRendererConfig::$THEME_CONTENTS) as $themeElement) {
            if (isset($this->$themeElement) && ($this->$themeElement != null)) {
                imagedestroy($this->$themeElement);
                $this->$themeElement = null;
            }
        }
    }

    //--------------------------------------------------------------------------

    private function pathForThemeImage($name)
    {
        return $this->themeDir . DIRECTORY_SEPARATOR . $name . '.png';
    }

    //--------------------------------------------------------------------------

    private function detectThemeDir($themeDirOrThemeName)
    {
        if (!file_exists($themeDirOrThemeName)) {
            $fromPathName = realpath(__DIR__ . '/../../assets/' . $themeDirOrThemeName);
            if (!file_exists($fromPathName)) {
                throw new \Exception("Cannot find theme by name or dir: " . $themeDirOrThemeName);
            } else {
                $this->themeDir = $fromPathName;
            }
        } else {
            $this->themeDir = $themeDirOrThemeName;
        }
    }

    //--------------------------------------------------------------------------

    private function ensureThemeFilesExists()
    {
        foreach (array_keys(ImageRendererConfig::$THEME_CONTENTS) as $themeElement) {
            if (!file_exists($this->pathForThemeImage($themeElement))) {
                throw new \Exception("Required theme file for " . $themeElement . " not found at " . $this->pathForThemeImage($themeElement));
            }
        }
    }

    //--------------------------------------------------------------------------

    private function detectPixelSize()
    {
        $refInfo = getimagesize($this->pathForThemeImage('pixel_on'));
        $this->pixelPerPoint = $refInfo[0];
    }

    //--------------------------------------------------------------------------

    private function ensureThemeImagesHaveCorrectSize()
    {
        foreach (ImageRendererConfig::$THEME_CONTENTS as $themeElement => $pixelScale) {
            $checkInfo = getimagesize($this->pathForThemeImage($themeElement));
            if ($checkInfo[0] != $checkInfo[1]) {
                throw new \Exception("Theme element " . $themeElement . " should be square (theme at: " . $this->pathForThemeImage($themeElement) . ")");
            }

            if ($checkInfo[0] != ($pixelScale * $this->pixelPerPoint)) {
                throw new \Exception("Theme element " . $themeElement . " should be " . ($pixelScale * $this->pixelPerPoint) . " wide and tall, but got: " . $checkInfo[0] . "x" . $checkInfo[1]);
            }
        }
    }

    //--------------------------------------------------------------------------

    private function loadThemeImages()
    {
        foreach (array_keys(ImageRendererConfig::$THEME_CONTENTS) as $themeElement) {
            $image = imagecreatefrompng($this->pathForThemeImage($themeElement));
            imagealphablending($image, false); // Overwrite alpha
            imagesavealpha($image, true);

            $this->$themeElement = $image;
        }
    }

}
