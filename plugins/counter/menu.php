<?php

$Page = Instance::getInstance('Page');
$Menu = $Page -> addMenu('Compteur de clics', $this -> url(array('mode' => 'counter', 'plugin' => $id), true));

?>