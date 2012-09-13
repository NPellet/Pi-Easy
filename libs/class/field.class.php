<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');
	
class Field extends Object {

	protected $id, $name, $label, $type, $group, $required, $multilang, $rss, $fblike_fieldtype, $order, $priority = 3, $helper, $placeholder;
	protected $errors;
	protected $lang;
	protected $oldName, $wasMultilang;
	protected $modFields, $cfgFields = array();
	protected $idxModule, $Module;
	protected $oneField;
	protected $_class = array();

	public function __construct($dataSql = NULL) {
		
		$this -> modFields = array('' => 'VARCHAR (250) NOT NULL');

		if($dataSql == NULL) 
			return true;

		extract($dataSql);
		$this -> id = $id;
		$this -> name = $name;
		$this -> label = $label;
		$this -> type = $type;
		$this -> group = $idx_group;
		$this -> required = (bool) $required;
		$this -> multilang = (bool) $multilang;
		$this -> rss = (bool) $rss;
		$this -> order = $order;
		$this -> fblike_fieldtype = $fblike_fieldtype;
		$this -> priority = $priority;
		$this -> helper = $helper;
		$this -> placeholder = $placeholder;

		$this -> idxModule = $idx_module;
		$this -> Module = Module::buildFromId($idx_module);

		$this -> errors['empty'] = 'Le champ %s est obligatoire. Il doit être rempli';
	}


	public static function buildFromType($dataSql) {
		
		if(is_array($dataSql))
			$type = $dataSql['type'];
		else {
			$type = $dataSql;
			$dataSql = NULL;	
		}
		
		$Field = false;

		switch($type) {
			
			case 'string':
				$Field = new FieldString($dataSql);
			break;
			
			case 'video':
				$Field = new FieldVideo($dataSql);
			break;
			
			case 'textarea':
				$Field = new FieldText($dataSql);
			break;
			
			case 'picture':
				$Field = new FieldPicture($dataSql);
			break;
			
			case 'map':
				$Field = new FieldMap($dataSql);
			break;
			
			case 'time':
				$Field = new FieldTime($dataSql);
			break;
			
			case 'date':
				$Field = new FieldDate($dataSql);
			break;

			case 'checkbox':
				$Field = new FieldCheckbox($dataSql);
			break;

			case 'numeric':
				$Field = new FieldNumeric($dataSql);
			break;

			case 'link':
				$Field = new FieldLink($dataSql);
			break;
			
			case 'email':
				$Field = new FieldEmail($dataSql);
			break;
			
			case 'enum':
				$Field = new FieldEnum($dataSql);
			break;
			
			case 'mp3':
			case 'file':
				$Field = new FieldFile($dataSql);
			break;
			
			case 'idx':
				$Field = new FieldIdx($dataSql);
			break;
			
			case 'fbpost':
				$Field = new FieldFacebookpost($dataSql);
			break;

			case 'fbevent':
				$Field = new FieldFacebookevent($dataSql);
			break;
			
			case 'select_fields':
				$Field = new FieldSelectField();
			break;
/*			case 'mp3':
				$Field = new FieldMp3($dataSql);
			break;*/
		}

		return $Field;
	}
	
