<?php

class GroupFields extends Object {
	
	private $id, $label, $order, $idx_module;
	
	public static function buildFromId($id) {
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_GROUPFIELDS) . '` WHERE `id` = ' . intval($id) . ' LIMIT 1';
		if($resSql = Sql::query($strSql))
			if($dataSql = mysql_fetch_assoc($resSql))
				$obj = new GroupFields($dataSql);
			else {
				$obj = new GroupFields(array('id' => '', 'idx_module' => '', 'order' => '', 'label' => ''));
			}
		return $obj;
	}


	public function __construct($dataSql = NULL) {
		
		if(empty($dataSql)) {
			$this -> label = '';
			return true;
		}
		extract($dataSql);
		$this -> id = $id;
		$this -> label = $label;
		$this -> order = $order;
		$this -> idx_module = $idx_module;
	}
	
	public function getId() { return $this -> id; }
	public function setId($id) { $this -> id = intval($id); }
	public function getLabel() { return $this -> label; }
	public function getOrder() { return $this -> order; }
	public function getIdxModule() { return $this -> idxModule; }
	public function setIdxModule($intIdxModule) {
		$this -> idx_module = intval($intIdxModule);
	}
	public function setName($strName) { $this -> name = $strName; }
	
	public function save() {
		
		$sql['label'] = $this -> label;
		$sql['idx_module'] = $this -> idx_module;
	
		if($this -> query(Sql::buildSave(T_CFG_GROUPFIELDS, $this -> id, $sql))) {
			$this -> saveOrder($this -> order, $this -> id);	
			return true;
		}
		//return $this -> log();
	}
	
	public function edit() {

		if(!Security::userAdmin())
			return $this -> log();
			
		if(empty($_POST['formEntry']))
			return $this -> formEdit();
	
		extract($_POST);
		$this -> label = $groupLabel;
		$this -> order = $groupOrder;
		$this -> id = $this -> nav('groupfields');
		$this -> idx_module = $groupIdxModule;
		
		$proceed = true;
		
		if($this -> idx_module == NULL) {
			$this -> log();
			return $this -> formEdit();
		}
		
		if($proceed == false)
			return $this -> formEdit();
		
		if($this -> save()) {
			$this -> log('grpfields:1:1');
			$this -> redirect($this -> url(array('groupfields' => NULL, 'action' => 'show')));
		}
	}
	
	private function formEdit() {
		
		$tGroups = GroupFields::getAll();
		$opts = NULL;
		$selected = false;
		
		foreach($tGroups as $Group) {
			if($this -> order != NULL && $Group -> getOrder() > $this -> order && $selected == false) {
				$strSelected = ' selected="selected"';
				$selected = true;
			} else
				$strSelected = NULL;
				
			$opts .= '
			<option value="' . $Group -> getOrder() . '"' . $strSelected . ' ' . 
			($Group -> getId() == $this -> getId() ? ' disabled="disabled"' : NULL) . '>' . 
				$Group -> getLabel() . 
			'</option>';
		}
		
		$opts .= '<option value="last" ' . ($selected == false ? 'selected="selected"' : NULL) . '>Placer en dernier</option>';
		
		$tModules = Module::getAll();
		$optsMod = '';
		
		$optsMod .= '<option value="0"' . ($this -> idx_module == 0 ? ' selected="selected"' : '') . '>Lier à la configuration</option><option disabled="disabled">--------</option>';
		
		foreach($tModules as $Module) {
			$optsMod .= '
			<option value="' . $Module -> getId() . '"' . ($Module -> getId() == $this -> idx_module ? ' selected="selected"' : '') . '>'
				. $Module -> getLabel() . 
			'</option>';
		}
		
		
		$Form = new Form();
		$Form -> setUrl('');
		$Form -> setClass('DataForm');
		$Form -> addLang('', 'Informations du groupe de champs');
		
		$Field = $Form -> addField('label', '', false);
		$Field -> setTitle('Nom du groupe');
		$Field -> setHelper('Ce nom devrait être unique');
		$Field -> setField('<input type="text" name="groupLabel" value="' . $this -> label . '" />');
		

		$Field = $Form -> addField('order', '', false);
		$Field -> setTitle('Placer avant');
		$Field -> setField('<select name="groupOrder">' . $opts . '</select>');
		
		$Field = $Form -> addField('idxmodule', '', false);
		$Field -> setTitle('Lier au module');
		$Field -> setField('<select name="groupIdxModule">' . $optsMod . '</select>');

		return $Form -> display();
	}
	
	public static function getAll($idxModule = false) {
		
		$tGrps = array();
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_GROUPFIELDS) . '` ' . ($idxModule ? ' WHERE `idx_module` = ' . intval($idxModule) . ' ' : '') . ' ORDER BY `order` ASC';
		
		if($resSql = self::query($strSql)) {
			while($dataSql = mysql_fetch_assoc($resSql)) {
				$tGrps[] = new GroupFields($dataSql);
			}

			return $tGrps;
		}
	}
	
	public static function showAll() {
		
		$strHtml = '
		<table cellpadding="0" cellspacing="0" class="Data">	
			<tr><th>Label</th></tr>
		';
		$tGrps = GroupFields::getAll();
		
		$i = 0;
		foreach($tGrps as $Grp) {
			$strHtml .= '
			<tr rel="' . $Grp -> getId() . '" class="' . ($i % 2 == 0 ? 'Even' : 'Odd') . '">
				<td class="FirstCol">' . $Grp -> getLabel() . '</td>
			</tr>
			';
			$i++;
		}
		
		if(count($tGrps) == 0)
			$strHtml .= '<tr><td class="FirstCol Error">Aucun groupe n\'a été installée</td></tr>';	
		
		$strHtml .= '</table>';
		return self::getInstance('Message') -> display() . $strHtml;
	}
	
	public function remove() {
		
		// Remove category from categories table
		$strSql = 'DELETE FROM `' . Sql::buildTable(T_CFG_GROUPFIELDS) . '` WHERE `id` = ' . intval($this -> id) . ' LIMIT 1';
		$this -> query($strSql);
		
		// Uncategorify the corresponding modules
		$strSql = 'UPDATE `' . Sql::buildTable(T_CFG_FIELDS) . '` SET `idx_group` = 0 WHERE `idx_group` = ' . intval($this -> id);
		$this -> query($strSql);
	}
}

?>