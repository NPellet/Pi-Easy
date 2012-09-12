<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldLink extends Field {
	
	const fieldModel = '
	<table cellpadding="0" cellspacing="0" class="TableUrlEmail">
		<tr>
			<td class="Label">Adresse du lien</td><td>
			<input type="text" name="%s[url]" value="%s" class="%s" pattern="(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)?([\w\-\.,@?^=%%;&amp;:/~\+#]*[\w\-\@?^=%%;&amp;/~\+#])?"/>
			</td>
		</tr>
		<tr>
			<td class="Label">Texte à afficher</td><td><input type="text" name="%s[text]" value="%s" class="%s" /></td>
		</tr>
		<tr>
			<td class="Label">Le lien s\'ouvrira dans</td><td>
			<select name="%s[target]" class="%s">
				<option value="_parent"%s>la même fenêtre</option>
				<option value="_blank"%s>une nouvelle fenêtre</option>
			</select>
			</td>
		</tr>
	</table>';
	
	
	public function __construct($dataSql) {
		parent::__construct($dataSql);	
		$this -> modFields = array();
		$this -> modFields['url'] = 'VARCHAR( 250 ) NOT NULL';
		$this -> modFields['text'] = 'VARCHAR( 250 ) NOT NULL';
		$this -> modFields['target'] = 'VARCHAR( 250 ) NOT NULL';
		$this -> errors['format'] = 'Le lien du champ %s n\'a pas un format escompté. Veuillez vérifier la syntaxe de l\'URL';

	}
	
	public function check($value) {
		if($this -> isRequired() && empty($value['url']))
			return $this -> errors['empty'];
		
		if(!empty($value['url']) && !filter_var($value['url'], FILTER_VALIDATE_URL))
			return $this -> errors['format'];
			
		return false;
	}
	
	
	public function treat($value) {		
		return $value;
	}
	
	public function display($value) {
		return '<a href="' . $value['url'] . '" target="_blank">' . ($value['text'] ? $value['text'] : $value['url']) . '</a>';
	}
	
	public function showField($value, $error = false) {

		$class = array();		
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'Link';
		$class = implode(' ' , $class);

		$fieldName =  $this -> formName();
		return sprintf(self::fieldModel, 
				$fieldName, 
				$value['url'], 
				$class, 
				$fieldName, 
				$value['text'], 
				$class, 
				$fieldName, 
				$class, 
				$value['target'] == '_parent' ? ' selected="selected"' : NULL,
				$value['target'] == '_blank' ? ' selected="selected"' : NULL
		);
	}
}


?>