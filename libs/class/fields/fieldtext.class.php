<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldText extends Field {
	
	const fieldModel = '<textarea name="%s" class="%s">%s</textarea>';
	protected $text_mode;

	public function __construct($dataSql = NULL) {
		parent::__construct($dataSql);	
		$this -> text_mode = !empty($dataSql['text_mode']) ? $dataSql['text_mode'] : 'Strict';
		$this -> modFields[''] = 'TEXT';
	}
	
	public function setMode($strMode) { if(in_array($strMode, array('Wysiwyg', 'WysiwygExtended', 'Html', 'Strict'))) { 
		$this -> text_mode = $strMode; }}
	
	public function check($value) {
		
		if($this -> isRequired() && $value == NULL)
			return $this -> errors['empty'];
		
		return false;
	}
	
	public function treat($value) {

		switch($this -> text_mode) {

			case 'Wysiwyg':
			case 'WysiwygExtended':
				$value = utf8_encode(html_entity_decode($value));
			break;
		
			case 'Html':
				$value = html_entity_decode($value);
			break;
			
			case 'Strict':
				$value = htmlspecialchars($value);
			break;
		}
		
		return $value;
	}
	
	public function showField($value, $error = false) {

		$class = array();		
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'Text';
		
		switch($this -> text_mode) {
			case 'Wysiwyg':
				$class[] = 'WysiwygSimple';
			break;
			
			case 'WysiwygExtended':
				$class[] = 'WysiwygExtended';
			break;
			
			default:
				$class[] = 'Standard';
			break;
		}
		
		$class = implode(' ', $class);

		return sprintf(
			self::fieldModel, 
			$this -> formName(), 
			$class, 
			stripslashes(html_entity_decode($value))
		);		
	}
	
	public function display($value) { return $value; /*return substr($value, 0, 200);*/ }
	public function configFields() {
		extract($_POST);
		return array(
			'text_mode' => $fieldTextMode
		);
	}
}

?>