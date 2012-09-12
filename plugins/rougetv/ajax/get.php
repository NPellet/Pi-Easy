<?php

$_baseUrl = '../../../';
require($_baseUrl . '/libs/includes/ajax.utils.inc.php');

$Plugin = PluginController::getPlugin('rougetv');

if(!empty($_GET['url'])) {
	$url = $_GET['url'];
	preg_match_all('!vi=([0-9]{1,5})!', $url, $match);
	$id = $match[1][0];
} else
	$id = (string) $_GET['id'];	

$url = $Plugin -> getUrl($id);

if($_GET['dl'] != 'false') {
	$ret = $Plugin -> downloadFile($url);
	echo $ret;
} else {
	echo $url;	
}

?>