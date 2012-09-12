<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
exit(0);

$User = Instance::getInstance('User');
$rubrique = $_POST['rubrique'];
$Module = Module::buildFromId($_POST['module']);

if(!empty($_POST['id'])) {
	$idRubrique = $_POST['id'];
	$Rubrique = Rubrique::buildFromId($idRubrique);
} else {
	$Rubrique = new Rubrique();	
	$Rubrique -> setModule($Module);
}

foreach($Module -> getLangs() as $lang => $trash) {
	
	$theRubrique = $lang == '' ? $rubrique[0] : $rubrique[$lang];
	$Rubrique -> setLabel($lang, $theRubrique);
}

$Rubrique -> save();

?>