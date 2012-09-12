<?php

$_baseUrl = '../../../';
require($_baseUrl . '/libs/includes/ajax.utils.inc.php');

$Plugin = PluginController::getPlugin('affinites_handler');
$params = $Plugin -> getParams('affinites_handler');


if(empty($_GET['subscription_id'])) {
	header("HTTP/1.1 403 Forbidden");
	return false;	
}

$strHtml = NULL;
	
$Module = Module::buildFromId($params['module_subscriptions']['value']);
$Fields = $Module -> getFields();

$getData = new GetData();

//$getData -> setOrder('date', true);
$getData -> setModule($Module);
$getData -> setWhere('actif', '1', '=');
$getData -> setWhere('id', (int) $_GET['subscription_id'], '=');
$getData -> get();
$entries = $getData -> filter();

$ModuleEvent = Module::buildFromId($params['module_events']['value']);
$getDataEvent = new GetData();
$getDataEvent -> setModule($ModuleEvent);
$getDataEvent -> setWhere('actif', '1', '=');

$ModuleDates = Module::buildFromId($params['module_dates']['value']);
$getDataDate = new GetData();
$getDataDate -> setModule($ModuleDates);
$getDataDate -> setWhere('actif', '1', '=');


$ModuleUser = Module::buildFromId($params['module_users']['value']);
$getDataUser = new GetData();
$getDataUser -> setModule($ModuleUser);
$getDataUser -> setWhere('actif', '1', '=');

global $Navigation;

foreach($entries as $subscription) {

	$getDataEvent -> setWhere('id', $subscription['idx_event'], ' = ');
	$getDataEvent -> get();
	$events = $getDataEvent -> filter();
	$event = current($events);

	$getDataDate -> setWhere('id', $subscription['idx_date'], ' = ');
	$getDataDate -> get();
	$dates = $getDataDate -> filter();
	$date = current($dates);

	$getDataUser -> setWhere('id', $subscription['idx_user'], ' = ');
	$getDataUser -> get();
	$users = $getDataUser -> filter();
	$user = current($users);
	
	
	$strDate = $date['date'] != "" ? date('d/m/Y', strtotime($date['date'])) : 'Pas encore défini';
	$strStatus = $Plugin -> getStrState($date['state']);
	
	$strHtml .= '
		<h2>Informations de l\'inscription</h2>
		<div><h3>Evénement</h3><p>' . $event['title'] . '</p></div>
		<div><h3>Date</h3><p>' . $strDate . '</p></div>
		<div><h3>Commentaire</h3><p>' . $subscription['comment'] . '</p></div>
		<div><h3>Membre</h3><p><a href="./plugins/affinites_handler/export.php?member=' . $user['id'] . '">' . $user['firstname'] . ' ' . $user['lastname'] . '</a></p></div>
		<div><h3>Status</h3><p>' . $strStatus . '</p></div>
		<div><h3>Editer l\'inscription</h3><p><a href="mode-entry/module-6/action-edit/entry-' . $subscription['id'] . '.html">Editer</a></p></div>
	';
}

echo $strHtml;

?>