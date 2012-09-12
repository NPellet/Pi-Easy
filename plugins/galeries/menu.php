<?php

$params = PluginController::getParams($id);

$Page = Instance::getInstance('Page');
$Menu = $Page -> addMenu('Galeries', $this -> url(array('plugin' => $id, 'mode' => 'galerie'), true));

$tAlbums = array();
$strSql = 'SELECT * FROM `' . Sql::buildTable($params['t_albums']['value']) . '` ORDER BY `order`';
if($resSql = Sql::query($strSql))
	while($dataSql = mysql_fetch_assoc($resSql))
		$tAlbums[] = $dataSql;	

$strSql = 'SELECT * FROM `' . Sql::buildTable($params['t_galeries']['value']) . '` ORDER BY `order`';
if($resSql = Sql::query($strSql)) {
	
	$config = array();
	$config['url'] = $this -> url(array('mode' => 'facebook', 'plugin' => $id, 'action' => 'show', 'galerie' => 0), true);
	$config['label'] = 'Synchro Facebook';
	$Menu -> addEntry($config);


	$config = array();
	$config['url'] = $this -> url(array('mode' => 'album', 'plugin' => $id, 'action' => 'show', 'galerie' => "0"), true);
	$config['label'] = 'Albums non classés';
	$config['selected'] = ($this -> nav('galerie') == 0) ? true : false;
	$Menu -> addEntry($config);
	
	while($dataSql = mysql_fetch_assoc($resSql)) {
		$config = array();
		$config['url'] = $this -> url(array('mode' => 'album', 'plugin' => $id, 'action' => 'show', 'galerie' => $dataSql['id']), true);
		$config['label'] = $dataSql['label'];
		$config['selected'] = ($this -> nav('galerie') == $dataSql['id']) ? true : false;
	/*	$config['sub'] = array();
	
		foreach($tAlbums as $album) {
			if($album['idx_galerie'] == $dataSql['id']) {
				$config['sub'][] = array(
					 'url' => $this -> url(array(
						'mode' => 'album', 
						'plugin' => $id, 
						'action' => 'edit', 
						'album' => $album['id']), true), 
					'label' => $album['label'],
					'galerie' => NULL,
				);	
			}
		}
		*/
		$Menu -> addEntry($config);
	}
}

?>