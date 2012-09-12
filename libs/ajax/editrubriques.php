<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
exit(0);

$data = $_GET['rub'];

$Module = Module::buildFromId($_GET['idx_module']);


foreach($data as $order => $details) {
	
	$id = $details['id'];
	echo $id;
	
	if($id == '') {
		$idRubrique = 0;
		if(count($details['content']) > 0)
			foreach($details['content'] as $id) {
				$strSql = 'UPDATE `'  . $Module -> getTable() . '` SET `idx_rubrique` =  ' . $idRubrique . ' WHERE `id` = ' . $id . ' LIMIT 1';
				echo $strSql;
				Sql::query($strSql);
			}
		
	} else {
		
		if($id == 'new') {
			$Rubrique = new Rubrique(array('id' => NULL, 'order' => $order, 'idx_module' => $Module -> getId()));
			$Rubrique -> save();
			$idRubrique = $Rubrique -> getId();
		} else {
			$idRubrique = $id;
			$Rubrique = Rubrique::buildFromId($id);	
		}
		
		foreach($details['label'] as $lang => $text)
			$Rubrique -> setLabel($lang == 0 ? '' : $lang, $text);
		
		$Rubrique -> save();
				
		if(!empty($details['content']))
			foreach($details['content'] as $id) {
				$strSql = 'UPDATE `'  . $Module -> getTable() . '` SET `idx_rubrique` =  ' . $idRubrique . ' WHERE `id` = ' . $id . ' LIMIT 1';
				Sql::query($strSql);
			}
	
		if($details['removed'] == 'true') {
			$Rubrique -> remove();
		}
	}
}


?>