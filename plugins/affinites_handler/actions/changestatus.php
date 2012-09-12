<?php

$_baseUrl = '../../../';
require($_baseUrl . '/libs/includes/ajax.utils.inc.php');

$Plugin = PluginController::getPlugin('affinites_handler');

$subscription = $_GET['subscription'];
$status = $_GET['status'];

$strSql = 'UPDATE `' . Sql::buildTable('subscriptions') . '` SET `status` = "' . Sql::secure($status) . '" WHERE `id` = ' . (int) $subscription . ' LIMIT 1';
Sql::query($strSql);

?>