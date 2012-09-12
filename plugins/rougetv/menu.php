<?php

$params = PluginController::getParams($id);

$Page = Instance::getInstance('Page');
$Menu = $Page -> addMenu('Plugin Rouge TV', $this -> url(array('mode' => NULL, 'plugin' => 'rougetv'), true));

?>