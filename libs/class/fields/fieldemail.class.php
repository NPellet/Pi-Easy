<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldEmail extends Field {
	
	const fieldModel = '
	<table class="TableUrlEmail" cellpadding="0" cellspacing="0">
	<tr>
		<td class="Label">Adresse e-mail</td>
		<td><input type="text" name="%s[email]" value="%s" class="%s" pattern="[\w\.=-]+@[\w\.-]+\.[\w]{2,3}"/></td>
	</tr>
	<tr>
		<td class="Label">Texte</td>
		<td>
			<input type="text" name="%s[text]" value="%s" class="%s" />
		</td>
	</tr>
	</table>';
	
	
	public function __construct($dataSql) {
		parent::__construct($dataSql);	
		$this -> modFields = array();
		$this -> modFields[''] = 'VARCHAR( 250 ) NOT NULL';
		$this -> modFields['text'] = 'VARCHAR( 250 ) NOT NULL';
		$this -> errors['format'] = 'Le lien du champ %s n\'a pas un format escompté. Veuillez vérifier la syntaxe de l\'E-mail';

	}
	
	public function check($value) {
		if($this -> isRequired() && empty($value['email']))
			return $this -> errors['empty'];
		
		if(!empty($value['url']) && !filter_var($value['email'], FILTER_VALIDATE_EMAIL))
			return $this -> errors['format'];
			
		return false;
	}
	
	
	public function treat($value) {
		$value[''] = $value['email'];
		return $value;
	}
	
	public function showField($value, $error = false) {

		$class = array();		
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'Email';
		$class = implode(' ' , $class);

		$fieldName =  $this -> formName();
		return sprintf(self::fieldModel, 
				$fieldName, 
				$value[''], 
				$class, 
				$fieldName, 
				$value['text'], 
				$class
		);
	}
	
	public function display($value) {
		
		if(is_string($value))
			return $value;
		return '<a href="mailto:' . $value[''] . '">' . (!empty($value['text']) ? $value['text'] : $value['']) . '</a>';
	}
}


?>