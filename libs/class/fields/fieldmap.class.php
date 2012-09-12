<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldMap extends Field {
	
	const fieldModel = '
	<div class="FieldMap">
	<table cellpadding="0" cellspacing="0">
		<tr>
			<td class="Label">Nom</td><td><input type="text" class="Name" name="%s[nom]" value="%s" /></td>
		</tr>
		<tr>
			<td class="Label">Adresse</td><td><input type="text" class="Address" name="%s[adresse]" value="%s" /></td>
		</tr>
		<tr>
			<td class="Label">NPA</td><td><input type="text" class="ZIP" name="%s[npa]" value="%s" /></td>
		</tr>
		<tr>
			<td class="Label">Localité</td><td><input type="text" class="City" name="%s[localite]" value="%s" /></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" value="Générer la carte" class="getMap" /></td>
		</tr>
	</table>
	<div class="GoogleMap">
	</div>
	</div>
	';
	
	public function __construct($dataSql = NULL) {
		
		$this -> errors['empty'] = 'Une propriété de la localité n\'a pas été définie';
		parent::__construct($dataSql);	
		$this -> modFields = array();
		$this -> modFields['nom'] = 'VARCHAR( 250 ) NOT NULL';
		$this -> modFields['adresse'] = 'VARCHAR( 250 ) NOT NULL';
		$this -> modFields['npa'] = 'VARCHAR( 250 ) NOT NULL';
		$this -> modFields['localite'] = 'VARCHAR( 250 ) NOT NULL';
	}
	
	public function check($value) {
		
		if($this -> isRequired() && ($value['nom'] == NULL || $value['adresse'] == NULL || $value['npa'] == NULL || $value['localite'] == NULL))
			return $this -> errors['empty'];
		return false;
	}
	
	
	public function treat($value) {
		return $value;
	}
	
	public function display($value) {
		return implode('<br />', $value);
		
	}
	public function showField($value, $error = false) {

		$class = array();		
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'String';
		$class = implode(' ' , $class);

		$name = $this -> formName();
	
		return sprintf(
			self::fieldModel, 
			$name,
			$value['nom'],
			$name,
			$value['adresse'],
			$name,
			$value['npa'],
			$name,
			$value['localite']
		);
	}
}


?>