<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldNumeric extends Field {
	
	const fieldModel = '<input type="text" name="%s" value="%s" class="%s" placeholder="%s" pattern="%s" />';
	private $float;
	
	public function __construct($dataSql) {
		parent::__construct($dataSql);	
		$this -> float = !empty($dataSql['num_float']) ? (bool) $dataSql['num_float'] : false;
		$this -> modFields[''] = $this -> float ? 'FLOAT' : 'INT';
		$this -> errors['format'] = 'La valeur entrée dans le champ %s n\'est pas un nombre';
	}
	
	public function check($value) {
		
		if($this -> isRequired() && $value == NULL)
			return $this -> errors['empty'];
		
		if(!is_numeric($value) && $value != NULL)
			return $this -> errors['format'];
			
		return false;
	}
	
	public function treat($value) {
		return $this -> float ? floatval($value) : intval($value);
	}
	
	public function showField($value, $error = false) {

		$class = array();		
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'Numeric';
		$class = implode(' ' , $class);

		return sprintf(
			self::fieldModel, 
			$this -> formName(), 
			$value, 
			$class, 
			$this -> placeholder,
			$this -> float ? '[0-9\.]+' : '[0-9]+'
		);
	}
	
	public function configFields() {
		
		extract($_POST);
		return array(
			'num_float' => isset($fieldNumericFloat) ? true : false,
		);
	}

}

?>