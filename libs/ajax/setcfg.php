<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
exit(0);

$Navigation = new Navigation();

if(isset($_POST['key']) && isset($_POST['value'])) {
	$Cfg = Instance::getInstance('Config');
	$Cfg -> config($_POST['key'], $_POST['value']);
	$Cfg -> saveConfig();
} 

?>