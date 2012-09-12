<?php

$_baseUrl = '../../../';
require($_baseUrl . '/libs/includes/ajax.utils.inc.php');

$Plugin = PluginController::getPlugin('galeries');
$params = $Plugin -> getParams('galeries');

$Navigation = new Navigation();

if(!empty($_REQUEST['toremove'])) {

	$remove = $_REQUEST['type'];
	$toRemove = explode(',', $_REQUEST['toremove']);
	$extra = $_REQUEST['extraData'];

	foreach($toRemove as $removeitem) 
	switch($remove) {
		
		case 'galerie':
			$galId = $removeitem;
			$strSql = 'DELETE FROM `' . Sql::buildTable($params['t_galeries']['value']) . '` WHERE `id` = ' . intval($galId) . ' LIMIT 1';
			FileManager::remove($Plugin -> getFolder($galId));
			
			if(Sql::query($strSql)) {
				$strSql = 'DELETE FROM `' . Sql::buildTable($params['t_albums']['value']) . '` WHERE `idx_galerie` = ' . intval($galId);
				Sql::query($strSql);				
			}
			
		break;
		
		case 'album':
			$albId = $removeitem;
			$album = $Plugin -> get('album', $albId);
			$folder = $Plugin -> getFolder($album['idx_galerie'], $album['id']);
			FileManager::remove($folder);
			$strSql = 'DELETE FROM `' . Sql::buildTable($params['t_albums']['value']) . '` WHERE `id` = ' . intval($albId) . ' LIMIT 1';
		echo $strSql;
			if(!Sql::query($strSql))
				return;
				
			FileManager::remove($Plugin -> getFolder($album['idx_galerie'], $album['label']));
		break;
		
		case 'image':
			
			if(empty($removeitem))
				return;
			
			$album = $Plugin -> get('album', $extra[1]);
			$folder = $Plugin -> getFolder($extra[0], $extra[1]);
			
			if(!is_file($folder . $removeitem))
				return;
			
			FileManager::remove($folder . $removeitem);
			FileManager::remove($folder . 'thumbs/' . $removeitem);
			FileManager::remove($folder . 'thumbs/' . $removeitem . '.props');
			FileManager::remove($folder . $removeitem . '.props');
		break;
	}
}

?>