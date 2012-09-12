<?php

$_baseUrl = '../../../';
require_once($_baseUrl . '/libs/includes/ajax.utils.inc.php');
if(!isset($_GET['dateid'])) {
	header("HTTP/1.1 403 Forbidden");
	return false;	
}
ini_set('display_errors', true);

$toJson2	= array();

$Plugin = PluginController::getPlugin('affinites_handler');
$params = $Plugin -> getParams('affinites_handler');

$Module = Module::buildFromId($params['module_subscriptions']['value']);
$Fields = $Module -> getFields();
$getData = new GetData();
$getData -> setModule($Module);
$getData -> setWhere('actif', '1', '=');
$getData -> setWhere('date', $_GET['dateid'], '=');
if(!empty($_GET['eventid']))
	$getData -> setWhere('event', (int) $_GET['eventid'], '=');
$getData -> get();	
$entries = $getData -> filter();


$ModuleUsers = Module::buildFromId($params['module_users']['value']);
$FieldsUsers = $ModuleUsers -> getFields();
$getDataUsers = new GetData();
$getDataUsers -> setModule($ModuleUsers);
$getDataUsers -> setWhere('actif', '1', '=');
$getDataUsers -> get();	
$entriesUsers = $getDataUsers -> filter();


foreach($entries as $entry) {

	$Entry = new Entry($Module, $entry['id'], $entry);
	$status = $Entry -> get('status', '');
	$status = $status['treat'];
	$state = $Plugin -> getStrState($status);
	$userId = $Entry -> get('user', '');	
	foreach($entriesUsers as $user) {
		if($user['id'] == $userId['treat']) {
			$theuser = $user;
			break;
		}
	}
	
	
	$jsonPart = array();
	$jsonPart['isFolder'] = false;
	$jsonPart['title'] = '(' . $state . ') ' . $theuser['firstname'] . ' ' . $theuser['lastname'] . ' ' . $theuser['email_email'];
	$jsonPart['key'] = 'subscription_' . $Entry -> cfg['id'];
	$jsonPart['isLazy'] = false;
	
	$toJson2[] = $jsonPart;
}

echo json_encode($toJson2);

?>