<?php

class PluginManager extends Security {
	
	public static function run() {

		if($Plugin = PluginController::getPlugin($this -> nav('plugin')))
			return $Plugin -> run();		
		
		if(!empty($this -> nav('plugin')))
			return self::install();
			
		self::showAll();
	}
	
	public function showAll() {
	
		$tPlugins = PluginController::getPlugins();
		$tablePlugins = '<table cellpadding="0" cellspacing="0" class="Data Plugins">';
		
		foreach($tPlugins as $plugin) {
			
			$tablePlugins .= '
			<tr>
				<td>
					<h2>' . $plugin['title'] . '</h2>
					<div class="Version">Version ' . $plugin['version'] . '</div>
					<div class="Author">Par ' . $plugin['author'] . '</div>
					<div class="Description">' . $plugin['description'] . '</div>
					<div class="Buttons">
					' . ($plugin['installed'] ? 
						'<a href="' . url(array('action' => 'uninstall', 'plugin' => $plugin['id'])) . '">
							<div class="Button">Désinstaller</div>
						</a>
						<a href="' . url(array('action' => 'configure', 'plugin' => $plugin['id'])) . '">
							<div class="Button">Configurer</div>
						</a>'
					:
						'<a href="' . url(array('action' => 'install', 'plugin' => $plugin['id'])) . '">
							<div class="Button">Installer</div>
						</a>'
					) . '
					</div>
				</td>
			</tr>';
		}
		$tablePlugins .= '</table>';
		return $tablePlugins;
	}
	
	
	public function install() {
		
		$parameters = PluginController::getParams($this -> plugin);

		if(!empty($_POST['formInstallPlugin'])) {
			
			$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_PLUGINS) . '` WHERE `name` = "' . $this -> plugin . '" LIMIT 1';
			if($resSql = $this -> query($strSql))
				if(mysql_num_rows($resSql) > 0)
					return $this -> log();
			
			$strSql = '
			INSERT INTO 
				`' . Sql::buildTable(T_CFG_PLUGINS) . '`
				(`name`, `installed`) VALUES ("' . $this -> plugin . '", 1)
			';
			
			if(!$this -> query($strSql))
				return $this -> log();
			$pluginId = mysql_insert_id();
			
			$strSql = '
			INSERT INTO 
				`' . Sql::buildTable(T_CFG_PLUGINS_PARAMETERS) . '`
				(`idx_plugin`, `key`, `value`) 
			VALUES
				';
				
			$strEntries = NULL;
			foreach($parameters as $key => $value)
				$strEntries .= ', (' . $pluginId . ', "' . $key . '", "' . Sql::secure((!empty($_POST[$key]) ? $_POST[$key] : $value)) . '")
				';
			
			if($this -> query($strSql . substr($strEntries, 1))) {
				$pluginFile = include(FOLDER_PLUGINS . $this -> plugin . '/install.php');
				$Installer = new PluginInstaller();
				$Installer -> install($pluginId);
			}
			
			$this -> redirect(array('mode' => 'plugin'), true);
		}
		
		$strForm = '<form method="post">';
		
		foreach($parameters as $key => $value) {
			
			$Field = new FieldString();
			$Field -> setName($key);

			$strForm .= '
			<div class="FieldWrapper">
				<div class="Title">
					Label
				</div>				
				<div class="Field">
					' . $Field -> showField($value) . '	
				</div>
			</div>';
		}
		
		$strForm .= '<input type="submit" name="formInstallPlugin" value="Installer" /></form>';
		$strTitle = '<h2>Installer un nouveau plugin</h2>';
		
		return $strTitle . $strForm;
	}	


}

?>