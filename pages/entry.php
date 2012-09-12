<?php

if(intval($this -> module) == 0)
	$this -> log();
else {
	$Module = Module::buildFromId($this -> module);

if($this -> sent == true) {
	
	if($this -> action == 'add' || ($this -> action == 'edit' && $this -> entry != NULL)) {

		$Entry = $this -> action == 'add' ? new Entry($Module) : Entry::buildFromIdAndModule($Module, $this -> entry);
		$Fields = $Module -> getFields();

		foreach($Fields as $Field)
			$Entry -> setFromData($Field, $_POST);
		
		$Entry -> treatData();
		$Entry -> checkData();
		$Entry -> validateData();

		if(!$Entry -> hasErrors()) {

			if($Entry -> save())		
				$this -> redirect(array('mode' => 'show', 'entry' => NULL));
			else
				$transEntry = $Entry;
		}
	}
}

$strTitle = $strBody = $strButtons = NULL;

if(($this -> action == 'edit' && $this -> entry != NULL) || $this -> action == 'add') {

	if($this -> action == 'edit') {
		$Entry = empty($transEntry) ? Entry::buildFromIdAndModule($Module, $this -> entry) : $transEntry;
		$strTitle = 'Editer l\'entrée ' . $Module -> getLabel();
	} else {
		$strTitle = 'Ajouter une nouvelle entrée';
		$Entry = empty($transEntry) ? new Entry($Module) : $transEntry;
	}

	$strBody = $Entry -> displayEdit();
} else {
	
	$strTitle = 'Entrées actuelles';
	$List = new ListData($this -> module);
	$strBody = $List -> displayTable();
	
	$strButtons = '<a href="' . url(array('action' => 'add')) . '">Ajouter une entrée</a>';
}
$strButtons .= '<a class="Button EditRubriques">Editer les rubriques</a>';
return '

<div id="ModuleId" rel="' . $Module -> getId() . '"></div>
<h1>' . $strTitle . '</h1><div id="Content"><p>' . $strButtons . '</p>

<div id="AjaxZone"></div>
<p>' . $strBody . '</p>';

}

?>