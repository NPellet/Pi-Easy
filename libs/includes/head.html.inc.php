<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Pi-Easy - Espace d'administration <?php echo (defined('NOM_SITE') ? NOM_SITE : ''); ?></title>
<link rel="icon" type="image/png" href="./favicon.png" />
<?php
	echo '<base href="http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['SCRIPT_NAME'] . '" />';
?>