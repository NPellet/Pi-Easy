<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Button extends Security {

	private $url, $opts, $auto;
	
	public function __construct($class, $text, $automaticDisplay = true) {
		$this -> opts['class'] = array($class);
		$this -> opts['text'] = $text;
		$this -> auto = $automaticDisplay;
	}
	
	public function setUrl($url) {
		$this -> url = is_array($url) ? $this -> url($url) : $url;
	}
	
	public function setClass($class) {
		$this -> opts['class'][] = $class;
	}
	
	public function setOptions($tOptions) {
		
		foreach($tOptions as $key => $value) {
			$key = strtolower($key);
			if(!in_array($key, array('disabled', 'disablable', 'selected', 'selectable', 'validable', 'text', 'id', 'class', 'rel')))
				continue;
			if($key == 'class')
				$this -> opts[$key][] = $value;
			else
				$this -> opts[$key] = $value;
		}
	}
	
	public function display($force = true) {
		if($force == false && $this -> auto == false) return;
		
		return '
		<li' . 
		(!empty($this -> opts['id']) ? ' id="' . $this -> opts['id'] . '"' : NULL) . 
		(!empty($this -> opts['rel']) ? ' rel="' . $this -> opts['rel'] . '"' : NULL) . 
		' class="Button ' . implode(' ' , $this -> opts['class']) . ' ' . 
		(!empty($this -> opts['disabled']) ? 'Disabled ' : NULL) . 
		(!empty($this -> opts['disablable']) ? 'Disablable ' : NULL) . 
		(!empty($this -> opts['selected']) ? 'Selected ' : NULL) . 
		(!empty($this -> opts['selectable']) ? 'Selectable ' : NULL) . 
		(!empty($this -> opts['validable']) ? 'Validable ' : NULL) . 		
		'">
			<a' . (!empty($this -> url) ? ' href="' . $this -> url . '" ' : NULL) . '>
				' . $this -> opts['text'] . '
			</a>
		</li>';
	}
}
?>