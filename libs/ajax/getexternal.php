<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
exit(0);

$url = $_REQUEST['url'];
$fname = FileManager::parse($url);
$file = FileManager::getExternal($url);

$filename = uniqid();
$filePath = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_TEMP . $filename;
touch($filePath);
$written = file_put_contents($filePath, $file);

$infos = new InfosFile($filePath);
$mime = $infos -> getMime();

if($written)
	echo json_encode(array('name' => $fname['basename'] . '.' . $fname['extension'], 'serverName' => $filename, 'type' => $mime, 'basename' => $fname['basename'], 'extension' => $fname['extension'], 'size' => filesize($filePath)));
else
	die('Impossible d\'écrire dans le fichier');
?>