<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Message {
	
	public static $tMessages = array();
	
	public function log($id = NULL, $active = false) {
		self::$tMessages[$id] = $active;
	}

	public function logTxt($key, $text, $mode) {
		self::$tMessages[$key] = array($text, $mode);
	}

	public function export() {
		$toExport = array();
		foreach(self::$tMessages as $id => $active) 
			if($active == true && !is_numeric($id))
				$toExport[] = $id;

		return implode(',', $toExport);	
	}
	
	public function display() {
	
		$strHtml = NULL;
		
		foreach(self::$tMessages as $id => $is) {

			if(is_numeric($id)) {
				if($is[1] == 0) $class = 'Error';
				else $class = 'Ok';
				$strHtml .= '<div class="Message ' . $class . '">' . $is[0] . '</div>';
			} else
				$strHtml .= $this -> displayMessage($id);
		}
		self::$tMessages = array();
		
		return $strHtml;
	}
	
	public function nbMessages() {
		return count(self::$tMessages);	
	}
	
	private function displayMessage($id) {
		global $_tMessages;

		$id = explode(':', $id);
		$message = $_tMessages;
		
		$j = 0;
		foreach($id as $i) {
			$j++;
			if($j == 2)
				$mode = $j;
				
			if(!isset($message[$i]))
				continue;
				
			$message =  $message[$i];
		}
	
		if(is_array($id) && count($id) > 1) {
			if($id[1] == 0) $class = 'Error';
			else $class = 'Ok';
	
			return '<div class="Message ' . $class . '">' . $message . '</div>';
		}
	}
}

?>