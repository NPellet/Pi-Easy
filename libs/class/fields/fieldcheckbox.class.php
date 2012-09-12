<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldCheckbox extends Field {
	
	const fieldModel = '<input type="checkbox" name="%s" class="%s" %s />';
	
	public function __construct($dataSql = NULL) {
		$this -> modFields[''] = 'VARCHAR( 250 ) NOT NULL';
		parent::__construct($dataSql);	
	}
	
	public function check($value) {
		return false;
	}
		
	public function treat($value = NULL) {
		return $value != NULL ? 1 : 0;
	}
	
	public function showField($value, $error = false) {

		$class = array();		
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'String';
		$class = implode(' ', $class);

		return sprintf(self::fieldModel, $this -> sqlName(), $class, !empty($value) ? ' checked="checked"' : NULL);
	}
}


?>