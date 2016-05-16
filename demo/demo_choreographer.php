<?php
require __DIR__ . '/../vendor/autoload.php';

use DeltaLab\CustomPixelQRCode\CodeFrame;
use DeltaLab\CustomPixelQRCode\CodeChoreographer;
use DeltaLab\CustomPixelQRCode\Choreography;
use DeltaLab\CustomPixelQRCode\Renderers\Image\ImageCodeRenderer;
use DeltaLab\CustomPixelQRCode\Renderers\Image\ImageRendererConfig;

define('EXAMPLE_TMP_SERVERPATH', __DIR__ . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR);
define('EXAMPLE_TMP_SERVERURL', 'output/');

//##############################################################################

$imageRendererA = new ImageCodeRenderer(new CodeFrame("http://www.coca-cola.com"), new ImageRendererConfig('small-circle-24'));
$choreograpgerA = new CodeChoreographer($imageRendererA);
$choreograpgerA
        ->background(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'coca-cola-1950-scaled.png')
        ->boundingBoxPadding(120)
        ->codeSizeFit()
        //->codeSizeSharp()
        ->renderToFile(EXAMPLE_TMP_SERVERPATH . 'demo_A.png');

// dispose is also automatically done in destructor
// but here we call it earlier to release some memory for further processing
$imageRendererA->dispose();
$choreograpgerA->dispose();

$imageRendererB = new ImageCodeRenderer(new CodeFrame("http://php.dzienia.pl/custom-pixel-qrcode/demo/demo_stage.php"), new ImageRendererConfig('bordered-24'));
$choreograpgerB = new CodeChoreographer($imageRendererB);
$choreograpgerB
        ->background(__DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'girl-and-blank-paper.jpg')
        ->boundingBoxExactly(480, 180, 290, 290)
        ->codeSizeFit()
        ->align(Choreography::$alignMiddle, Choreography::$alignMiddle)
        ->renderToFile(EXAMPLE_TMP_SERVERPATH . 'demo_B.png');
$imageRendererB->dispose();
$imageRendererB->dispose();

//------------------------------------------------------------------------------
?>
<h2>Custom Pixel QRCode Demos :: Choreographer</h2>
<a href="index.php">&laquo; DEMO LIST</a>
<br /><br />
<img src="<?php echo EXAMPLE_TMP_SERVERURL . 'demo_A.png'; ?>"  />
<br />
<img src="<?php echo EXAMPLE_TMP_SERVERURL . 'demo_B.png'; ?>"  />
<br /><br /><br />
Image sources:
<ul>
    <li>http://logos.wikia.com/wiki/File:Coca-Cola_1950.png</li>
    <li>http://www.publicdomainpictures.net/view-image.php?image=13857&picture=girl-and-blank-paper</li>
</ul>