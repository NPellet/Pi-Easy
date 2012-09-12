<?php

define('IN_PIEASY', true);
$_baseUrl = './';
include('./includes.inc.php');

$Navigation = new Navigation();
if(Security::userAdmin())
	ini_set('display_errors', 1);
$Navigation -> run();


?>