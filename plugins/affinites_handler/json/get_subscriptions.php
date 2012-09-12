<?php

$_baseUrl = '../../../';
require($_baseUrl . '/libs/includes/ajax.utils.inc.php');


if(empty($_GET['dateid'])) {
	header("HTTP/1.1 403 Forbidden");
	return false;	
}

$toJson	= array();

$Plugin = PluginController::getPlugin('affinites_handler');
$params = $Plugin -> getParams('affinites_handler');
$Module = Module::buildFromId($params['module_users']);

$Fields = $Module -> getFields();

$getData = new GetData();

$getData -> setOrder('firstname', true);
$getData -> setModule($Module);
$getData -> setWhere('actif', '1', '=');
$getData -> setWhere('idx_date', (int) $_GET['dateid'], '=');

$getData -> get();	
$entries = $this -> getData -> filter();

foreach($entries as $entry) {

	$Entry = new Entry($Module, $entry['id'], $entry);
	$state = $Plugin -> getStrState($Entry -> get('state', ''));
	
	$jsonPart = array();
	$jsonPart['isFolder'] = false;
	$jsonPart['title'] = '(' . $state . ')' . $Entry -> get('firstname', '') . ' ' . $Entry -> get('lastname', '');
	$jsonPart['key'] = 'subscription_' . $Entry -> cfg['id'];
	$jsonPart['isLazy'] = false;
	
	$toJson[] = $jsonPart;
}

echo json_encode($toJson);

?>