<?php
require __DIR__ . '/../vendor/autoload.php';

use DeltaLab\CustomPixelQRCode\CodeFrame;
use DeltaLab\CustomPixelQRCode\Renderers\Image\ImageCodeRenderer;
use DeltaLab\CustomPixelQRCode\Renderers\Image\ImageRendererConfig;

define('EXAMPLE_TMP_SERVERPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR);
define('EXAMPLE_TMP_SERVERURL', 'output/');

//##############################################################################

$codeContents = 'Border configuration demo';
$fileName = 'demo.png';

// this is FOR SURE NOT SAFE in production app
// make sure all user inputs are filtered!!!
if (isset($_REQUEST['contents']) && (strlen($_REQUEST['contents']) > 0)) {
    $codeContents = $_REQUEST['contents'];
    $fileName = 'border_' . md5($codeContents) . '.png';
}

//------------------------------------------------------------------------------

$codeFrame = new CodeFrame($codeContents);

$imageRenderer = new ImageCodeRenderer($codeFrame, new ImageRendererConfig('small-circle-24'));
// This mode is set by default
// $imageRenderer->config->setBorderMode(ImageRendererConfig::$borderModeOwn);
$imageRenderer->renderToFile(EXAMPLE_TMP_SERVERPATH . 'image_A_' . $fileName);

$imageRenderer->config->setBorderMode(ImageRendererConfig::$borderModePixelated);
$imageRenderer->renderToFile(EXAMPLE_TMP_SERVERPATH . 'image_B_' . $fileName);

$imageRenderer->config->setBorderMode(ImageRendererConfig::$borderModeHidden);
$imageRenderer->renderToFile(EXAMPLE_TMP_SERVERPATH . 'image_C_' . $fileName);

//------------------------------------------------------------------------------
?>
<h2>Custom Pixel QRCode Demos :: Border mode</h2>
<a href="index.php">&laquo; DEMO LIST</a>
<br /><br />
<div style="width:1050px; height:380px;overflow:auto;border:1px solid silver;position: relative; left: 0; top: 0; background-color:gray">
    <img src="<?php echo EXAMPLE_TMP_SERVERURL . 'image_A_' . $fileName; ?>" style="width:300px;position:absolute;top:40;left:40;z-index:100" />
    <img src="<?php echo EXAMPLE_TMP_SERVERURL . 'image_B_' . $fileName; ?>" style="width:300px;position:absolute;top:40;left:370;z-index:100" />
    <img src="<?php echo EXAMPLE_TMP_SERVERURL . 'image_C_' . $fileName; ?>" style="width:300px;position:absolute;top:40;left:700;z-index:100" />
</div>
<br /><br /><br />
<form action="demo_border.php" method="post">
    <input style="width:40%;margin:0.5em;padding:0.25em;font-size:1.3em" type="text" name="contents" value="<?php echo htmlentities($codeContents); ?>">
    <br />
    <input style="margin:0.5em" type="submit">
</form>
