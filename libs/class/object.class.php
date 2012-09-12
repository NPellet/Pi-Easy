<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Object extends Security {
	
	/*public static function __call($funcName, $params) {

		$class = get_called_class();
		$tClasses = array();
		
		if(preg_match('!^buildFrom[a-zA-Z]+$!is', $funcName)) {
			
			$fields = explode('And', str_replace('buildFrom', '', $funcName));
			$i = 0;
			foreach($fields as $field) {
				$strSql[] = '`' . strtolower($field) . '` = "' . Sql::secure($params[$i]) . '"';
				$i++;
			}

			$strSql = 'SELECT * FROM `' . Sql::buildTable(constant('T_CFG_' . strtoupper($class))) . '` WHERE ' . implode(' AND ', $strSql);

			if($resSql = Sql::query($strSql))
				while($dataSql = mysql_fetch_assoc($resSql))
					$tClasses[] = new $class($dataSql);
		}		
		return count($tClasses) == 1 ? $tClasses[0] : (count($tClasses) == 0 ? new $class : $tClasses);
	}
	*/
	
	public function saveOrder($intOrder, $id, $class = false, $added = NULL) {
		
		if(!$class)
			$class = @get_class($this);
//		$class = get_called_class();

		$tableName = Sql::buildTable(constant('T_CFG_' . strtoupper($class)));
		self::order($tableName, $intOrder, $id, $added);
	}
	
	public static function order($tableName, $intOrder, $id, $added) {
		
		if($intOrder !== 'last') {
			$strSql = 'SELECT * FROM `' . $tableName . '` WHERE `order` = ' . intval($intOrder) . ' ' . $added . ' AND `id` != ' . $id . ' LIMIT 1';
			if($resSql = self::query($strSql))
				if($dataSql = mysql_fetch_assoc($resSql))
					Object::order($tableName, $intOrder + 1, $dataSql['id'], $added);
		} else {
			$strSql = 'SELECT MAX(`order`) as maxOrder FROM `' . $tableName . '`';
			if($resSql = self::query($strSql)) {
				if($dataSql = mysql_fetch_assoc($resSql)) {
					$intOrder = $dataSql['maxOrder'] + 1;	
				}
			}
		}

		$strSql = 'UPDATE `' . $tableName . '` SET `order` = ' . intval($intOrder) . ' WHERE `id` = ' . intval($id) . ' LIMIT 1';

		self::query($strSql);
	}
}

?>