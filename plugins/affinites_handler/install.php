<?php

class PluginInstaller extends PluginController {
	
	public function edit($oldParams, $params) {
		return true;
	}
	
	public function install($pluginId) {
		return true;
	}
	
	public function uninstall($pluginId) {
		return true;
	}
}

?>