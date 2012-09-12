<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');
	
class Form {
	
	private $langs = array();
	private $fields = array();
	private $appended = array();
	private $url = false, $_class = array(), $name = NULL;
	
	public function setUrl($url) { $this -> url = $url; }
	public function setClass($class) { $this -> _class[] = $class; }
	
	public function addLang($lang, $name) {
		$this -> langs[] = array($lang, $name);
	}
	
	public function addForm($Form) {
		$this -> appended[] = $Form -> display(false);	
	}
	
	public function append($str) {
		$this -> appended[] = $str;	
	}
	
	public function setName($name) { $this -> name = $name; }
	public function addField($name = NULL, $lang = NULL, $multilang = false) {
		
		if(isset($this -> fields[$name][$lang]))
			return false;
			
		$FormField = new FormField();
		$this -> fields[$name][$lang] = $FormField;
		
		if($multilang == false) {
			foreach($this -> langs as $rlang)
				if($rlang[0] != $lang)
					$this -> fields[$name][$rlang[0]] = false;
		}
		
		return $FormField;
	}
	
	public function display($includeForm = true) {

		if(($this -> url === false && $includeForm == true))
			return;
			
		$form = ($includeForm ? '<form method="post" action="' . $this -> url . '" name="' . $this -> name . '">' : NULL) . '
		<table cellpadding="0" cellspacing="0" class="' . @implode(' ', $this -> _class) . ' Cols' . count($this -> langs) . '">
		';
		
		$head = '<tr>';
		$showHeader = false;
		$i = 0;
		
			
		foreach($this -> langs as $lang) {
			if($i > 0) $head .= '<th class="ColSpacer"></th>';
			
			if($lang[1] != '')
				$showHeader = true;
				
			$i++;
			$head .= '
				<th colspan="2"><h2>' . $lang[1] . '</h2></th>';
		
		}
		$head .= '</tr>';

		if($showHeader)
			$form .= $head;
			
		//print_r($this -> fields);
		foreach($this -> fields as $fieldName => $fields) {
			$i = 0;
			$form .= '<tr class="' . $fieldName . '">';
			
			$nbLangs = count($this -> langs);
			foreach($this -> langs as $key => $lang) {	
				$cSpan = 1;
			//	echo ($key + 1) . " "  . $nbLangs;
				for($j = $key + 1; $j < $nbLangs; $j++)  {
					$l = $this -> langs[$j][0];
					//echo $l;
					if(!$fields[$l]) {
						//echo $fieldName;
						$cSpan += 3;
					}
				}

			
				if($i > 0 && $cSpan == 1)
					$form .= '<td class="ColSpacer"></td>';
				
					$i++;
					if($fields[$lang[0]]) {
						$Field = $fields[$lang[0]];
						$form .= $Field -> display($cSpan);	
					}
			}
			$form .= '</tr>';
		}
		
		
		$form .= '
		</table>
		';

		foreach($this -> appended as $append) $form .= $append;
		
		$form .= '		
		' . ($includeForm == true ? '
		 <input type="submit" value="Valider" name="formEntry" />
		 </form>' : NULL);
		
		return $form;
	}
}



class FormField {
	
	private $title = NULL, $helper = NULL, $error = NULL, $field;
	
	public function setTitle($strTitle) { $this -> title = $strTitle; }
	public function setHelper($strHelper) { $this -> helper = $strHelper; }
	public function setError($strError) { $this -> error = $strError; }
	public function setField($Field) { $this -> field = $Field; }
	
	public function display($cSpan) {

		return '
		<td class="Title' . ($this -> error != NULL ? ' Error' : '') . '">
			' . $this -> title . '
		</td>
		<td class="Field" colspan="' . $cSpan . '">'
		
			. ($this -> helper != NULL ? '
			 <div class="Helper">
				' . $this -> helper . '
			 </div>' : NULL) . 
							
			($this -> error != NULL ? '
			 <div class="Error">
				' . $this -> error . '
			 </div>' : NULL) . 
			
			$this -> field
			. '
		</td>
		';
	}
}

?>