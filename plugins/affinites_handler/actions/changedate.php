<?php

$_baseUrl = '../../../';
require($_baseUrl . '/libs/includes/ajax.utils.inc.php');

$Plugin = PluginController::getPlugin('affinites_handler');

$subscription = $_GET['subscription'];
$newdate = $_GET['date'];

$strSql = 'UPDATE `' . Sql::buildTable('subscriptions') . '` SET `idx_date` = ' . (int) $newdate . ' WHERE `id` = ' . (int) $subscription . ' LIMIT 1';
Sql::query($strSql);

?>