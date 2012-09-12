<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

$Navigation = new Navigation();

$rub = $_GET['r'];
$mod = $_GET['m'];
$sort = $_GET['s'];
$sortType = $_GET['st'];
$page = $_GET['p'];
$sortMode = $_GET['sm'];

$Rubrique = Rubrique::buildFromId($rub);
$Module = Module::buildFromId($mod);

$List = new ListData($Module);
$List -> setPage($page);
$List -> getData -> filter['rubrique'] = $rub;
if($sort != NULL && !$Module -> isSortable())
	$List -> getData -> setOrder($sort, $sortType == 'asc' ? true : false);

if($sortMode == 1)
	$List -> setNbEntries(0);

$List -> get();
$data = $List -> displayInnerTable();
echo $data;

?>