<?php

class PluginInstaller extends PluginController {
	
	public function edit($oldParams, $params) {
	
		global $_baseUrl;

		$folder = $_baseUrl . DATA_ROOT_REL . $params['image_folder']['value'];
		$oldFolder = $_baseUrl . DATA_ROOT_REL . $oldParams['image_folder']['value'];

		rename($oldFolder, $folder);
/*		if(!rename($oldFolder, $folder))
			return $this -> log();
	*/		
		@chmod($folder, 0777);

		if($oldParams['t_galeries']['value'] != $params['t_galeries']['value']) {
			$strSql = 'RENAME TABLE `' . Sql::buildTable($oldParams['t_galeries']['value']) . '` TO `' . Sql::buildTable($params['t_galeries']['value']) . '`';		
			if(!$this -> query($strSql))
				return $this -> log('galeries:0:6');
		}
				

		if($oldParams['t_albums']['value'] != $params['t_albums']['value']) {
			$strSql = 'RENAME TABLE `' . Sql::buildTable($oldParams['t_albums']['value']) . '` TO `' . Sql::buildTable($params['t_albums']['value']) . '`';		
			if(!$this -> query($strSql))
				return $this -> log('galeries:0:6');
		}
	}
	
	public function install($pluginId) {
	
		global $_baseUrl;
		$params = $this -> getParams($pluginId);
		$folder = $_baseUrl . DATA_ROOT_REL . $params['image_folder']['value'];

//		if(is_dir($folder))
			
		//	FileManager::remove($folder);
		
		if(!file_exists($folder) && !mkdir($folder)) 
			return $this -> log('galeries:0:3');
			
		@chmod($folder, 0777);
		
		$strSql = '
		CREATE TABLE IF NOT EXISTS `' . Sql::buildTable($params['t_galeries']['value']) . '` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `label` varchar(100) NOT NULL,
		  `description` TEXT NOT NULL,
		  `date` int(11) NOT NULL,
		  `order` int(10) NOT NULL,
		  `link1` varchar(100) NOT NULL,
		  `link2` varchar(100) NOT NULL,
  		  `watermark` VARCHAR(250) NOT NULL,
 		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
		echo $strSql;
		if(!$this -> query($strSql))
			return $this -> log('galeries:0:4');
			
		$strSql = '
		CREATE TABLE IF NOT EXISTS `' . Sql::buildTable($params['t_albums']['value']) . '` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `idx_galerie` int(11) NOT NULL,
		  `label` varchar(100) NOT NULL,
		  `folder` varchar(100) NOT NULL,
		  `description` TEXT NOT NULL,
		  `date` int(11) NOT NULL,
		  `order` int(10) NOT NULL,
		  `cover` varchar(100) NOT NULL,
		  `link1` varchar(100) NOT NULL,
		  `link2` varchar(100) NOT NULL,
		  `watermark` tinyint(1) NOT NULL,
 		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
		echo $strSql;
		if(!$this -> query($strSql))
			return $this -> log('galeries:0:4');
			
		$this -> log('galeries:1:2');
		
		$this -> redirect(array('action' => 'show'));
	}
	
	public function uninstall($pluginId) {
		
		$params = $this -> getParams($pluginId);
		
		$folder = $params['image_folder']['value'];
		if(is_dir($folder))
			FileManager::remove($folder);
		
		$strSql = 'DROP TABLE `' . Sql::buildTable($params['t_galeries']['value']) . '`';
		if(!$this -> query($strSql))
			$this -> log();
		
		$strSql = 'DROP TABLE `' . Sql::buildTable($params['t_galeries']['value']) . '`';
		if(!$this -> query($strSql))
			$this -> log();
	}
	
}


?>