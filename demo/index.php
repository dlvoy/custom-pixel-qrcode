<?php

require __DIR__ . '/../vendor/autoload.php';

use DeltaLab\CustomPixelQRCode\CodePreprocessor;
use DeltaLab\CustomPixelQRCode\Renderers\Debug\DebugCodeRenderer;

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

$preProcessor = new CodePreprocessor($codeContents);
$renderer = new DebugCodeRenderer($preProcessor->getFrame());
$renderer->renderToFile(EXAMPLE_TMP_SERVERPATH . $fileName);

//------------------------------------------------------------------------------

echo '<img src="' . EXAMPLE_TMP_SERVERURL . $fileName . '" />';
echo '<br /><br /><br /><form action="index.php" method="post"><input style="width:40%;margin:0.5em;padding:0.25em;font-size:1.3em" type="text" name="contents" value="' . htmlentities($codeContents) . '">';
echo '<br /><input style="margin:0.5em" type="submit"></form>';
