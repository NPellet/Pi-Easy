<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Page extends Security {
	
	private $tMenus, $tButtons;
	private $title, $content;
	private $ajaxZone = false;
	private $js, $css;
	public $simple = false;
	private $navigation = array(), $toolbar = array();

	public function addMenu($title, $link = NULL, $position = 'left', $order = 'last') {
		$Menu = new Menu($title, $link);
		$order = ($order == 'last' || $order == 'first' || is_int($order)) ? $order : 'last';
		$this -> tMenus[] = array('pos' => $position, 'order' => $order, 'menu' => $Menu);
		return $Menu;
	}

	public function showNav() {
		
		$Nav = $this -> getInstance('Navigation');
		$strHtml = '
		<script language="javascript" type="text/javascript">
		<!--
		(function($) {
			$.nav = ' . json_encode($Nav -> nav) . ';
			$.baseUrl = "' . (defined('BASE_URL') ? BASE_URL : '') . '";
			$.folderMedia = "' . @DATA_ROOT_REL . FOLDER_UPLOAD_MEDIAS . '";
			$.folderFTP = "' . @FTP_ROOT_REL . '";
	    }) (jQuery);
		-->
		</script>';
				
		return $strHtml;
	}
	
	public function showCfg() {
	
		$Cfg = $this -> getInstance('Config');
		if(!$Cfg || !is_array($Cfg -> getCfg()))
			return false;
			
		$tCfg = array();
		foreach($Cfg -> getCfg() as $key => $details)
			$tCfg[$key] = $details['value'];
	
		
		$strHtml = '
		<script language="javascript" type="text/javascript">
		<!--
		(function($) {
			$.cfg = ' . json_encode($tCfg) . ';
	    }) (jQuery);
		-->
		</script>';
				
		return $strHtml;		
	}

	public function addButton($Button) {
		$this -> tButtons[] = $Button;
	}
	
	public function getButtons() { return $this -> tButtons; }
	
	public function setTitle($strTitle) {
		$this -> title = $title;
	}
	
	public function addNavigation($nav) {
		$this -> navigation[] = $nav;
	}
	
	public function addToolbar($class, $url, $label) {
		$this -> toolbar[] = array($class, $url, $label);	
	}
	
	public function showNavigation() {
		$i = count($this -> navigation);
		$strPage = NULL;

		foreach($this -> navigation as $nav) {
			$i--;
			$strPage .= ' > <span class="Niv' . $i . '">' . $nav . '</span>';
		}
		
		return '<div id="Navigation">' . substr($strPage, 3) . '</div>';
	}

	public function addJs($fPath) {
		if(is_array($fPath))
			foreach($fPath as $f)
				$this -> js[$f] = true;
		else
			$this -> js[$fPath] = true;
	}
	
	public function addCss($fPath) {
		if(is_array($fPath))
			foreach($fPath as $f)
				$this -> css[$f] = true;
		else
			$this -> css[$fPath] = true;
	}
	
	public function setContent($strContent) {
		$this -> content = $strContent;	
	}

	public function setFile($strPage) {
		ob_start();
		include($strPage);	
		$this -> content = ob_get_contents();
		ob_end_clean();
	}
	
	public function display() {
		global $_baseUrl;
		$strPage = NULL;
		
		$page = file_get_contents($_baseUrl . FOLDER_LIBS_INCLUDES . 'page.html.inc.php');
		
		ob_start();
		require($_baseUrl . FOLDER_LIBS_INCLUDES . 'head.html.inc.php');
		$start = ob_get_contents();
		ob_end_clean();
		
		//$fb = require($_baseUrl . FOLDER_LIBS_INCLUDES . 'facebook.inc.php');
		
		foreach($this -> js as $js => $trash)
			$start .= '
			<script language="javascript" type="text/javascript" src="' . $js . '"></script>';
			
		foreach($this -> css as $css => $trash)
			$start .= '
			<link rel="stylesheet" type="text/css" href="' . $css . '" />';
		
		$start .= '
		</head>
		';
		
	
	//	$strTable = array();

		$strMenu = NULL;
		if(count($this -> tMenus) > 0)
			foreach($this -> tMenus as $Menu)
				$strMenu .= $Menu['menu'] -> display();
	//		$strTable .= $strMenu != NULL ? '<td class="Menu">' . $strMenu . '</td>' : @$strTable[$position];

		$strPage .= $this -> showNavigation();
		
		
		$strButtons = NULL;
		if(count($this -> tButtons) > 0) {
			foreach($this -> tButtons as $Button)
				$strButtons .= $Button -> display(false);	
		}
		
		if($strButtons != NULL)
			$strPage .= '<ul class="Buttons Actions">' . $strButtons . '</ul><div class="Spacer"></div>';
		
		$strPage .= $this -> content;
		$menu = $strMenu;

		$strPage .= $this -> showNav() . $this -> showCfg();
		
		ob_start();
		require($_baseUrl . FOLDER_LIBS_INCLUDES . 'tail.html.inc.php');
		$end = ob_get_contents();
		ob_end_clean();
		
		if(Instance::getInstance('Config') != false)
			$logo = 'getFile.php?file=' . Instance::getInstance('Config') -> get('logo_site');
		else
			$logo = NULL;
			
		$header = '
			<div>
				' . (Instance::getInstance('Config') != NULL && Instance::getInstance('Config') -> get('logo_site') != NULL ? '<img src="' . $logo . '" />' : NULL) . '
			</div>
			<span>' . 	
				(defined('NOM_SITE') ? NOM_SITE . ' | ' . NOM_SITE_APPEND : 'Installation du Pi-Easy') . 
			'</span>';
		
		$strToolbar = NULL;
		foreach($this -> toolbar as $toolbar)
			$strToolbar .= '<li class="' . $toolbar[0] . '"><a href="' . $toolbar[1] . '">' . $toolbar[2] . '</a></li>';
	
		$user = Instance::getInstance('currentUser');
		if($user != NULL)
			$userName = $user -> getUsername();
		else
			$userName = NULL;
		
		echo $start . 
		(!$this -> simple ? sprintf($page, $header, $menu,  $strToolbar, $userName, $this -> url(array('logout' => 1), true), $strPage) : $strPage) . 
		$end;
	}	
}

?>