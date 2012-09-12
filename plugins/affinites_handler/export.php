<?php

$_baseUrl = '../../';
require($_baseUrl . '/libs/includes/ajax.utils.inc.php');

$Plugin = PluginController::getPlugin('affinites_handler');
$params = $Plugin -> getParams('affinites_handler');

$Plugin -> export($_GET['member']);

?>