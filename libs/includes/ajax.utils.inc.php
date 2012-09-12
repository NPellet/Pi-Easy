<?php

session_start();
define('IN_PIEASY', true);

require($_baseUrl . 'includes.inc.php');

$Sql = new Sql();
$Sql -> connect();

Instance::setInstance(new Message(explode('_', $this -> message)), 'Message');
Instance::setInstance($Sql, 'Sql');

if(!User::buildFromSession())
	die('You do not have access to this file');

function findFile() {
	global $_baseUrl;
	if(strpos($_REQUEST['file'], '-') != false) {
		$file = explode('-', $_REQUEST['file']);
		$_REQUEST['file'] = $file[1];
		$_REQUEST['field'] = $file[0];
	}

	if(!empty($_REQUEST['field']) && $_REQUEST['field']!= 'null') {
	
		$Field = Field::buildFromId($_REQUEST['field']);
		
		if($Field -> getType() == 'picture')
			$target_path = isset($_REQUEST['thb']) ? $Field -> getThbFolder() : $Field -> getImgFolder();
		else if($Field -> getType() == 'file' || $Field -> getType() == 'mp3')
			$target_path = $Field -> getFolder();
		
		
	} elseif(!empty($_REQUEST['folder']))
		$target_path = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_MEDIAS . $_REQUEST['folder'];
		
	elseif(file_exists(DATA_ROOT_REL . FOLDER_UPLOAD_TEMP . $_REQUEST['file']))
		$target_path = DATA_ROOT_REL . FOLDER_UPLOAD_TEMP;
	else
		$target_path = DATA_ROOT_REL . FOLDER_UPLOAD_MEDIAS;
		
	$file = $target_path . $_REQUEST['file'];

	if(!file_exists($file))
		die('No file with this name');
	else
		return $file;
}

function securePath($path = NULL) {
	if(!preg_match('!^[()a-zA-Z0-9_\.-]*$!', !empty($path) ? $path : $_REQUEST['file']) || strpos($path, '..'))
		die('You cannot access this file');
}

?>