<?php
require __DIR__ . '/../vendor/autoload.php';

use DeltaLab\CustomPixelQRCode\CodeFrame;
use DeltaLab\CustomPixelQRCode\Renderers\Image\ImageCodeRenderer;
use DeltaLab\CustomPixelQRCode\Renderers\Image\ImageRendererConfig;

define('EXAMPLE_TMP_SERVERPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR);
define('EXAMPLE_TMP_SERVERURL', 'output/');

//##############################################################################

$codeContents = 'Let see what the code structure looks like with a little bit bigger code';
$fileName = 'stage.png';

//------------------------------------------------------------------------------

$codeFrame = new CodeFrame($codeContents);
$imageRenderer = new ImageCodeRenderer($codeFrame);
$imageRenderer->setConfig(new ImageRendererConfig('small-circle-24'));
$imageRenderer->renderToFile(EXAMPLE_TMP_SERVERPATH . 'image_F_' . $fileName);

//------------------------------------------------------------------------------
?>
<h2>Custom Pixel QRCode Demos :: Stage</h2>
<a href="index.php">&laquo; DEMO LIST</a>
<br /><br />
<img src="<?php echo EXAMPLE_TMP_SERVERURL . 'image_F_' . $fileName; ?>" style="width:200px;" />
<br /><br /><br />

