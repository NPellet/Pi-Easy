<?php

$params = PluginController::getParams($id);
$Page = Instance::getInstance('Page');
$Menu = $Page -> addMenu('Fichier langue', $this -> url(array('plugin' => $id, 'mode' => 'build'), true));

?>