	public static function buildFromId($id) {
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_FIELD) . '` WHERE `id` = ' . intval($id) . ' LIMIT 1';
		if($resSql = Sql::query($strSql)) {
			if($dataSql = mysql_fetch_assoc($resSql))
				return Field::buildFromType($dataSql);	
			else
				return new Field();
		}
	}
	
	public function moduleFields() { return $this -> modFields; }
	public function configFields() { return $this -> cfgFields; }
	
	public function getLang() { return $this -> lang; }
	public function getLangs() { return $this -> multilang ? $this -> Module -> getLangs() : array('' => ''); }
	public function getSqlType($suf) { return @$this -> modFields[$suf]; }
	// Getters
	public function getId() { return $this -> id; }
	public function getModule() { return $this -> Module; }
	public function getName($format = false) {
		if($format)
			$this -> name = Sql::FormatDB(empty($this -> name) ? $this -> label : $this -> name);
		return $this -> name;
	}
	
	public function getType() { return $this -> type; }
	public function getLabel() { return stripslashes(htmlspecialchars($this -> label)); }
	public function getHelper() { return $this -> helper; }
	public function getIdxGroup() { return $this -> group; }
	public function isMultilang() { return $this -> multilang; }
	public function getPriority() { return $this -> priority; }
	public function isRequired() { return $this -> required; }
	public function isRSS() { return $this -> rss; }
	public function getOrder() { return $this -> order; }

	public function setLabel($label) { $this -> label = $label; }
	public function setPriority($priority) { $this -> priority = $priority; }
	public function setMultilang($blnMultilang) { $this -> multilang = $blnMultilang; } 
	public function setClass($class) { $this -> _class[] = $class; }

	public function oneField($mode) { $this -> oneField = $mode; }
	public function getFirstSuffix() { $suffix = array_keys($this -> modFields); return $suffix[0]; }
	public function getFirstLang() { $langs = array_keys($this -> getLangs()); return $langs[0]; }
	
	public function setIdxModule($idxModule) {
		$this -> idxModule = intval($idxModule);
		$this -> Module = Module::buildFromId($idxModule);
	}
  	public function getIdxModule() {return $this -> idxModule; }
	public function setName($strName) { $this -> name = $strName; }
	
	// Fonctions d'interface
	public function treat($value) { return $value; } // Traitement des données
	public function check() { return false; } // Vérification des données
	public function display($value) { return stripslashes($value); }
	
	// Fonctions de langue
	public function setLang($lang) { 
		
		if(!$this -> multilang)
			return NULL;
	
		if(in_array($lang, array_keys($this -> Module -> getLangs()))) 
			$this -> lang = $lang;
		else
			return false;
	}
		
	public function setFirstLang() {
		if(!$this -> multilang) $this -> lang = NULL;
		else {
			$modLangs = array_keys($this -> Module -> getLangs());
			$this -> lang = $modLangs[0];
		}
	}
	
	

	
	public function handleLang($from, $to, $oldField = false) {

		$sql = array();

		$oldName = !$oldField ? $this -> name : $oldField -> getName();
		$oldType = !$oldField ? $this -> type : $oldField -> getType();
		$oldFields = !$oldField ? $this -> moduleFields() : $oldField -> moduleFields();
		
		if(!$oldField)
			$oldField = $this;
		
		$strSql = 'ALTER TABLE `' . $this -> Module -> getTable() . '` ';
		$fromC = $from;
		
		foreach($this -> modFields as $suffix => $sqlType) {
		
			$toC = $to;
			
			$nbFrom = count($from);
			for($i = 0; $i < $nbFrom; $i++) {
				$langFrom = $fromC[$i];

				if(isset($toC[$i]) && in_array($suffix, array_keys($oldFields))) {
					$oldNameSql = $oldField -> sqlName($suffix, $langFrom);
					$langTo = $toC[$i];
					$this -> setLang($langTo);
					$newName = $this -> sqlName($suffix, $langTo);
					$sql[] = $this -> sqlChangeField('change', $oldNameSql, $newName, $this -> getSqlType($suffix));
					unset($fromC[$i]);
					unset($toC[$i]);

				} 
			}
			
			foreach($toC as $key => $langTo) {
				$this -> setLang($langTo);
				$newName = $this -> sqlName($suffix, $langTo);
				$sql[] = $this -> sqlChangeField('add', $newName, $this -> getSqlType($suffix));
			}
		}
		
		if(!empty($oldFields) && count($oldFields) > 0)
			foreach($oldFields as $suffix => $sqlType) {
//				if(!in_array($suffix, array_keys($this -> moduleFields()))) 
				foreach($fromC as $key => $langTo) {
					$oldNameSql = $oldField -> sqlName($suffix, $langTo);
					$sql[] = $this -> sqlChangeField('remove', $oldNameSql);
				}
			}
			
		$strSql .= implode(', ', $sql);
		
		return $this -> query($strSql) ? true : $this -> log('sql:query');		
	}
	
	public function sqlName($suffix = false, $lang = false) {

		if(!$suffix || !array_key_exists($suffix, $this -> modFields))
			$suffix = $this -> getFirstSuffix();
		
		return $this -> formName($suffix, $lang);
	}
	

	public function formName($suffix = false, $lang = false) {

		return ($this -> type == 'idx' ? FIELD_IDX_PREFIX : '') 
				. $this -> name
				. (!$suffix ? NULL : '_' . $suffix)
				. (!$lang ? $this -> getLang() : '_' . $lang);
	}
	
	private function sqlChangeField($mode, $p1, $p2 = NULL, $p3 = NULL) {
		
		switch($mode) {
			
			case 'add':
				$fieldTo = Sql::secure($p1);
				$sqlType = $p2;
				return ' ADD `' . $fieldTo . '` ' . $sqlType;
			break;
			
			case 'change':
				$fieldFrom = Sql::secure($p1);
				$fieldTo = Sql::secure($p2);
				$sqlType = $p3;
				return ' CHANGE `' . $fieldFrom . '` `' . $fieldTo . '` ' . $sqlType;
			break;
			
			case 'remove':
				$fieldFrom = Sql::secure($p1);
				return ' DROP `' . $fieldFrom . '`';
			break;
		}
		
		return false;
	}
		
	
	public function save($oldField) {
		
		$sql = array();

		$sql['name'] = htmlspecialchars($this -> name);
		$sql['label'] = $this -> label;
		$sql['type'] = $this -> type;
		$sql['required'] = $this -> required;
		$sql['multilang'] = $this -> multilang;
		$sql['idx_group'] = $this -> group;
		$sql['rss'] = $this -> rss;
		$sql['order'] = $this -> order;
		$sql['priority'] = $this -> priority;
		$sql['helper'] = $this -> helper;
		$sql['fblike_fieldtype'] = $this -> fblike_fieldtype;
		$sql['placeholder'] = $this -> placeholder; 
		$sql['idx_module'] = $this -> idxModule; 
		
		$sql = array_merge($sql, $this -> configFields());

		if(!$this -> query(Sql::buildSave(T_CFG_FIELD, $this -> nav('field'), $sql)))
			return $this -> log('sql:query');
		
		if(empty($this -> id))
			$this -> id = mysql_insert_id();
		
					//($from, $to, $oldName, $oldFields, $sqlTo) {		
		$modLangs = array_keys($this -> Module -> getLangs());
		$oldLangs = $oldField -> getId() != NULL ? ($oldField -> isMultilang() ? $modLangs : array('')) : array();
		$newLangs = $this -> isMultilang() ? $modLangs : array('');

		$this -> handleLang($oldLangs, $newLangs, $oldField);
		
		if($oldField -> getType() != $this -> getType())
			$oldField -> handleDir(true);			

		$this -> handleDir();
		
		$this -> saveOrder($this -> order, $this -> id, 'Field', ' AND `idx_module` = ' . $this -> idxModule);
		return true;
	}
	
	public function edit() {

		if(!Security::userAdmin())
			return $this -> log('right:0:4');
		
		if(empty($_POST['formEntry']))
			return $this -> formEdit();

		$proceed = true;
		
		extract($_POST);
		$sql['id'] = $this -> nav('field');
		$sql['idx_module'] = $this -> nav('module');
		$sql['label'] = $fieldLabel;
		$sql['name'] = $fieldName;
		$sql['type'] = $fieldType;
		$sql['idx_group'] = $fieldGroup;
		$sql['helper'] = $fieldHelper;
		$sql['placeholder'] = $fieldPlaceholder;
		$sql['multilang'] = !isset($fieldMultilang) ? false : true;
		$sql['required'] = !isset($fieldRequired) ? false : true;
		$sql['rss'] = !isset($fieldRSS) ? false : true;
		$sql['priority'] = $fieldPriority;
		$sql['fblike_fieldtype'] = $fbLikeType;
		$sql['order'] = $fieldOrder;
		
		$sql = array_merge($sql, $this -> configFields());
		
		$Field = Field::buildFromType($sql);
		$name = $Field -> getName(true);
		
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_MODULE) . '` WHERE LOWER(`name`) = "' . Sql::secure(strtolower($name)) . '" LIMIT 1';
	
		if($resSql = $this -> query($strSql)) {
			if(mysql_num_rows($resSql) == 1) {
				$proceed = false;
				$this -> log('field:0:1');	
			}
		} else {
			$proceed = false;
			$this -> log('sql:query');
		}
		
		if($fieldLabel == NULL && $fieldName == NULL)	 {
			$this -> errors['nom_displayed'] = true;
			$this -> errors['nom_bdd'] = true;
			$proceed = false;
		}
		
		if($proceed == false)
			return $this -> formEdit();
		
		$this -> log('field:1:1');
		
		if($Field -> save($this))
			$this -> redirect($this -> url(array('field' => NULL, 'action' => 'show')));
	}


	public function getGroups($id = false) {
		
		$tGrps = GroupFields::getAll($id ? $id : $this -> Module -> getId());
		$empty = new GroupFields;
		$empty -> setId(0);
		$empty -> setName('Aucun groupe');
		$tGrps = array_merge(array($empty), $tGrps);
		
		return $tGrps;
	}
	
	
	private function formEdit() {
		global $_fieldTypes;
		
		$Add = new Button('Add', 'Ajouter', false);
		$Remove = new Button('Remove', 'Supprimer', false);
		
		$optsType = NULL;
		foreach($_fieldTypes as $group => $fields) {
			$optsType .= '<optgroup label="' . $group . '">';
			foreach($fields as $label => $name) {
				$optsType .= '
				<option value="' . $name . '"' . ($name == $this -> type ? ' selected="selected"' : NULL) . '>
					' . $label . '
				</option>';
			}
			$optsType .= '</optgroup>';
		}

		$tFields = Field::getAll($this -> idxModule);
		
		

		$tGrps = $this -> getGroups();
		
		$optsFields = NULL;
		$selected = false;
		foreach($tGrps as $Group) {
			
			$optsFields .= '<optgroup label="' . $Group -> getLabel() . '">';
			
			foreach($tFields as $Field) {
				
				if($Field -> getIdxGroup() != $Group -> getId())
					continue;
					
				if($Field -> getOrder() > $this -> order && $selected == false && $this -> order != 0) {
					$strSelected = ' selected="selected"';
					$selected = true;
				} else
					$strSelected = NULL;
					
				$optsFields .= '
				<option value="' . $Field -> getOrder() . '"' . $strSelected . ' ' . 
				($Field -> getId() == $this -> getId() ? ' disabled="disabled"' : NULL) . '>' . 
					$Field -> getLabel()
				 . '</option>';
			}
			
			$optsFields .= '</optgroup>';
		}
				
		$optsFields .= '<option value="last" ' . ($selected == false ? 'selected="selected"' : NULL) . '>Placer en dernier</option>';

		// Groupe
		$optsGrp = NULL;
		foreach($tGrps as $Grp) {
			$optsGrp .= '<option value="' . $Grp -> getId() . '" ' . ($this -> group == $Grp -> getId() ? ' selected="selected"' : '') . '>' . $Grp -> getLabel() . '</option>';
		}

		// Opts IDX
		$tModules = Module::getAll();
		$optsIdx = array();
		foreach($tModules as $Module) {
			$tFields = $Module -> getFields();	
			foreach($tFields as $Field) {
				$optsIdx[$Module -> getLabel()][$Field -> getId()] = $Field -> getLabel();	
			}
		}
	
		// Idx
		$FormIdx = new Form();
		$FormIdx -> addLang('', 'Configuration de la clé étrangère');
		$FormIdx -> setClass('cfgIdx DataForm');
		
		$FieldMultiple = $FormIdx -> addField('idx_multiple', '', false);
		$FieldMultiple -> setTitle('Plusieurs clés possibles');
		$FieldMultiple -> setField('<input type="checkbox" name="fieldIdxMultiple"' . (@$this -> idxMultiple ? ' checked="checked"' : NULL) . ' />');

		$FieldIdx = $FormIdx -> addField('idx_idxfield', '', false);
		$FieldIdx -> setTitle('Clé étrangère');
		
		if($this -> type == 'idx') {
			$strIdx = NULL;
			foreach($this -> idxFields as $idx)
				$strIdx .= '<p><select name="fieldIdxFields[]">' . Html::buildList($optsIdx, $idx) . '</select></p>';		
		} else
			$strIdx = '<p><select name="fieldIdxFields[]">' . Html::buildList($optsIdx) . '</select></p>';
	
		$strIdx .= '
		<ul class="Buttons Actions">' . 
				$Add -> display(true) . $Remove -> display(true) . '
		</ul>';	
		
		$FieldIdx -> setField($strIdx);

		// Enumeration
		$FormEnum = new Form();
		$FormEnum -> addLang('', 'Configuration de l\'énumération');
		$FormEnum -> setClass('cfgEnum DataForm');
		$FormEnum -> setUrl('');
		
		foreach($this -> Module -> getLangs() as $lang => $name) {
			
			$unilang = NULL;
			$multilang = NULL;			
			$first = true;
			
			$this -> setLang($lang);
			$langEnum = $this -> getLang();
			
			if($this -> type == 'enum') {	
				foreach($this -> enumList[$langEnum] as $list) {
					
					  $multilang .= '
					  <p>
						<input type="text" name="fieldEnum[' . $lang . '][]" value="' . $list . '" />
					  </p>';
					  
					  $unilang .= '
					  <p>
						<input type="text" name="fieldEnum[]" value="' . $list . '" />
					  </p>';
					}
					
			} else {
				
				$this -> setLang($lang);
				$multilang = '
				<p>
					<input type="text" name="fieldEnum[' . $lang . '][]" value="" />
				</p>';
						
				$unilang = $first ? '
				<p>
					<input type="text" name="fieldEnum[]" value="" />
				</p>' : NULL;
			}
				
			$multilang .= '
			<ul class="Buttons Actions">' . 
				$Add -> display(true) . $Remove -> display(true) . '
			</ul>';	

			$unilang .= '
			<ul class="Buttons Actions">' . 
				$Add -> display(true) . $Remove -> display(true) . '
			</ul>';	

			$Multi = $FormEnum -> addField('Multilang', $name, false);	
			$Multi -> setTitle('Langue : ' . $name);
			$Multi -> setField($multilang);
			
			if($first == true) {
				$Uni = $FormEnum -> addField('Unilang', $name, false);
				$Uni -> setTitle('Langue unique');
				$Uni -> setField($unilang);
			}
			
			$first = false;
		}
		
		
		$FormGeneral = new Form();
		$FormGeneral -> addLang('', 'Configuration générale');
		$FormGeneral -> setClass('DataForm');
		
		$Label = $FormGeneral -> addField('label', '', false);
		$Label -> setTitle('Nom affiché');
		
		if(isset($this -> errors['nom_displayed']))
			$Label -> setError('Le nom du champ ne peut pas être vide');
			
		$Label -> setField('<input type="text" name="fieldLabel" value="' . $this -> getLabel() . '" />');

		$Name = $FormGeneral -> addField('name', '', false);
		$Name -> setTitle('Nom (BDD)');
		
		if(isset($this -> errors['nom_bdd']))
			$Name -> setError('Le nom du champ ne peut pas être vide');

		$Name -> setHelper('Ce nom sera utilisé dans la base de données pour configurer le champ');
		$Name -> setField('<input type="text" name="fieldName" value="' . $this -> name . '" />');
		
		$Type = $FormGeneral -> addField('type', '', false);
		$Type -> setTitle('Type');
		$Type -> setField('<select name="fieldType">' . $optsType . '</select>');
		
		$Group = $FormGeneral -> addField('group', '', false);
		$Group -> setTitle('Groupe');
		$Group -> setField('<select name="fieldGroup">' . $optsGrp . '</select>');
		
		$Placeholder = $FormGeneral -> addField('placeholder', '', false);
		$Placeholder -> setTitle('Aide à la saisie');
		$Placeholder -> setHelper('Ce texte s\'affichera à la place du contenu du champ. Pas applicable à tous les types');
		$Placeholder -> setField('<input type="text" name="fieldPlaceholder" value="' . $this -> placeholder . '" />');
		
		$Helper = $FormGeneral -> addField('helper', '', false);
		$Helper -> setTitle('Informations complémentaires');
		$Helper -> setHelper('Ce texte doit aider l\'utilisateur à reconnaître ce qu\'il doit remplir dans le champ');
		$Helper -> setField('<input type="text" name="fieldHelper" value="' . $this -> helper . '" />');
		
		$Order = $FormGeneral -> addField('order', '', false);
		$Order -> setTitle('Placer avant');
		$Order -> setField('<select name="fieldOrder">' . $optsFields . '</select>');
		
		$Required = $FormGeneral -> addField('required', '', false);
		$Required -> setTitle('Obligatoire');
		$Required -> setField('<input type="checkbox" name="fieldRequired"' . ($this -> required ? ' checked="checked"' : NULL) . ' />');

		$Multilang = $FormGeneral -> addField('multilang', '', false);
		$Multilang -> setTitle('Plusieurs langues');
		$Multilang -> setField('<input type="checkbox" name="fieldMultilang"' . ($this -> multilang ? ' checked="checked"' : NULL) . ' ' . ($this -> Module -> isMultilang() ? NULL : ' disabled="disabled"') . ' />');

		$FPriority = new FieldEnum();
		$FPriority -> setName('fieldPriority');
		$FPriority -> setList(array('' => array('1' => 'Haute', '2' => 'Moyenne', '3' => 'Basse')));
		$Priority = $FormGeneral -> addField('priority', '', false);
		$Priority -> setTitle('Priorité');
		$Priority -> setHelper('Les priorités définissent les critères d\'apparitions des champs. Attention à la choisir soigneusement');
		$Priority -> setField($FPriority -> showField($this -> priority, false));



		$FFBLike = new FieldEnum();
		$FFBLike -> setName('fbLikeType');
		$FFBLike -> setList(array('' => array(
								'Général' => 
									array(
										'title' => 'Titre', 
										'image' => 'Image', 
										'description' => 'Description'
									),
									
								'Audio' =>
									array(
										'audio' => 'Fichier audio',
										'audio:title' => 'Titre',
										'audio:artist' => 'Artiste',
										'audio:album' => 'Album',
										'audio:type' => 'Mime-type' 
									),
									
								'Vidéo' => 
									array(
										'video' => 'Fichier vidéo',
										'video:height' => 'Hauteur de la vidéo',
										'video:width' => 'Largeur de la vidéo',
										'video:type' => 'Mime-type de la vidéo'								
									)
								)));
								
		$FBLike = $FormGeneral -> addField('fblike', '', false);
		$FBLike -> setTitle('Facebook Open Graph tag');
		$FBLike -> setHelper('');
		$FBLike -> setField($FFBLike -> showField($this -> fblike_fieldtype, false));
		
		$Rss = $FormGeneral -> addField('rss', '', false);
		$Rss -> setTitle('Flux RSS');
		$Rss -> setHelper('Encore non implémenté');
		$Rss -> setField('<input type="checkbox" name="fieldRSS"' . ($this -> rss ? ' checked="checked"' : NULL) . ' />');
		


		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// CHAMPS TEXTE //////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$FormText = new Form();
		$FormText -> addLang('', 'Configuration du champ texte');
		$FormText -> setClass('cfgTextarea DataForm');
		
		$Mode = $FormText -> addField('mode', '', false);
		$Mode -> setTitle('Mode');
		$Mode -> setField('
			<select name="fieldTextMode">
				<option value="Html"' . (@$this -> text_mode == 'Html' ? ' selected="selected"' : NULL) . '>Html</option>
				<option value="Wysiwyg"' . (@$this -> text_mode == 'Wysiwyg' ? ' selected="selected"' : NULL) . '>Wysiwyg</option>
				<option value="WysiwygExtended"' . (@$this -> text_mode == 'WysiwygExtended' ? ' selected="selected"' : NULL) . '>Wysiwyg Etendu</option>
				<option value="String"' . (@$this -> text_mode == 'Strict' ? ' selected="selected"' : NULL) . '>Strict</option>
			</select>
');
	
	
	
	
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// CHAMPS NUMERIQUE //////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$FormNumeric = new Form();
		$FormNumeric -> addLang('', 'Configuration le champ numérique');
		$FormNumeric -> setClass('cfgNumeric DataForm');
		$Float = $FormNumeric -> addField('float', '', false);
		$Float -> setTitle('Nombre décimal');
		$Float -> setField('<input type="checkbox" name="fieldNumericFloat"' . (@$this -> numericFloat == true ? ' checked="checked"' : NULL) . ' />');




		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// CHAMPS IMAGE & THUMB //////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$FormPicture = new Form();
		$FormPicture -> addLang('', 'Configuration de l\'image');
		$FormPicture -> setClass('cfgPicture DataForm');
		
		$NbFiles = $FormPicture -> addField('nbfiles', '', false);
		$NbFiles -> setTitle('Nombre de fichiers');
		$NbFiles -> setField('<input type="text" name="fieldPicturenbFiles" value="' . (isset($this -> nbFiles) ? $this -> nbFiles : 1) . '" />');

		$WMax = $FormPicture -> addField('widthmax', '', false);
		$WMax -> setTitle('Largeur maximale');
		$WMax -> setField('<input type="text" name="fieldPictureImgWidth" value="' . (@$this -> imgWidth != NULL ? $this -> imgWidth : IMG_MAX_WIDTH) . '" />');	

		$HMax = $FormPicture -> addField('heightmax', '', false);
		$HMax -> setTitle('Hauteur maximale');
		$HMax -> setField('<input type="text" name="fieldPictureImgHeight" value="' . (@$this -> imgHeight != NULL ? $this -> imgHeight : IMG_MAX_HEIGHT) . '" />');

		$Thumb = $FormPicture -> addField('thumb', '', false);
		$Thumb -> setTitle('Créer une miniature');
		$Thumb -> setField('<input type="checkbox" name="fieldPictureThb"' . (@$this -> imgThumb != NULL ? ' checked="checked"' : NULL) . ' />');

		$FormThumb = new Form();
		$FormThumb -> addLang('', 'Configuration de la miniature');
		$FormThumb -> setClass('cfgThumb DataForm');
		
		$WMax = $FormThumb -> addField('wmax', '', false);
		$WMax -> setTitle('Largeur de la miniature');
		$WMax -> setField('<input type="text" name="fieldPictureThbWidth" value="' . (@$this -> thumbWidth != NULL ? $this -> thumbWidth : THB_MAX_WIDTH) . '" />');
			
		$HMax = $FormThumb -> addField('hmax', '', false);
		$HMax -> setTitle('Hauteur de la miniature');
		$HMax -> setField('<input type="text" name="fieldPictureThbHeight" value="' . (@$this -> thumbHeight != NULL ? $this -> thumbHeight : THB_MAX_HEIGHT) . '" />');


		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// CHAMPS FICHIER ////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$FormFile = new Form();
		$FormFile -> addLang('', 'Configuration du fichier');
		$FormFile -> setClass('cfgFile DataForm');
		
		$NbFiles = $FormFile -> addField('nbfiles', '', false);
		$NbFiles -> setTitle('Nombre maximal de fichiers');
		$NbFiles -> setField('<input type="text" name="fieldFilenbFiles" value="' . (isset($this -> nbFiles) ? $this -> nbFiles : 1) . '" />');
		
		
		$cFields = array();
		$tFields = $this -> Module -> getFields();
		$cFields[''][0] = 'Aucun';
		foreach($tFields as $Field)
			$cFields[''][$Field -> getId()] = $Field -> getLabel();	
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// CHAMPS FACEBOOK POST //////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$FormFBPost = new Form();
		$FormFBPost -> addLang('', 'Configuration du champ Facebook post');
		$FormFBPost -> setClass('cfgFBPost DataForm');
		
		$fb = !empty($this -> fb_post_infos) ? $this -> fb_post_infos : array();
		//$fball = array('Titre', 'Message (court)', 'Image', 'Texte du lien', 'Description du lien');
		$fball = array('Titre', 'Description', 'Lien', 'Texte du lien', 'Image');
		
	 	$i = 0;
		foreach($fball as $k => $v) {
			$Field = $FormFBPost -> addField($k, '', false);
			$Field -> setTitle($v);
			$Select = new FieldEnum();
			$Select -> setList($cFields);
			$Select -> setName('FBPost[]');
			$Field -> setField($Select -> showField(!empty($fb[$i]) ? $fb[$i] : '', false));
			$i++;
		}
		
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// CHAMPS FACEBOOK EVENT /////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
		$FormFBEvent = new Form();
		$FormFBEvent -> addLang('', 'Configuration du champ Facebook event');
		$FormFBEvent -> setClass('cfgFBEvent DataForm');
		
		$fb = !empty($this -> fb_event_infos) ? $this -> fb_event_infos : array();
		$fball = array('Titre', 'Description', 'Date de début', 'Date de fin', 'Heure de début', 'Heure de fin', 'Lieu', 'Image');

		$i = 0;
		foreach($fball as $k => $v) {
			$Field = $FormFBEvent -> addField($k, '', false);
			$Field -> setTitle($v);
			$Select = new FieldEnum();
			$Select -> setList($cFields);
			$Select -> setName('FBEvent[]');
			$Field -> setField($Select -> showField(!empty($fb[$i]) ? $fb[$i] : '', false));
			$i++;
		}
		
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		$strHtml = 
		$this -> getInstance('Message') -> display() . 
		'<h2>' . ($this -> id != NULL ? 'Editer le champ ' . $this -> getLabel() : 'Ajouter un nouveau champ') . '</h2>';
		
		$Form = new Form();
		$Form -> setClass('DataForm');
		$Form -> setName('formField');
		$Form -> setUrl('');
		$Form -> addForm($FormGeneral);
		$Form -> addForm($FormPicture);
		$Form -> addForm($FormThumb);
		$Form -> addForm($FormNumeric);
		$Form -> addForm($FormText);
		$Form -> addForm($FormFile);
		$Form -> addForm($FormIdx);
		$Form -> addForm($FormEnum);
		$Form -> addForm($FormFBPost);
		$Form -> addForm($FormFBEvent);

		$strHtml .= $Form -> display() . self::showAll($this -> idxModule);

		return $strHtml;
	}
	
	public static function getAll($moduleId = false) {
		$tFields = array();		
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_FIELD) . '`' . ($moduleId ? ' WHERE `idx_module` = ' . intval($moduleId) : '') . ' ORDER BY `order` ASC';
		if($resSql = self::query($strSql)) {
			while($dataSql = mysql_fetch_assoc($resSql)) {
				$tFields[] = Field::buildFromType($dataSql);	
			}
		}
		
		return $tFields;
	}

	public static function showAll($idModule) {
		
		$strHtml = NULL;
		$tGrps = Field::getGroups($idModule);
		
		$head = '
		<table cellpadding="0" cellspacing="0" class="Data">	
			<tr>
				<th>Label</th>
				<th>Name</th>
				<th>Type</th>
				<th>Multilangue</th>
				<th>Requis</th>
				<th>Indexé</th>
				<th>RSS</th>
			</tr>
		';

		$tail = '</table>';
		
		$tFields = Field::getAll($idModule);
		foreach($tGrps as $Group) {
			
			$strHtml .= ($Group -> getLabel() != '' ? '<h2>' . $Group -> getLabel() . '</h2>' : '') . $head;
			$i = 0;
			foreach($tFields as $Field) {
				if($Field && $Field -> getIdxGroup() == $Group -> getId()) {
					$i++;
					$strHtml .= '
					<tr rel="' . $Field -> getId() . '">
						<td class="FirstCol">' . $Field -> getLabel() . '</td>
						<td>' . $Field -> getName() . '</td>
						<td>' . $Field -> getType() . '</td>
						<td>' . ($Field-> isMultilang() ? 'Oui' : 'Non') . '</td>
						<td>' . ($Field-> isRequired() ? 'Oui' : 'Non') . '</td>
						<td>' . $Field-> getPriority() . '</td>
						<td>' . ($Field-> isRSS() ? 'Oui' : 'Non') . '</td>
					</tr>
					';
				}
			}
			
			if($i == 0)
				$strHtml .= '
				<tr><td class="FirstCol Error" colspan="6">Aucun champ dans ce module</td></tr>';
				
			$strHtml .= $tail;
		}
		
		return self::getInstance('Message') -> display() . $strHtml;
	}
	
	public function remove() {
		
		// Remove the entry from field table
		$strSql = 'DELETE FROM `' . Sql::buildTable(T_CFG_FIELD) . '` WHERE `id` = ' . (int) $this -> id . ' LIMIT 1';
		$this -> query($strSql);
		
		// Remove the fields from module table
		$this -> handleLang(array_keys($this -> getLangs()), array());
	}
	
	protected function handleDir() { return; }
}

?>