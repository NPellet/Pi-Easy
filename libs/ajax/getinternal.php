<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
exit(0);

$url = $_REQUEST['url'];
$fname = FileManager::readFile($url);
$file = file_get_contents($url);

$filename = uniqid();
$filePath = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_TEMP . $filename;

touch($filePath);
$written = file_put_contents($filePath, $file);

echo json_encode(array('name' => $fname['name'] . '.' . $fname['ext'], 'serverName' => $filename, 'type' => FileManager::getMime($filePath)));
																										   
if(!$written)
die('Impossible d\'écrire dans le fichier');
?>