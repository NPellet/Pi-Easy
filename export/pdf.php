<?php

define('IN_PIEASY', true);
$_baseUrl = '../';
include('../includes.inc.php');


$Navigation = new Navigation();
if(Security::userAdmin())
	ini_set('display_errors', 1);

$Navigation -> login();
$User = $Navigation -> getInstance('currentUser');

if(!$User) 
	return false;

$Module = Module::buildFromId($_GET['module']);

$Get = new GetData();
$Get -> setOrder(FIELD_ORDER, 'asc');
$Get -> setModule($Module);
$Get -> setWhere('actif', '1', '=');
$Get -> setWhere('id', $_GET['entry'], '=');
$val = $Get -> get();

$Entry = new Entry($Module, $_GET['entry'], $val[$_GET['entry']]);
$sections = array(1 => '', 2 => '', 3 => '');
foreach($Entry -> cfg['Fields'] as $Field) {
	$Field -> setFirstLang();
	$value = $Entry -> get($Field -> getName(), $Field -> getLang());
	$treated = $value['treat'];
	
	$section = $Field -> getPriority();
	
	switch($Field -> getType()) {
		case 'picture':
		case 'file':
			$section = 3;	
		break;
		
		default:
			if($section == 3)
				$section = 2;	
		break; 	
	}
	
	
	switch($section) {
		
		case '1';
			$sections[1] .= ' ' . $Field -> display($treated);
		break;
		
		case '2':
			$sections[2] .= '<div><label>' . $Field -> getLabel() . '</label>' . $Field -> display($treated) . '<div class="Spacer"></div></div>';
		break;
		
		case '3':
			$sections[3] .= '<div>' . $Field -> display($treated) . '</div>'; 	
		break;		
	}	
}

echo '
<link href="' . $_baseUrl . FOLDER_DESIGN . 'exporthtml.css" rel="stylesheet" />
<div id="Page">
	<div class="Left">
		' . $sections[3] . '
	</div>
	<div class="Right">
		<h1>' . $sections[1] . '</h1>
		' . $sections[2] . '	
	</div>
</div>';

?>