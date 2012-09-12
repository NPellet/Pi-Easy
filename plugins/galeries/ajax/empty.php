<?php

$_baseUrl = '../../../';
require($_baseUrl . '/libs/includes/ajax.utils.inc.php');

$Plugin = PluginController::getPlugin('galeries');

if(!empty($_GET['album'])) {

	$album = intval($_GET['album']);
	$album = $Plugin -> get('album', $album);
	$dest = $Plugin -> getFolder($album['idx_galerie'], $album['id']);
	
	$dir = opendir($dest);
	while($file = readdir($dir)) {
		if(!is_dir($dest . '/' . $file))
			unlink($dest . '/' . $file);
	}
	
	$dest = $dest . '/thumbs';
	$dir = opendir($dest);
	while($file = readdir($dir)) {
		if(!is_dir($dest . '/' . $file))
			unlink($dest . '/' . $file);
	}
}


?>