<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class PluginManager extends Security {
	
	private static $Installer;
	
	public static function run() {

		$plugin = self::nav('plugin');
		
		if(self::nav('action') == 'install')
			return PluginManager::install($plugin);
		if(self::nav('action') == 'config')
			return self::edit($plugin);
		
		if($Plugin = PluginController::getPlugin($plugin))
			return $Plugin -> run($plugin);		
		
		return self::showAll();
	}
	
	public static function showAll() {

		$tPlugins = PluginController::getPlugins();
		$tablePlugins = '<table cellpadding="0" cellspacing="0" class="Data Plugins">';
		
		foreach($tPlugins as $plugin) {
			
			$Uninstall = new Button('Defaut', 'Désinstaller', false);
			$Uninstall -> setUrl(array('action' => 'uninstall', 'plugin' => $plugin['id']));
			
			$Config = new Button('Defaut', 'Configurer', false);
			$Config -> setUrl(array('action' => 'config', 'plugin' => $plugin['id']));
			
			$Install = new Button('Defaut', 'Installer', false);
			$Install -> setUrl(array('action' => 'install', 'plugin' => $plugin['id']));
			
			$tablePlugins .= '
			<tr>
				<td>
					<h2>' . $plugin['title'] . '</h2>
					<div class="Version">Version ' . $plugin['version'] . '</div>
					<div class="Author">Par ' . $plugin['author'] . '</div>
					<div class="Description">' . $plugin['description'] . '</div>
					<ul class="Buttons Actions">
					' . ($plugin['installed'] ?	$Uninstall -> display() . $Config -> display() : $Install -> display()) . '
					</ul>
				</td>
			</tr>';
		}
		$tablePlugins .= '</table>';
		return $tablePlugins;
	}

	public static function getFolder($p = NULL) {
		global $_baseUrl;

		if($p == NULL)
			$p = self::nav('plugin');
			
		$strSql = '
		SELECT * FROM 
			`' . Sql::buildTable(T_CFG_PLUGINS) . '` 
		WHERE `id` = ' . intval($p) . ' OR `name` = "' . Sql::secure($pluginId) . '" LIMIT 1';

		if($resSql = self::query($strSql)) {
			if(mysql_num_rows($resSql) == 0) {
				$p = preg_replace('![^a-zA-Z0-9_]!', '', $p);
			} else if($dataSql = mysql_fetch_assoc($resSql))
				$p = $dataSql['name'];
		}
		return $_baseUrl . FOLDER_PLUGINS . $p . '/';
	}
	
	public static function edit($id) {
		
		$parameters = PluginController::getParams($id);
		$newParameters = $parameters;
		
		if(!empty($_POST['formEntry'])) {


			$strSql = '
				INSERT INTO 
					`' . Sql::buildTable(T_CFG_PLUGINS_PARAMETERS) . '`
					(`idx_plugin`, `key`, `value`) 
				VALUES
					%s
				ON DUPLICATE KEY UPDATE
					`value` = "%s"
					';
			$strEntries = NULL;
		
			foreach($parameters as $key => $value) {
		
				self::query(sprintf($strSql,
				
				'(' . $id . ', "' . $key . '", "' . Sql::secure((isset($_POST[$key]) ? $_POST[$key] : $value['value'])) . '")',
				Sql::secure((isset($_POST[$key]) ? $_POST[$key] : $value['value'])) ));
				
				
				
				
				$newParameters[$key]['value'] = (isset($_POST[$key]) ? $_POST[$key] : $value['value']);
			}

			$pluginFile = include(self::getFolder() . '/install.php');
			$Installer = new PluginInstaller();
			$Installer -> edit($parameters, $newParameters);
		}
		
		return  self::paramForm($parameters);	
	}

	public static function install($id) {
		
		$pluginFile = include(self::getFolder() . '/install.php');
		$Installer = new PluginInstaller();
		self::$Installer = $Installer;

//		$parameters = PluginController::getParams(self::nav('plugin'), true);
		$parameters = PluginController::getParams($id);

		if(!empty($_POST['formEntry'])) {
			
			$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_PLUGINS) . '` WHERE `name` = "' . self::nav('plugin') . '" LIMIT 1';
			if($resSql = self::query($strSql))
				if(mysql_num_rows($resSql) > 0)
					return self::log();
			
			$strSql = '
			INSERT INTO 
				`' . Sql::buildTable(T_CFG_PLUGINS) . '`
				(`name`, `installed`) VALUES ("' . self::nav('plugin') . '", 1)
			';
			if(!self::query($strSql))
				return self::log();
			$pluginId = mysql_insert_id();
			
			$strSql = '
			INSERT INTO 
				`' . Sql::buildTable(T_CFG_PLUGINS_PARAMETERS) . '`
				(`idx_plugin`, `key`, `value`) 
			VALUES
				';
		
			$strEntries = NULL;
			foreach($parameters as $key => $value)
				$strEntries .= ', (' . $pluginId . ', "' . $key . '", "' . Sql::secure((isset($_POST[$key]) ? $_POST[$key] : $value['value'])) . '")
				';
			
			if(self::query($strSql . substr($strEntries, 1))) {
				$Installer -> install($pluginId);
			}
			
			//self::redirect(array('mode' => 'plugin'), true);
		}

		return self::paramForm($parameters);
	}	

	public static function paramForm($parameters) {
				
		$strHtml = Instance::getInstance('Message') -> display();
			
		$Plugin = PluginController::getPlugin(self::nav('plugin'));	
		//$tCfg = $key == NULL ? $this -> tCfg : array($key => $this -> tCfg[$key]);
					
		$Form = new Form();
		$Form -> addLang('', 'Configuration du plugin');
		$Form -> setUrl('');
		$Form -> setClass('DataForm');

		foreach($parameters as $key => $details) {
			
			if(!Security::userAdmin() && $details['admin'] == 1)
				continue;

			$field = Field::buildFromType($details['type']);
			$field -> setName($key);
			$field -> oneField(true);

			$content = $field -> showField($details['value']);//self::$Installer -> parameterField($key, $details, $field);

			$FormField = $Form -> addField($key, '', false);
			$FormField -> setTitle($details['label']);
			$FormField -> setField($content);
		}
		
		return $strHtml . $Form -> display();
	}
}

?>