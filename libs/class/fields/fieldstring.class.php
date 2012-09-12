<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldString extends Field {
	
	const fieldModel = '<input type="text" name="%s" value="%s" class="%s" placeholder="%s" />';
	
	public function __construct($dataSql = NULL) {
		$this -> modFields[''] = 'VARCHAR( 250 ) NOT NULL';
		parent::__construct($dataSql);	
	}
	
	public function check($value) {
		if($this -> isRequired() && $value == NULL)
			return $this -> errors['empty'];
		
		return false;
	}
	
	
	public function treat($value) {		
		return $value;
	}
	
	public function showField($value, $error = false) {

		$class = array();		
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'String';
		$class = implode(' ' , $class);

		return sprintf(
			self::fieldModel, 
			$this -> formName(), 
			stripslashes(htmlspecialchars($value)), 
			$class, 
			$this -> placeholder
		);
	}
}


?>