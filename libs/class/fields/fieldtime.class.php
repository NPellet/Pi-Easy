<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldTime extends Field {
	
	const fieldModel = '<select name="%s" class="%s">%s</select> : <select name="%s" class="%s">%s</select>';
	
	public function __construct($dataSql = NULL) {
		parent::__construct($dataSql);	
		$this -> modFields[''] = 'TIME';
	}
	
	public function check($value) {
		$date = explode('.', $value);
		if($this -> isRequired() && ($value['hour'] == NULL || $value['minute'] == NULL))
			return $this -> errors['empty'];
		
		return false;
	}
	
	public function treat($value) {
		return $value['hour'] . ':' . $value['minute'] . ':00';
	}
	
	public function display($value) {
		if($value == NULL)
			return 'N/A';
			
		$time = explode(':', $value);
		return $time[0] . 'h' . $time[1];
	}
	
	
	public function showField($value, $error = false) {

		$class = array();		
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'Date';
		$class = implode(' ' , $class);
		
		if($value == NULL)
			$value = array('hour' => '', 'minute' => '');
		else {
			$value = explode(':', $value);
			$value['hour'] = $value[0];
			$value['minute'] = $value[1];
		}

		$optsHours = '<option value="">Heures</option>';
		for($i = 0; $i <= 23; $i++)
			$optsHours .= '<option value="' . $i . '"' . ($value['hour'] == $i ? ' selected="selected"' : '') . '>' . $i . '</option>';
		
		$optsMinutes = '<option value="">Minutes</option>';
		for($i = 0; $i <= 55; $i += 5)
			$optsMinutes .= '<option value="' . $i . '"' . ($value['minute'] == $i ? ' selected="selected"' : '') . '>' . $i . '</option>';
		
		return sprintf(self::fieldModel,
			$this -> formName() . '[hour]',
			$class,
			$optsHours,
			$this -> formName() . '[minute]',
			$class,
			$optsMinutes);
	}
}

?>