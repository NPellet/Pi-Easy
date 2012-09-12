<?php

$_baseUrl = '../../../';
require_once($_baseUrl . '/libs/includes/ajax.utils.inc.php');

if(!isset($_GET['eventid'])) {
	header("HTTP/1.1 403 Forbidden");
	return false;	
}

$toJson	= array();

$Plugin = PluginController::getPlugin('affinites_handler');
$params = $Plugin -> getParams('affinites_handler');
$Module = Module::buildFromId($params['module_dates']['value']);
$Fields = $Module -> getFields();

$getData = new GetData();

$getData -> setModule($Module);
$getData -> setOrder('date', true);
$getData -> setWhere('actif', '1', '=');
$getData -> setWhere('event', $_GET['eventid'], '=');

$getData -> get();	
$entries = $getData -> filter();

foreach($entries as $entry) {

	$Entry = new Entry($Module, $entry['id'], $entry);
	
	$strDate = $Entry -> get('date', '');
	$strDate = $strDate['treat'];
	
	$jsonPart = array();
	$jsonPart['isFolder'] = true;
	$jsonPart['title'] = $strDate;
	$jsonPart['key'] = 'date_' . $Entry -> cfg['id'];
	$jsonPart['isLazy'] = true;
	
	$toJson[] = $jsonPart;
}


ob_start();
$_GET['dateid'] = 0;
include('get_subscriptions.json.php');
$strJson = ob_get_contents();
ob_end_clean();

if($jsonUsers = json_decode($strJson, true)) {
	$toJson = array_merge_recursive($toJson, $jsonUsers);
}

echo json_encode($toJson);

?>