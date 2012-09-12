<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Config extends Security {
	
	private $tCfg;
	
	public function __construct() {	
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_CONFIG) . '`';
		if($resSql = Sql::query($strSql)) 
			while($dataSql = mysql_fetch_assoc($resSql)) 
				$this -> tCfg[$dataSql['key']] = array(
					'type' => $dataSql['type'], 
					'value' => $dataSql['value'], 
					'admin' => $dataSql['admin'], 
					'label' => $dataSql['label'],
					'group' => $dataSql['idx_group']);
		else
			return $this -> log();
	}
	
	public function get($p1, $p2 = NULL) {
		if($p2 === NULL)
			return isset($this -> tCfg[$p1]) ? $this -> tCfg[$p1]['value'] : false;

		if($this -> tCfg[$p1]['admin'] && !Security::userAdmin())
			return false;

		$this -> tCfg[$p1]['value'] = $p2;
		return true;
	}

	

	public function saveConfig() {
		
		$sql = array();
		foreach($this -> tCfg as $key => $data) {
			$sql['key'] = $key;
			$sql['value'] = $data['value'];
			$this -> query(Sql::buildSave(T_CFG_CONFIG, NULL, $sql));
		}
		return true;
	}
	
	public function edit() {

		if(!empty($_POST['formEntry'])) {	
			foreach($this -> tCfg as $key => $details) {
				
				$field = Field::buildFromType($details['type']);
				$field -> setName($key);
				$field -> oneField(true);

				$val = $field -> treat(@$_POST[$key]);
				$this -> get($key, $val);		
			}

			$this -> saveConfig();
			$this -> log('config:1:1');
		}
	
		return $this -> form();
	}
	
	public function form($key = NULL) {
		
		$strHtml = Instance::getInstance('Message') -> display();
		
		$tCfg = $key == NULL ? $this -> tCfg : array($key => $this -> tCfg[$key]);
						
		$Form = new Form();
		$Form -> addLang('', 'Administration de la configuration');
		$Form -> setUrl('');
		$Form -> setClass('DataForm');

		
		foreach($tCfg as $key => $details) {
			
			$field = Field::buildFromType($details['type']);
			$field -> setName($key);
			$field -> oneField(true);
			
			$FormField = $Form -> addField($key, '', false);
			$FormField -> setTitle($details['label']);
			$FormField -> setField($field -> showField($details['value']));
		}
		

		return $strHtml . $Form -> display();
	}
	
	public function editKey() {
		
		if(empty($_POST['formEntry']))
			return $this -> formKey();
			
		extract(@$_POST);
		$sql = array();
		$sql['key'] = $cfgKey;
		$sql['label'] = $cfgLabel;
		$sql['admin'] = !empty($cfgAdmin) ? 1 : 0;
		$sql['type'] = $cfgType;
		$sql['group'] = $cfgGroup;
		
		$strSql = Sql::buildSave(T_CFG_CONFIG, false, $sql);
		if(Sql::query($strSql)) {
			$this -> log('config:1:2', true);
			$this -> redirect(array('action' => 'show', 'cfg' => false));
		} else
			$this -> log('config:0:1');
		
		return $this -> formKey($sql);
	}
	
	private function formKey($icfg = NULL) {
		
		if(!User::userAdmin())
			return $this -> log();
		
		
		$cfg = $this -> nav('cfg');
		$key = $cfg;
		if($icfg) {
			$cfg = $icfg;
		} elseif($cfg) {
			Instance::getInstance('Page') -> addNavigation('Editer une clé');
			$cfg = $this -> tCfg[$cfg];
		} else {
			Instance::getInstance('Page') -> addNavigation('Ajouter une clé');
			$cfg = array('key' => '', 'label' => '', 'admin' => 0, 'type' => 'string');
		}

		global $_fieldTypes;
		
		$optsType = NULL;
		foreach($_fieldTypes as $group => $fields) {
			$optsType .= '<optgroup label="' . $group . '">';
			foreach($fields as $label => $name) {
				$optsType .= '
				<option value="' . $name . '"' . ($name == $cfg['type'] ? ' selected="selected"' : NULL) . '>
					' . $label . '
				</option>';
			}
			$optsType .= '</optgroup>';
		}
		
		$optsGroups = NULL;
		$Groups = GroupFields::getAll(0);
		foreach($Groups as $Group) {
			$optsGroups .= '<option value="' . $Group -> getId() . '" ' . ($name == $cfg['group'] ? ' selected="selected"' : '') . '>' . $Group -> getLabel() . '</option>';
		}
		
		$strHtml = Instance::getInstance('Message') -> display();
		
		$Form = new Form();
		$Form -> setUrl('');
		$Form -> addLang('', 'Informations sur la clé de configuration');
		$Form -> setClass('DataForm');
		
		$Field = $Form -> addField('key', '', false);
		$Field -> setTitle('Clé');
		$Field -> setField('<input type="text" name="cfgKey" ' . ($key !== NULL ? 'readonly="readonly"' : '') . ' value="' . $key . '" />');
		
		$Field = $Form -> addField('label', '', false);
		$Field -> setTitle('Label');
		$Field -> setField('<input type="text" name="cfgLabel" value="' . $cfg['label'] . '" />');
		
		$Field = $Form -> addField('type', '', false);
		$Field -> setTitle('Type de champ');
		$Field -> setField('<select name="cfgType">' . $optsType . '</select>');		
		
		$Field = $Form -> addField('group ', '', false);
		$Field -> setTitle('Groupe de champ');
		$Field -> setField('<select name="cfgGroup">' . $optsGroups . '</select>');		
		
		$Field = $Form -> addField('admin', '', false);
		$Field -> setTitle('Administrateur seulement ?');
		$Field -> setField('<input type="checkbox" name="cfgAdmin" ' . ($cfg['admin'] != 0 ? ' checked="checked"' : '') . ' />');
		
		$strHtml .= $Form -> display();
		return $strHtml;
	}
	
	public function removeKey($key) {
		
		$strSql = 'DELETE FROM `' . Sql::buildTable(T_CFG_CONFIG) . '` WHERE `key` = "' . Sql::secure($key) . '"';
		
		if(Sql::query($strSql))
			return true;
		else 
  			return false;
	}
	
	public function getCfg() { return $this -> tCfg; }
}

?>