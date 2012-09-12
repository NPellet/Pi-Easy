<?php

class Plugin extends PluginController {
	
	public $id, $params;
	
	public function __construct($id) {
		$this -> id = $id;
		$this -> params = $this -> getParams($id);	
	}
	
	public function run($id) {
		global $_baseUrl;
		
		Instance::getInstance('Page') -> addCss($_baseUrl . '/plugins/xmllang/style.css');
		Instance::getInstance('Page') -> addNavigation('Modification du fichier langue');
		$fileUrl = $_baseUrl . $this -> params['url_file']['value'];
		if(!file_exists($fileUrl))
			return '<div class="Error Message">Source du fichier introuvable</div>';
		
		$Xml = new SimpleXMLElement($fileUrl, 0, true);
		$lang = $this -> params['lang']['value'];
		
		
		if(!empty($_POST['changeXmlFile'])) {
			$rebuilt = $this -> rebuildXml($Xml, $_POST);
			$fh = fopen($fileUrl, 'w') or die("can't open file");
			
			if(fwrite($fh, $rebuilt -> asXml()))
				$strHtml = 'Succès d\'écriture';
			else
				$strHtml .= 'Erreur';
			fclose($fh);
			@chmod($fileUrl, 0777);
		} else {
			$tLang = $this -> parseXml($Xml);
			$strHtml = $this -> buildForm($tLang);
		}
		return $strHtml;
	}

	
	function getLang($simpleXmlNode, $langDefault) {
		if($val = (string) $simpleXmlNode -> $langDefault)
			return $val;
		$children = $simpleXmlNode -> children();
		return (string) $children[0]; 
	}
	
	function parseXml($Xml) {
		$toReturn = array();
		$toReturn['children'] = $this -> parseLevel($Xml);
		return $toReturn;
	}
	
	function parseLevel($Xml) {
		
		$toReturn = array();
		$nodeName = $Xml -> getName();
		$nodeChildren = $Xml -> children();
		$toReturn['name'] = $nodeName;
		if($Xml -> label) {
			$nodeLabel = $this -> getLang($Xml -> label, $lang);
			$toReturn['label'] = $nodeLabel;
		}
		if($Xml -> value) {
			$nodeValue = $this -> getLang($Xml -> value, $lang);
			$toReturn['value'] = $nodeValue;
		} else {
			$childNodes = array();
			foreach($nodeChildren as $nodeChild) {
				if($nodeChild -> getName() == 'label' || $nodeChild -> getName() == 'value')
					continue;
				$childNodes[] = $this -> parseLevel($nodeChild);
			}
			$toReturn['children'] = $childNodes;
		}
		return $toReturn;
	}
	
	function buildForm($xmlArray) {
		$strHtml = NULL;
		$strHtml .= '<form method="post">';
	
		$strHtml .= $this -> buildFormRecursive($xmlArray['children'], NULL);
		$strHtml .= '<input type="submit" name="changeXmlFile" value="Enregistrer" /></form>';
		return $strHtml;
	}
	
	function buildFormRecursive($xmlRec, $path) {
		
		$strHtml = NULL;
		$strHtml .= '<div class="XMLLangElement XMLLangLevel">';
		foreach($xmlRec['children'] as $child) {
			$name = $child['name'];
			$thepath = $path . (strlen($path) == 0 ? '' : ',') . $name;
			if(!empty($child['label']) && empty($child['value']))
				$strHtml .= '<div class="XMLLangElement XMLLangSectionTitle">' . $child['label'] . '</div>';
			elseif(!empty($child['value']))
				$strHtml .= '<div class="XXMLLangElement XMLLangField"><label>' . $child['label'] . '</label><div><textarea name="' . $thepath . '">' . $child['value'] . '</textarea></div><div class="Spacer"></div></div>';
			
			if(!empty($child['children'])) {
			//	foreach($child['children'] as $child)
					$strHtml .= $this -> buildFormRecursive($child, $thepath);
			}
		}
		$strHtml .= '</div>';
		return $strHtml;
	}
	
	function rebuildXml($Xml, $post) {
		$lang = $this -> params['lang']['value'];
		foreach($post as $label => $value) {
			$label = explode(',', $label);
			
			$xmlPath = NULL;
			foreach($label as $labelelement) 
				$xmlPath .= ' -> ' . $labelelement;
				
				
			eval('$XmlChild = $Xml' . $xmlPath . ';');
			if(!empty($XmlChild))
				$XmlChild -> value -> $lang = $value;
		}
		
		return $Xml;
	}
	
	


}

?>