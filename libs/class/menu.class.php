<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Menu {
	
	private $title, $tElements, $link;

	public function __construct($title, $link = NULL) {
		$this -> title = strip_tags($title);
		$this -> link = $link;
	}

	public function addEntry($config) {
		$this -> tElements[] = $config;	
	}

	public function display() {
		
		$strHtml = '
		<ul class="Menu">
			<li class="Title">' . 
				($this -> link != NULL ? '<a href="' . $this -> link . '">' . $this -> title . '</a>' : $this -> title) . '
			</li>';
		
		if(count($this -> tElements) > 0)
			foreach($this -> tElements as $element) {
				$strHtml .= '<li class="Element"><a href="' . $element['url'] . '">' . $element['label'] . '</a></li>';
				if(!empty($element['sub']) && count($element['sub']) > 0) {
					$strHtml .= '<ol class="Submenu">';
					
					foreach($element['sub'] as $sub)
						$strHtml .= '<li class="SubElement"><a href="' . $sub['url'] . '">' . $sub['label'] . '</a>';	
					$strHtml .= '</ol>';
				}
			//	$strHtml .= '</li>';	
			}
		
		$strHtml .= '
		</ul>';
		
		return $strHtml;
	}
}

?>