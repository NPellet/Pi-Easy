<?php

$Module = Module::buildFromId($idModule);
$tFields = $Module -> getFields();

$gOrder = !empty($_GET['order']) ? Sql::secure($_GET['order']) : FIELD_DATE_ADDED;
$gOrderMode = !empty($_GET
$gLang = !empty($_GET['lang']) ? Sql::secure($_GET['lang']) : '';
$gLimit = !empty($_GET['limit']) ? $_GET['limit'] : 0;


$get = new GetData();
$get -> setModule($Module);
$get -> setOrder($gOrder);

if($gLimit > 0)
	$get -> setLimit(0, 3);
$get -> get();

if($tEntries = $get -> getData()) {
	
	$xml = new SimpleXMLElement();
	$root = $xml -> addChild($Module -> getName() . 's');
	
	foreach($tEntries as $entry) {
		
		$Entry = new Entry($Module, $entry['id'], $entry);	
		$child = $xml -> addChild($Module -> getName());
		foreach($tFields as $Field) {
			if($Field -> isRss()) {
				$lang = $Field -> getLangs();
				$lang = array_keys($lang);
				$lang = $lang[0];
				
				$child -> addChild($Field -> getName(), $Entry -> get($Field -> getName(), $lang))
			}
		}
	}
	
	echo $xml -> asXml();
}
?>