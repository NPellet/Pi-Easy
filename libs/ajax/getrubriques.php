<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
exit(0);

$idModule = $_REQUEST['idModule'];

if(!Security::userAccess($idModule, 'rubriques'))
	die();

$Module = Module::buildFromId($idModule);
$tRubriques = Rubrique::getAll($Module);
$toPrint = array();

foreach($tRubriques as $Rubrique) {
	
	$array = array();
	foreach($Module -> getLangs() as $lang => $details) {
		$toPrint['rubriques'][$Rubrique -> getId()][$lang] = $Rubrique -> getLabel($lang);
	}
}

$toPrint['langs'] = $Module -> getLangs();
echo json_encode($toPrint);

?>