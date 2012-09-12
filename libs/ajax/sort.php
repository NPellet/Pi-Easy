<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

if(!empty($_REQUEST['sort'])) {
	
	$sort = $_REQUEST['sort'];
	$moduleId = $_REQUEST['module'];

	if($Module = Module::buildFromId($moduleId)) {
		foreach($sort as $id => $position) {
			$strSql = 'UPDATE `' . $Module -> getTable() . '` SET `order` = ' . intval($position) . ' WHERE `id` = ' . intval($id) . ' LIMIT 1';
	//		echo $strSql;

			Sql::query($strSql);
		}
	}
	
}

?>