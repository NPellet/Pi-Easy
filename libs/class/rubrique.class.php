<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Rubrique extends Object {
	
	private $id, $idx_module, $order, $Module, $labels = array();
	
	public static function buildFromId($id) {
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_RUBRIQUE) . '` WHERE `id` = ' . intval($id) . ' LIMIT 1';
		if($resSql = Sql::query($strSql))
			if($dataSql = mysql_fetch_assoc($resSql))
				$obj = new Rubrique($dataSql);
			else
				$obj = new Rubrique();
		return $obj;
	}
	
	public function __construct($dataSql = NULL) {

		if(empty($dataSql))
			return;
			
		extract($dataSql);
		$this -> id = $id;
		$this -> idx_module = $idx_module;
		$this -> order = $order;
		
		$this -> Module = Module::buildFromId($idx_module);
		
		if($this -> id != NULL) {
			$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_RUBRIQUE_LANGS) . '` WHERE `idx_rubrique` = ' . intval($this -> id);
			if($resSql = $this -> query($strSql)) {
				while($dataSql = mysql_fetch_assoc($resSql))
					$this -> labels[$dataSql['lang']] = $dataSql['label'];
			} else
				return $this -> log('sql:query');
		}
	}
	
	public function save() {
		
		$sql = array();
		$sql['idx_module'] = $this -> idx_module;
		//$sql['order'] = 1;
		
		if($this -> query(Sql::buildSave(T_CFG_RUBRIQUE, $this -> id, $sql))) {
			if($this -> id == NULL)
				$this -> id = mysql_insert_id();
				
			foreach($this -> Module -> getLangs() as $abr => $lang) {
				$sql = array();				
				$sql['label'] = $this -> labels[$abr];
				$sql['idx_rubrique'] = $this -> id;
				$sql['lang'] = $abr;
				if(!$this -> query(Sql::buildSave(T_CFG_RUBRIQUE_LANGS, NULL, $sql)))
					$this -> log('sql:query');
			}
		}
	}
	
	public function setLabel($langAbr, $label) { $this -> labels[$langAbr] = $label; }
	public function setModule($Module) { $this -> Module = $Module; $this -> idx_module = $Module -> getId(); }
	
	public function getId() { return $this -> id; }
	public function getLabel($strLang = NULL) { 
//print_r($this -> labels);
		if($strLang == NULL)
			return current($this -> labels);		
		else
			return $this -> labels[$strLang]; 
	}
	
	public function getModule() {
		return $this -> Module;
	}
	
	public function remove() {
	
		if(!$this -> id) 
			return $this -> log();

		/* Etape 1: Changer les entrées dans le module */
		$strSql = 'UPDATE `' . $this -> Module -> getTable() . '` SET `idx_rubrique` = 0 WHERE `idx_rubrique` = ' . $this -> id;
		if(!$this -> query($strSql))
			return $this -> log();
		
		/* Etape 2 : Supprimer les entrées dans la table langues */
		$strSql = 'DELETE FROM `' . Sql::buildTable(T_CFG_RUBRIQUE_LANGS) . '` WHERE `idx_rubrique` = ' . $this -> id;
		if(!$this -> query($strSql))
			return $this -> log();
		
		/* Etape 3 : Supprimer la rubrique */
		$strSql = 'DELETE FROM `' . Sql::buildTable(T_CFG_RUBRIQUE) . '` WHERE `id` = ' . $this -> id . ' LIMIT 1';
		if(!$this -> query($strSql))
			return $this -> log();
		
		return true;
	}
	
	public static function getAll() {
		$tRubriques = array();
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_RUBRIQUE) . '` ORDER BY `order` ASC';
		if($resSql = self::query($strSql)) {
			while($dataSql = mysql_fetch_assoc($resSql)) {
				$tRubriques[] = new Rubrique($dataSql);	
			}
			return $tRubriques;
		}
		
		return $this -> log('sql:query');
	}
}

?>