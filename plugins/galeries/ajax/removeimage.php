<?php

$_baseUrl = '../../../';
require($_baseUrl . '/libs/includes/ajax.utils.inc.php');

$Plugin = PluginController::getPlugin('galeries');

if(!empty($_GET['album'])) {

	$album = intval($_GET['album']);
	$album = $Plugin -> get('album', $album);
	$dest = $Plugin -> getFolder($album['idx_galerie'], $album['id']);
	
	foreach($_GET['toremove'] as $file) {
		unlink($dest . $file);
		unlink($dest . '/thumbs/' . $file);
	}
	
}


?>