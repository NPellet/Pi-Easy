<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Instance {
	
	private static $instances = array();
	
	public static function setInstance($Instance, $instanceName = false) {
		if(!$instanceName)
			$instanceName = get_class($Instance);

		self::$instances[$instanceName] = $Instance;
		return $Instance;
	}
	
	public static function getInstance($instanceName) {
		
		if(!empty(self::$instances[$instanceName]))
			return self::$instances[$instanceName];
		
		return false;
	}
	
	// Alias
	
	public function query($strSql) {
		return self::getInstance('Sql') -> query($strSql);
	}
	
	public function log($message = NULL, $active = true) {
		return self::getInstance('Message') -> log($message, $active);	
	}

	public function url($nav, $reset = false) {
		return self::getInstance('Navigation') -> url($nav, $reset);	
	}

	public function redirect($url) {
		return self::getInstance('Navigation') -> redirect($url);	
	}
	
	public function nav($mode) {
		return self::getInstance('Navigation') -> nav($mode);
	}
}

?>