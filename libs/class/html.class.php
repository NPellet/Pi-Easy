<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Html {
	
	public static function buildList($els, $sel = NULL, $addBlank = false) {
		
		if(!is_array($sel))
			$sel = array($sel);
		$strList = NULL;
		

		foreach($els as $group => $elsInGroup) {
			
			if(!is_array($elsInGroup)) {
				if($elsInGroup == NULL)
					continue;
					
				$strList .= '
				<option value="' . $group . '"' . (in_array($group, $sel) ? ' selected="selected"' : NULL) . '>'
					. $elsInGroup . 
				'</option>';			
			} else {	
				$strList .= '<optgroup label="' . $group . '">';
				foreach($elsInGroup as $value => $label) {
					if($label == NULL)
						continue;
					$strList .= '
					<option value="' . $value . '"' . (in_array($value, $sel) ? ' selected="selected"' : NULL) . '>'
						. $label . 
					'</option>';
				}
				$strList .= '</optgroup>';
			}
		}
		
		if($addBlank)
			$strList = '<option value=""></option>' . $strList;
			
		return $strList;
	}
}

?>