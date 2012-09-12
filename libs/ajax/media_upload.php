<?php

$_baseUrl = '../../';
require($_baseUrl . '/libs/includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
exit(0);

$uploaded = $_GET['file'];

$dest = DATA_ROOT . FOLDER_UPLOAD_MEDIAS . $_GET['folder'];

if(is_dir($dest)) {

	if(rename(DATA_ROOT . FOLDER_UPLOAD_TEMP . $uploaded, $dest . $uploaded)) {
		
		$Infos = new InfosFile($dest . $uploaded);
		$Infos -> setPropsFromName($_REQUEST['fileName']);
		$Infos -> save();
		
		echo json_encode(array('file' => $uploaded, 'folder' => $_GET['folder']));
	}
}

?>