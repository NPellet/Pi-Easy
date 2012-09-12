<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
exit(0);

$fPath = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_TEMP . @$_SESSION['filename'];

if(@$_REQUEST['newfile'] != NULL) {
	
	if(!empty($_SESSION['filename'])) {
		securePath($_SESSION['filename']);
		
		if(file_exists($fPath))
			if(filesize($fPath) != $_SESSION['filesize'])
				unlink($fPath);
	}
	
	$filesize = $_REQUEST['newfile'];
	$_SESSION['filename'] = uniqid();
	$_SESSION['filesize'] = $_REQUEST['filesize'];
	
	touch($fPath);


	$parsed = FileManager::parse($_REQUEST['filename']);
	echo json_encode(array('filename' => $_SESSION['filename'], 'basename' => $parsed['basename'], 'extension' => $parsed['extension']));
	return;
}

$filename = $_SESSION['filename'];
$post_fp = fopen("php://input", "rb");
if (!$post_fp)
	die('Contenu du fichier introuvable');

$written = file_put_contents($fPath, $post_fp, FILE_APPEND);
if(!$written)
	die('Impossible d\'écrire dans le fichier');
?>