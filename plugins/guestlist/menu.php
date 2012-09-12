<?php

$params = PluginController::getParams($id);

$Page = Instance::getInstance('Page');
$Menu = $Page -> addMenu('Relever la gueslist', $this -> url(array('mode' => NULL, 'plugin' => $id), true));

?>