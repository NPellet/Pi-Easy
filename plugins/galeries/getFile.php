<?php

$_baseUrl = '../../';
require($_baseUrl . 'libs/includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
exit(0);
//$target_path = $_baseUrl . FOLDER_MODULE_UPLOAD;
if(empty($_GET['file']) || empty($_GET['album']))
	die('Aucun fichier sélectionné');

$Plugin = PluginController::getPlugin('galeries');
$album = $Plugin -> get('album', $_GET['album']);
$galerie = $Plugin -> get('galerie', $album['idx_galerie']);
$folder = $Plugin -> getFolder($galerie['id'], $album['label']);
$fileName = $folder . (isset($_GET['thb']) ? '/thumbs/' : NULL) . $_GET['file'];

$mime = FileManager::getMime($fileName);
if (!$mime)
	die("Impossible to get mime type");

$fName = @parse_ini_file($fileName . '.props');
if(!$fName['name'])
	$fName['name'] = $_GET['file'];
	
$fp = fopen($fileName, 'rb');
header("Content-Type: $mime");
header("Content-Length: " . filesize($fileName));
header("Content-disposition: inline; filename=" . $fName['name']); 
fpassthru($fp);


?>