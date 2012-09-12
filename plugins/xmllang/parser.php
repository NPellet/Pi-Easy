<?php

$Xml = new SimpleXMLElement('_sample.xml', 0, true);
$lang = 'fr';
parseXml($Xml);

function getLang($simpleXmlNode, $langDefault) {
	if($val = (string) $simpleXmlNode -> $langDefault)
		return $val;
	$children = $simpleXmlNode -> getChildren();
	return (string) $children[0]; 
}

function parseXml($Xml) {
	$toReturn = array();
	$toReturn['children'] = parseLevel($Xml -> lang[0]);
	return $toReturn;
}

function parseLevel($Xml) {
	$toReturn = array();
	$nodeName = $Xml -> getName();
	$nodeChildren = $Xml -> children();
	$toReturn['name'] = $nodeName;
	if($Xml -> label) {
		$nodeLabel = getLang($Xml -> label, $lang);
		$toReturn['label'] = $nodeLabel;
	}
	if($Xml -> value) {
		$nodeValue = getLang($Xml -> value, $lang);
		$toReturn['value'] = $nodeValue;
	} else {
		$childNodes = array();
		foreach($nodeChildren as $nodeChild) {
			if($nodeChild -> getName() == 'label' || $nodeChild -> getLabel() == 'value')
				continue;
			$childNodes[] = parseLevel($nodeChild);
		}
		$toReturn['children'] = $childNodes;
	}
}

function buildForm($xmlArray) {
	$strHtml = NULL;
	$strHtml .= '<form method="post">';
	$strHtml .= buildFormRecursive($xmlArray['children'], NULL);
	$strHtml .= '<input type="post" value="Enregistrer" /></form>';
	return $strHtml;
}

function buildFormRecursive($xmlRec, $path) {
	
	$strHtml = NULL;
	$strHtml .= '<div class="XMLLangElement XMLLangLevel">';
	foreach($xmlArray['children'] as $child) {
		$name = $child['name'];
		$path .= (strlen($name) == 0 ? '' : '.') . $name;
		if(!empty($child['label']) && empty($child['value']))
			$strHtml .= '<div class="XMLLangElement XMLLangSectionTitle">' . $child['label'] . '</div>';
		elseif(!empty($child['value']))
			$strHtml .= '<label>' . $child['label'] . '</label><div><textarea name="' . $path . '">' . $child['value'] . '</textarea></div><div class="Spacer"></div>';
		
		if(!empty($child['children'])) {
			foreach($child['children'] as $child)
				$strHtml .= buildFormRecursive($child, $path);
		}
	}
	$strHtml .= '</div>';
	return $strHtml;
}

function rebuildXml($Xml, $post) {
	
	foreach($post as $label => $value) {
		$label = explode('.', $label);
		$XmlChild = $Xml;
		foreach($label as $labelelement) {
			$XmlChild = $XmlChild -> $labelelement;
		}
		
		$XmlChild -> $lang = $value;
	}
	
	return $Xml;
}

?>