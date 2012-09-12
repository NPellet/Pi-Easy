<?php

$_baseUrl = '../../../';
require($_baseUrl . '/libs/includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
exit(0);

$uploaded = $_GET['file'];
$album = $_GET['album'];
$Plugin = PluginController::getPlugin('galeries');

$Plugin -> savePicture($uploaded, $album, array('fullname' => $_REQUEST['full_name']), 'blah');

echo json_encode(array('file' => $uploaded, 'album' => $album));

?>