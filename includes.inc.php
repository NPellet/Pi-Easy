<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

if(is_file($_baseUrl . '/config/config.inc.php'))
	include($_baseUrl . '/config/config.inc.php');
else
	$toInstall = true;
	
include($_baseUrl . '/libs/config/constantes.inc.php');
include($_baseUrl . '/libs/includes/functions.inc.php');
include($_baseUrl . '/libs/includes/correspondances.inc.php');
include($_baseUrl . '/libs/includes/messages.inc.php');

function __autoload($className) {
	global $_baseUrl;
	$classFile = strtolower($className);
	$strPath = $_baseUrl . FOLDER_LIBS_CLASS;
	if(preg_match('!^Field([a-zA-Z0-9]+)$!is', $className))
		$strPath .= 'fields/';
	
	include($strPath . $classFile . '.class.php');
}



?>