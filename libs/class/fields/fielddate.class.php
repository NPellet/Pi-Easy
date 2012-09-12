<?php

class FieldDate extends Field {
	
	const fieldModel = '<input type="text" name="%s" value="%s" class="%s" placeholder="%s" />';
	
	public function __construct($dataSql = NULL) {
		parent::__construct($dataSql);	
		$this -> modFields[''] = 'DATE';
		$this -> errors['format'] = 'La date du champ %s n\'a pas un format escompté. Elle doit être de format DD.MM.YYYY';
	}
	
	public function check($value) {
		$date = explode('.', $value);
		if($this -> isRequired() && $value == NULL)
			return $this -> errors['empty'];
		
		if(!empty($value) && @!checkdate($date[1], $date[0], $date[2]))
			return $this -> errors['format'];
		return false;
	}
	
	public function treat($value) {
		
		$date = explode('.', $value);
		if(count($date) == 3)
			return $date[2] . '-' . $date[1] . '-' . $date[0];
		else
			return NULL;
	}
	
	public function display($value) {
		if($value == '0000-00-00')
			return 'Aucune date';
			
		$date = explode('-', $value);
		if(count($date) == 3)
			return $date[2] . '.' . $date[1] . '.' . $date[0];

		return date('d', $value) . '.' . date('m', $value) . '.' . date('Y', $value);
	}
	
	
	public function showField($value, $error = false) {

		$class = array();		
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'Date';
		$class = implode(' ' , $class);

		if($value != '0000-00-00' && $value != NULL) {
			$date = explode('-', $value);
			$value = $date[2] . '.' . $date[1] . '.' . $date[0];
		} else {
			$value = NULL;
		}

		return sprintf(self::fieldModel, $this -> formName(), $value, $class, $this -> placeholder);
	}
}

?>