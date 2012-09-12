<?php

$params = PluginController::getParams($id);
$Page = Instance::getInstance('Page');
$Menu = $Page -> addMenu('Détails des inscriptions', $this -> url(array('plugin' => $id, 'mode' => 'build'), true));

?>