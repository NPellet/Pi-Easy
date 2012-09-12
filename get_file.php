<?php

$_baseUrl = './';
require('./libs/includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
exit(0);
//$target_path = $_baseUrl . FOLDER_MODULE_UPLOAD;
if(empty($_GET['file']))
	die('Aucun fichier sélectionné');

$fileName = $_GET['file'];

if(preg_match('!\.\.!', $fileName))
	die('You cannot retrieve this file');
	
securePath();
$filePath = findFile();
$infos = new InfosFile($filePath);

$mime = $infos -> getMime();

if (!$mime)
	die("Impossible to get mime type");

$fName = @parse_ini_file($filePath . '.props');

$filename = empty($fName['name']) ? $_GET['file'] : $fName['name'];
if(!empty($fName['ext']))
	$filename .= "." . $fName['ext'];

$fp = fopen($filePath, 'rb');

header("Content-disposition: inline; filename=" . $filename);
header("Content-Type: $mime");
header("Content-Length: " . filesize($filePath));

fpassthru($fp);
?>