<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldSelectField extends Field {
	
	public static $data = array();
	protected $Module;
	
	public function setModule($Module) {
		$this -> Module = $Module;
	}
	
	public function treat($value) { return $value; }
	public function check($value) { return false; }
	
	public function display($value) { return $value; }
	
	public function showField($value) {
		
		if(!empty($Module)) {
			$Fields = $Module -> getFields();
			$Modules = array($Module);
		} else {
			$Modules = Module::getAll();
			$Fields = Field::getAll();
		}
		
		$opts = array();
		
		$opts = array('' => 'Aucun');
		foreach($Modules as $Module)	
			foreach($Fields as $Field)
				if($Field -> getIdxModule() == $Module -> getId())
					$opts[$Module -> getLabel()][$Field -> getId()] = $Field -> getLabel();	
		
		$class[] = 'Field';
		$class[] = 'Idx';
		$class = implode(' ', $class);
		$name = $this -> formName();
		
		return '
		<select class="' . $class . '" name="' . $name . '">
			' . Html::buildList($opts, $value) . '
		</select>';
	}
}

?>