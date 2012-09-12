<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class PluginController extends Security {
	
	public function getId() { return $this -> id; }
	
	public function getParams($pluginId = NULL) {
		global $_baseUrl;
		$params = array();
/*
		if(!empty($this -> params))
			return $this -> params;
*/
		$strSql = '
		SELECT `params`.*, `plugins`.`name` FROM 
			`' . Sql::buildTable(T_CFG_PLUGINS) . '` `plugins`
		LEFT JOIN
			`' . Sql::buildTable(T_CFG_PLUGINS_PARAMETERS) . '` `params`	
		ON 
			`plugins`.`id` = `params`.`idx_plugin`
		WHERE 
			`plugins`.`id` = "' . $pluginId . '"
		OR
			`plugins`.`name` = "' . $pluginId . '"
		';

		if($resSql = self::query($strSql)) {
				
			if(mysql_num_rows($resSql) > 0) {
				$i = 0;
				while($dataSql = mysql_fetch_assoc($resSql)) {
					$i++;
					if($i == 1) {
						$paramFile = $_baseUrl . FOLDER_PLUGINS . $dataSql['name'] . '/params.inc.php';
						$params = include($paramFile);
					}
					$params[$dataSql['key']]['value'] = $dataSql['value'];
				}
			
		} else {
			
			$paramFile = $_baseUrl . FOLDER_PLUGINS . $pluginId . '/params.inc.php';
			$params = include($paramFile);
	
		}
		}
		return $params;
	}


	public static function getPlugin($pluginId, $installed = false) {
		
		if(empty($pluginId))
			return;
			
		global $_baseUrl;
		$params = array();
		if(preg_match('!^[0-9]*$!', $pluginId)) {
			$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_PLUGINS) . '` 
						WHERE `id` = ' . intval($pluginId) . ' 
						OR `name` = "' . Sql::secure($pluginId) . '"
						' . ($installed == true ? ' AND `installed` = 1' : NULL);
			
			if($resSql = self::query($strSql)) {
				if($dataSql = mysql_fetch_assoc($resSql))
					$name = $dataSql['name'];
				else
					$name = $pluginId;
			
				$pluginId = $dataSql['id'];
			}
		} else if(preg_match('!^[a-zA-Z0-9_]*$!', $pluginId)) {
			$name = $pluginId;
		} else
			return false;

		if(!class_exists('Plugin', false)) {
			$pluginFile = $_baseUrl . FOLDER_PLUGINS . $name . '/plugin.php';
			
			if(!include($pluginFile))
				return self::log();
			else {
				
				include($_baseUrl . FOLDER_PLUGINS . $name . '/messages.php');
				$Plugin = new Plugin($pluginId);
				return $Plugin;	
			}
		} else {
			$Plugin = new Plugin($pluginId);
			return $Plugin;
		}
	}

	public static function getPlugins($installedOnly = false) {
		
		$installed = array();
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_PLUGINS) . '`';
		if($resSql = self::query($strSql)) {
			while($dataSql = mysql_fetch_assoc($resSql)) {
				$installed[$dataSql['name']] = $dataSql['id'];
			}
		}

		$tPlugins = array();
		if($dir = opendir(FOLDER_PLUGINS)) {
			while($file = readdir($dir)) {

				if(is_dir(FOLDER_PLUGINS . $file) && $file != '.' && $file != '..' && file_exists(FOLDER_PLUGINS . $file . '/config.inc.php')) {
					$config = require(FOLDER_PLUGINS . $file . '/config.inc.php');
					$isInstalled = array_key_exists($config['name'], $installed);
					if(($installedOnly == true && $isInstalled == true) || $installedOnly == false)
						$tPlugins[] = array(
								'name' => $file, 
								'installed' => $isInstalled, 
								'version' => $config['version'], 
								'title' => $config['title'], 
								'author' => $config['author'], 
								'description' => $config['description'], 
								'id' => $isInstalled ? $installed[$config['name']] : $file
						);
				}
			}
		}	
		
		return $tPlugins;
	}
	

}

?>