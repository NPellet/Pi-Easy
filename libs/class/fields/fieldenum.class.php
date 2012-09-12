<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldEnum extends Field {
	
	protected $enumList = array();

	public function __construct($dataSql = NULL) {
		parent::__construct($dataSql);
		$this -> modFields[''] = 'ENUM(%s) NOT NULL';
		$this -> getList();
	}
	
	private function getList() {

		if($this -> getModule()) {
			$strSql = 'SHOW COLUMNS FROM `' . $this -> getModule() -> getTable() . '` LIKE "' . $this -> name . '%"';
			if($resSql = $this -> query($strSql)) {
				while($dataSql = mysql_fetch_assoc($resSql)) {
					foreach(array_keys($this -> getLangs()) as $lang) {
						if($dataSql['Field'] == $this -> sqlName()) {
							preg_match_all('!\'([^\']*)\',?!s', $dataSql['Type'], $matches);
							$this -> enumList[$lang] = $matches[1];
						}
					}
				}
			}
		}
	}
	
	public function setList($tList) { $this -> enumList = $tList; }
	public function configFields() {
		
		extract($_POST);
		$tList = array();
		
		if($this -> multilang)
			foreach($fieldEnum as $lang => $enum)
				foreach($enum as $enumElement)
					$tList[$lang][] = "'" . $enumElement . "'";
		else
			foreach($fieldEnum as $enumElement)
				$tList[''][] = "'" . $enumElement . "'";

		$this -> enumList = $tList;
		return array();
	}
	
	public function treat($value) { return $value; }
	public function check($value) { return false; }
	
	public function display($value) { return $value; }
	public function showField($value, $combine = true) {
	
		$name = $this -> formName();

		if(!empty($this -> enumList[$this -> lang])) {
			
			$enum = $this -> enumList[$this -> lang];
			if($combine)
				$enum = array_combine($enum, $enum);
			
			$list = Html::buildList($enum, $value, true);
		} else
			$list = NULL;
		
		$class[] = 'Field';
		$class[] = 'Link';
		$class = implode(' ' , $class);

		return '<select class="' . $class . '" name="' . $name . '">' . $list . '</select>';
	}
	
	public function getSqlType($suffix) {
		return !empty($this -> enumList[$this -> lang]) ?
			sprintf($this -> modFields[''], implode(',', $this -> enumList[$this -> lang])) :
			sprintf($this -> modFields[''], '');
	}
}

?>