<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

if(!empty($_REQUEST['type'])) {
	
	$remove = $_REQUEST['type'];
	$toRemove = explode(',', $_REQUEST['toremove']);
	$extra = explode(';', $_REQUEST['extraData']);
	
	foreach($toRemove as $removeitem) 
		switch($remove) {
			
			case 'module':
				if(Security::userAdmin()) {
					$Module = Module::buildFromId($removeitem);
					$Module -> remove();
				}
			break;
			
			case 'field':
				if(Security::userAdmin()) {
					$Field = Field::buildFromId($removeitem);
					$Field -> remove();
				}
			break;
			
	
			case 'entry':
				if(Security::userAccess($extra[0], 'remove')) {
					$Entry = new Entry(Module::buildFromId($extra[0]), $removeitem);
					$Entry -> remove();
				}
			break;
	
			case 'categorie':
				if(Security::userAdmin()) {
					$Categorie = Categorie::buildFromId($removeitem);
					$Categorie -> remove();
				}
			break;
	
			case 'rubrique':
				if(Security::userAccess($removeitem, 'rubriques')) {
					$Rubrique = Rubrique::buildFromId($removeitem);
					$Rubrique -> remove();
				}
			break;
			
			case 'user':
				if(Security::userAdmin() || Security::userModerator()) {
					$User = User::buildFromId($removeitem);
					$User -> remove();
				}
			break;	
			
			case 'groupfields':
				if(Security::userAdmin() || Security::userModerator()) {
					$Group = GroupFields::buildFromId($removeitem);
					$Group -> remove();
				}
	
			break;
		}
}

?>