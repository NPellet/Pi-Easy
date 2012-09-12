<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Entry extends Object {
	
	public $cfg = array(), $data = array();
	
	public function __construct($Module, $id = 0, $dataSql = NULL) {

		$this -> cfg['Module'] = $Module;
		$this -> cfg['Fields'] = $Module -> getFields();
		$this -> cfg['errors'] = false;

		$this -> cfg['idx_rubrique'] = 0;

		if($id == NULL)
			return;

		$this -> cfg['id'] = $id;
		
		if(!empty($dataSql))
			$this -> cfg['id'] = $dataSql['id'];
		
		$this -> cfg['idx_rubrique'] = !empty($dataSql['idx_rubrique']) ? $dataSql['idx_rubrique'] : 0;
		$this -> cfg['order'] = !empty($dataSql['order']) ? $dataSql['order'] : 0;

		foreach($this -> cfg['Fields'] as $Field)
			$this -> setFromSql($Field, $dataSql);
		
		$this -> set('date_added', @$dataSql[FIELD_DATE_ADDED], '', 'treat');	
	}

	public function setFromData($Field, $data, $mode = 'form') {

		foreach($Field -> getLangs() as $lang => $trash) {
			$Field -> setLang($lang);
			$fieldName = $Field -> getName();
			$this -> set($Field -> getName(), $data[$Field -> formName()], $lang, $mode);
		}
	}
	
	public function setFromSql($Field, $data) {
		$value = array();
			
		foreach($Field -> getLangs() as $lang => $trash) {
			$Field -> setLang($lang);
			$fieldName = $Field -> getName();
			foreach($Field -> moduleFields() as $suffix => $field)
				$value[$suffix] = $data[$Field -> sqlName($suffix, $lang)];

			if(count($value) == 1) {
				$this -> set($Field -> getName(), current($value), $lang, 'treat');
			} else
				$this -> set($Field -> getName(), $value, $lang, 'treat');
		}

	}
	
	private function getModule() { return $this -> cfg['Module']; }
	private function getId() { return !empty($this -> cfg['id']) ? $this -> cfg['id'] : NULL; }

	public function set($keyField, $value, $lang, $mode) {

		if(empty($this -> data[$keyField][$lang][$mode]))
			$this -> data[$keyField][$lang][$mode] = array();

		$this -> data[$keyField][$lang][$mode] = $value;
	}
	
	public function get($keyField, $lang) { return $this -> data[$keyField][$lang]; }
  	private function getFields() { return $this -> cfg['Fields']; }
	
	public function treatData() {
		foreach($this -> getFields() as $Field) {
			foreach($Field -> getLangs() as $lang => $trash) {
				$Field -> setLang($lang);
				$keyField = $Field -> getName();
				$this -> data[$keyField][$lang]['treat'] = $Field -> treat($this -> data[$keyField][$lang]['form']);	
			}
		}
	}
	
	public function checkData() {
		foreach($this -> getFields() as $Field)
			foreach($Field -> getLangs() as $lang => $trash) {
				$Field -> setLang($lang);
				$keyField = $Field -> getName();
				$error = $Field -> check($this -> data[$keyField][$lang]['form']);
				$this -> data[$Field -> getName()][$lang]['error'] = $error;

				if($error != NULL)
					$this -> cfg['errors'] = true;
			}
	}

	public function hasErrors() {
		return $this -> cfg['errors'];
	}
	
	
	public function save() {
			
		$sql = array();
		foreach($this -> getFields() as $Field) {

			$fields = $Field -> moduleFields();
			foreach($Field -> getLangs() as $lang => $trash) {

				foreach($fields as $suffix => $field) {
					$value = $this -> data[$Field -> getName()][$lang]['treat'];
					$value = count($value) > 1 ? $value[$suffix] : $value;
					$sql[$Field -> sqlName($suffix, $lang)] = $value;
				}
			}
		}
		
		$sql['actif'] = 1;
		if($this -> cfg['Module'] -> hasRubriques())
			$sql['idx_rubrique'] = $this -> cfg['idx_rubrique'];
		
		$sqlAdded = array_merge($sql, array(FIELD_DATE_ADDED => time()));
		$sqlUpdated = array_merge($sql, array(FIELD_DATE_UPDATED => time()));
		$strSql = Sql::buildSave($this -> cfg['Module'] -> getBaseTable(), @$this -> cfg['id'], $sqlAdded, $sqlUpdated);
		
		if(!$this -> query($strSql)) {
			$this -> log('entry:0:1');
			return;	
		}
		
		if(empty($this -> cfg['id']))
			$this -> cfg['id'] = mysql_insert_id();
		
		return true;
	}
	
	public function remove($completely = false) {
		
		if(!Security::userAccess($this -> cfg['Module'] -> getId(), 'remove'))
			return $this -> log('right:0:3');
		
		if($completely == true) {
			
			$strSql = 'DELETE FROM `' . $this -> getModule() -> getTable() . '` WHERE `id` = ' . intval($this -> getId()) . ' LIMIT 1';
			$Fields = $this -> getFields();
			foreach($Fields as $Field) {
				foreach($Field -> getLangs() as $lang) {
					$Field -> removeFile($this -> data[$Field -> getBaseName()][$lang]);
				}
			}
			if($this -> query($strSql)) 
				return true;
		}
		
		$strSql = 'UPDATE `' . $this -> getModule() -> getTable() . '` SET `actif` = 0 WHERE `id` = ' . intval($this -> getId()) . ' LIMIT 1';
	
		if($this -> query($strSql))
			return true;	
	
		return $this -> log('sql:query');
	}
	
	public function formEdit() {	

		$langs = $this -> cfg['Module'] -> getLangs();
		$form = NULL;
		/*
		$form .= '<tr>';
		foreach($langs as $lang => $details) { 
			$form .= '<th colspan="2"><h2>' . $details . '</h2></th>';
		}
		$form .= '</tr>';
		*/
		if($this -> cfg['Module'] -> hasRubriques()) {
			
			$Rubrique = new FieldEnum();
			$Rubrique -> setMultilang(false);
			$Rubrique -> setList($this -> getRubriqueList($this -> cfg['idx_rubrique']));
			$Rubrique -> setLabel('Rubrique');
			$Rubrique -> setName('idx_rubrique');
			$this -> cfg['Fields'][] = $Rubrique;
		}

		$FormGen = new Form();
		$FormGen -> setUrl($this -> url(array('sent' => true)));
		$FormGen -> setClass('DataForm');
		$FormGen -> setClass('Entry');


		$tGroups = GroupFields::getAll($this -> cfg['Module'] -> getId());
		$others = new GroupFields();
		$others -> setId(0);
		array_unshift($tGroups, $others);
	
		foreach($tGroups as $Group) {
			
			if($Group -> getLabel() != '')
				$FormGen -> append($Group -> getLabel() != NULL ? '<h2 class="GroupFields">' . $Group -> getLabel() . '</h2>' : NULL);
				
			$Form = new Form();
			$Form -> setUrl($this -> url(array('sent' => true)));
			$Form -> setClass('DataForm');
			$Form -> setClass('Entry');
			
			foreach($langs as $lang => $details) 
				$Form -> addLang($lang, $details != NULL ? $details : NULL);				
			
			foreach($this -> cfg['Fields'] as $Field) {
				
				if($Field -> getIdxGroup() != $Group -> getId())
					continue;
					
			//	$form .= '<tr>';
				$i = 0;
	
				foreach($langs as $lang => $details) {
	
					$i++;
					$Field -> setLang($lang);
					$data = @$this -> data[$Field -> getName()][$Field -> isMultilang() ? $lang : ''];
					$error = @$data['error'];
						
					if($FormField = $Form -> addField($Field -> getName(), $lang, $Field -> isMultilang())) {
						$FormField -> setTitle($Field -> getLabel() . ($Field -> isRequired() ? ' *' : NULL));
						if($error != NULL)
							$FormField -> setError(sprintf($error, '<em>' . $Field -> getLabel() . '</em>'));
						if($Field -> getHelper() != NULL)
							$FormField -> setHelper($Field -> getHelper());
						if($Field -> getName() == 'idx_rubrique')
							$FormField -> setField($Field -> showField(@$this -> cfg['idx_rubrique'], false));	
						else
							$FormField -> setField($Field -> showField(@$data['treat']));	
					}
				}	
			}
			$FormGen -> addForm($Form);
		}
		$strHtml = 
		
		'<div class="Spacer"></div>' . 
		$this -> getInstance('Message') -> display() . $FormGen -> display();

		return $strHtml;
	}

	public static function showAll($module) {
		$list = new ListData(Module::buildFromId($module));
		$list -> get();
		return Instance::getInstance('Message') -> display() . $list -> displayTable();
	}
	
	public function edit() {
		
		$navEntry = $this -> nav('entry');
		if(!empty($navEntry) && !Security::userAccess($this -> cfg['Module'] -> getId(), 'edit'))
			return $this -> log('right:0:1');
		else if(empty($navEntry) && !Security::userAccess($this -> cfg['Module'] -> getId(), 'add'))
			return $this -> log('right:0:2');
	
		if(empty($_POST['formEntry'])) 
			return $this -> formEdit();
	
		$Fields = $this -> cfg['Module'] -> getFields();

		foreach($Fields as $Field)
			$this -> setFromData($Field, $_POST);

		$this -> cfg['idx_rubrique'] = @$_POST['idx_rubrique'];
		
		$this -> treatData();
		$this -> checkData();

		if(!$this -> hasErrors()) 
			if($this -> save())	{
				$this -> log('entry:1:1', true);
				return $this -> redirect($this -> url(array('mode' => 'entry', 'action' => 'show', 'entry' => NULL, (empty($navEntry) ? 'added' : 'updated') => $this -> cfg['id'])));
			}
		return $this -> formEdit();
	}
	
	private function getRubriqueList($idRubrique) {
		
		$optsRubriques = array();
		$tRubriques = $this -> getModule() -> getRubriques();
		$Rubrique = new Rubrique(array('id' => 0, 'idx_module' => $this -> getModule() -> getId(), 'order' => '1'));
		$Rubrique -> setLabel(NULL, 'Hors Rubrique');
		$tRubriques[] = $Rubrique;
		
		foreach($tRubriques as $Rubrique) {
			$optsRubriques[$Rubrique -> getId()] = $Rubrique -> getLabel();	
		}
		$langs = $this -> cfg['Module'] -> getLangs();
		$firstLang = array_keys($langs);
		$firstLang = $firstLang[0];

		return array('' => $optsRubriques);
	}
}

?>