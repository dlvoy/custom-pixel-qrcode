<?php

require __DIR__ . '/../vendor/autoload.php';

use DeltaLab\CustomPixelQRCode\CodeFrame;
use DeltaLab\CustomPixelQRCode\Renderers\Image\ImageCodeRenderer;
use DeltaLab\CustomPixelQRCode\Renderers\Image\ImageRendererConfig;

define('EXAMPLE_TMP_SERVERPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR);
define('EXAMPLE_TMP_SERVERURL', 'output/');

//##############################################################################

$codeContents = 'Let see what the code structure looks like with a little bit bigger code';
$fileName = 'demo.png';

// this is FOR SURE NOT SAFE in production app
// make sure all user inputs are filtered!!!
if (isset($_REQUEST['contents']) && (strlen($_REQUEST['contents']) > 0)) {
    $codeContents = $_REQUEST['contents'];
    $fileName = 'demo_' . md5($codeContents) . '.png';
}

//------------------------------------------------------------------------------

$codeFrame = new CodeFrame($codeContents);

$imageRenderer = new ImageCodeRenderer($codeFrame, new ImageRendererConfig('full-24'));
$imageRenderer->renderToFile(EXAMPLE_TMP_SERVERPATH . 'image_A_' . $fileName);

$imageRenderer->setConfig(new ImageRendererConfig('transparent-less-24'));
$imageRenderer->renderToFile(EXAMPLE_TMP_SERVERPATH . 'image_B_' . $fileName);

$imageRenderer->setConfig(new ImageRendererConfig('transparent-more-24'));
$imageRenderer->renderToFile(EXAMPLE_TMP_SERVERPATH . 'image_C_' . $fileName);

//------------------------------------------------------------------------------
?>
<div style="width:850px; height:300px;overflow:auto;border:1px solid silver;position: relative; left: 0; top: 0;">
    <img style="position:relative;top:10;left:10;z-index:20" src="http://lorempixel.com/800/280/nature">
    <img src="<?php echo EXAMPLE_TMP_SERVERURL . 'image_A_' . $fileName; ?>" style="width:200px;position:absolute;top:40;left:40;z-index:100" />
    <img src="<?php echo EXAMPLE_TMP_SERVERURL . 'image_B_' . $fileName; ?>" style="width:200px;position:absolute;top:40;left:280;z-index:100" />
    <img src="<?php echo EXAMPLE_TMP_SERVERURL . 'image_C_' . $fileName; ?>" style="width:200px;position:absolute;top:40;left:520;z-index:100" />
</div>
<br /><br /><br />
<form action="index.php" method="post">
    <input style="width:40%;margin:0.5em;padding:0.25em;font-size:1.3em" type="text" name="contents" value="<?php echo htmlentities($codeContents); ?>">
    <br />
    <input style="margin:0.5em" type="submit">
</form>
<br /><br /><br />
This demo uses fabulous <a href="http://lorempixel.com">http://lorempixel.com</a> for backgrounds.
