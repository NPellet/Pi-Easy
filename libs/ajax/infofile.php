<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
	exit(0);

if(isset($_REQUEST['file'])) {
	securePath();
	$filePath = findFile();
	$read = FileManager::readFile($filePath, $_REQUEST['folder']);
	echo json_encode($read);
}

?>