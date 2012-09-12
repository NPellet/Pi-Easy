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
$folder = $Plugin -> getFolder($album['idx_galerie'], $album['id']);

if(isset($_GET['cover']))
	$fileName = $folder . '/cover/' . (isset($_GET['thb']) ? '/thumbs/' : NULL) . $_GET['file'];
else
	$fileName = $folder . (isset($_GET['thb']) ? '/thumbs/' : NULL) . $_GET['file'];


$infos = new InfosFile($fileName);

$mime = $infos -> getMime();
if (!$mime)
	die("Impossible to get mime type");

$name = $infos -> getProp('name');
	
$fp = fopen($fileName, 'rb');
header("Content-Type: $mime");
header("Content-Length: " . filesize($fileName));
header("Content-disposition: inline; filename=" . $name);
 
fpassthru($fp);


?>