<?php

$_baseUrl = '../../../';
require($_baseUrl . '/libs/includes/ajax.utils.inc.php');

$toJson	= array();

$Plugin = PluginController::getPlugin('affinites_handler');
$params = $Plugin -> getParams('affinites_handler');
$Module = Module::buildFromId($params['module_events']);

$Fields = $Module -> getFields();

$getData = new GetData();
$getData -> setOrder('title', true);
$getData -> setModule($Module);
//$getData -> setWhere('actif', '1', '=');
$getData -> get();
	
$entries = $getData -> filter();

foreach($entries as $entry) {

	$Entry = new Entry($Module, $entry['id'], $entry);
	
	$jsonPart = array();
	$jsonPart['isFolder'] = true;
	$jsonPart['title'] = $Entry -> get('title', '');
	$jsonPart['title'] = $jsonPart['title']['treat'];
	$jsonPart['key'] = 'event_' . $Entry -> cfg['id'];
	$jsonPart['isLazy'] = true;
	
	$toJson[] = $jsonPart;
}

echo json_encode($toJson);

?>