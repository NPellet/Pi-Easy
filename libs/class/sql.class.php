<?php

class Sql extends Security {

	
	public static function secure($str) {
		return mysql_real_escape_string($str);
	}
	
	public static function buildTable($table) {
		return T_PREFIX . Sql::FormatDB(@NOM_SITE) . '_' . $table;
	}
	
	public static function buildSave($t, $id, $cfg, $cfgUpdate = NULL) {
		
		if($cfgUpdate == NULL)
			$cfgUpdate = $cfg;
		
		$strInsert = NULL;
		$strUpdate = NULL;
		$strValues = NULL;
		
		if($id != NULL) {
			$strInsert .= ', `id`';
			$strValues .= ', ' . intval($id);
		}
		foreach($cfg as $key => $value) {
			if(is_bool($value))
				$value = (int) $value;
				
			$value = self::secure($value);
			$key = self::secure($key);
			
			$strInsert .= ', `' . $key. '`';
			$strValues .= ', "' . $value . '"';
		}
		
		foreach($cfgUpdate as $key => $value) {
			if(is_bool($value))
				$value = (int) $value;
				
			$value = self::secure($value);
			$key = self::secure($key);
			
			$strUpdate .= ', `' . $key . '` = "' . $value . '"';
		}
	
		$strInsert = substr($strInsert, 2);
		$strValues = substr($strValues, 2);
		$strUpdate = substr($strUpdate, 2);
		
		$strSql = '
		INSERT INTO `' . self::buildTable($t) . '` 
			(' . $strInsert . ')
		VALUES 
			(' . $strValues . ')
		ON DUPLICATE KEY UPDATE 
			' . $strUpdate;

		return $strSql;
	}
	
	public function query($strSql) {

		return mysql_query($strSql);
		
		if($resSql = mysql_query($strSql))
			return $resSql;
		else
			// On ne log pas l'erreur. Certaines requ�tes peuvent volontairement �chouer
			return false;
	}
	
	public static function connect($host = false, $user = false, $password = false, $db = false) {

		if($host === false) $host = DB_HOST;
		if($user === false) $user = DB_USER;
		if($password === false) $password = DB_PASSWORD;
		if($db === false) $db = DB_NAME;
		
		if(@mysql_connect($host, $user, $password))
			if(@mysql_select_db($db))
				return true;
			else
				return Instance::getInstance('Message') -> log('sql:0:1');
		else
			return Instance::getInstance('Message') -> log('sql:0:2');
	}
	
	public static function FormatDB($str) {
	
		$strSubChar = '_';	
		$str = strtolower($str);
		$t_Search = array('|[����]|i','|[���]|i','|[��]|i','|[���]|i','|[��]|i','|[�]|i','|[^a-zA-Z0-9]|');
		$t_Replace = array('e','a','i','u','o','c', $strSubChar);
		$str = preg_replace($t_Search, $t_Replace, $str);		
		$str = preg_replace('|' . $strSubChar . '+|', $strSubChar, $str);	
		return $str;		
	}
}

?>