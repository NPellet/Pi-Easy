<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Module extends Object {
	
	private $defaultLang = array('name' => NULL, 'abr' => NULL);
	private $id;
	private $name, $label, $multilang, $sortable, $fblike_objecttype, $rubriques, $options, $order, $idx_category, $langs;
	private $oldName, $wasMultilang, $oldLangs;
	private $tFields;
	
	public static function buildFromId($id) {
		if($obj = Instance::getInstance('Module_' . $id))
			return $obj;
		 
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_MODULE) . '` WHERE `id` = ' . intval($id) . ' LIMIT 1';
		if($resSql = Sql::query($strSql)) 
			if($dataSql = mysql_fetch_assoc($resSql))
				$obj = new Module($dataSql);
			else 
				$obj = new Module();
		
		Instance::setInstance($obj, 'Module_' . $id);
		return $obj;	
	}

	public function getId() { return $this -> id; }
	public function getLabel() { return stripslashes(htmlspecialchars($this -> label)); }
	public function getIdxCategory() { return $this -> idx_category; }
	public function getOrder() { return $this -> order; }
	public function getTable($forcedFilename = false) {
		return Sql::buildTable(T_PREFIX_MODULES . ($forcedFilename ? $forcedFilename : $this -> name));	
	}
	
	public function getBaseTable($forcedFilename = false) {
		return T_PREFIX_MODULES . ($forcedFilename ? $forcedFilename : $this -> name);
	}
	
	public function getLangs() { return count($this -> langs) == 0 ? array('' => '') : $this -> langs; }	
	public function isMultilang() { return $this -> multilang; }
	public function isSortable() { return $this -> sortable; }
	public function hasRubriques() { return $this -> rubriques; }
	public function setOrder($intOrder) { $this -> order = $intOrder; }

	public function __construct($dataSql = NULL) {

		if(empty($dataSql))
			return;

		extract($dataSql);
		$this -> id = $id;
		$this -> name = $name;
		$this -> oldName = $name;
		$this -> label = $label;
		$this -> multilang = (bool) $multilang;
		$this -> wasMultilang = (bool) $multilang;
		$this -> sortable = (bool) $sortable;
		$this -> rubriques = (bool) $rubriques;
		$this -> idx_category = $idx_category;
		$this -> fblike_objecttype = $fblike_objecttype;
		$this -> order = $order;
		
		$this -> langs = array();
		for($i = 1; $i <= 3; $i++) 
			if(${'lang' . $i . '_name'} != NULL)
				$this -> langs[${'lang' . $i . '_abr'}] = ${'lang' . $i . '_name'};

		$this -> oldLangs = $this -> langs;				
	}
	
	public function getLanguages() {
		return $this -> multilang ? $this -> langs :  $this -> defaultLang;
	}

	public function save() {
		
		$sql = array();
		$sql['name'] = $this -> getName();
		$sql['label'] = stripslashes($this -> label);
		$sql['multilang'] = $this -> multilang;
		$sql['sortable'] = $this -> sortable;
		$sql['rubriques'] = $this -> rubriques;
		$sql['idx_category'] = $this -> idx_category;
		$sql['fblike_objecttype'] = $this ->  fblike_objecttype;
			
		$keyLangs = array_keys($this -> langs);
		for($i = 1; $i <= 3; $i++) {
			$sql['lang' . $i . '_abr'] = @$keyLangs[$i - 1];
			$sql['lang' . $i . '_name'] = @$this -> langs[$keyLangs[$i - 1]];
		}
		// Sauvegarde dans la table des modules
		if(!$this -> query(Sql::buildSave(T_CFG_MODULE, $this -> id, $sql)))
			return $this -> log('sql:query');
		
		// Met l'id si nécessaire
		if($this -> id == NULL) {
			$this -> id = mysql_insert_id();
			$this -> createTable();	
			Filemanager::createFolder($this -> getFolder());
		} else {
			$this -> changeTable();	
			rename($this -> getFolder($this -> oldName), $this -> getFolder());
		}

		// Gère le champ order et idx_rubrique
		$handle = array('idx_rubrique' => $this -> rubriques, 'order' => $this -> sortable);
		$strSql = 'ALTER TABLE `' . $this -> getTable() . '` ';
		foreach($handle as $field => $hasField) {
		
			if($hasField)
				$strSql2 = $strSql . ' ADD `' . $field . '` INT(3) NOT NULL AFTER `actif` ';
			else
				$strSql2 = $strSql . ' DROP `' . $field . '`';

			$this -> query($strSql2);
		}

		// Sauvegarde l'ordre
		$this -> saveOrder($this -> order, $this -> id);
		
		return true;
	}
	
	// Crée la table du module
	private function createTable() {
		
		$strSql = 'CREATE TABLE IF NOT EXISTS `' . $this -> getTable($this -> name) . '` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `' . FIELD_DATE_ADDED . '` int(10) unsigned NOT NULL,
		  `' . FIELD_DATE_UPDATED . '` int(10) unsigned NOT NULL,
		  `actif` tinyint(1) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
		
		return $this -> query($strSql) ? true : $this -> log('sql:query');
	}
	
	// Modifie la table du module
	private function changeTable() {
		
		// Change le nom de la table du module
		if($this -> oldName != $this -> name) {
			$strSql = '
			RENAME TABLE 
				`' . $this -> getTable($this -> oldName) . '` 
			TO 
				`' . $this -> getTable() . '`';
				
			if(!$this -> query($strSql))
				return $this -> log('sql:query');
		}
				
		// Regarde s'il faut supprimer les rubriques
		if($this -> rubriques == false)
			foreach($this -> getRubriques() as $Rubrique)
				$Rubrique -> remove();
		
		// Change les champs pour le multilangue
//		if($this -> multilang != $this -> wasMultilang) {
			$tFields = $this -> getFields();
			foreach($tFields as $Field) {
				$oldLangs = ($this -> wasMultilang && $Field -> isMultilang()) ? array_keys($this -> oldLangs) : array('');
				$newLangs = ($this -> multilang && $Field -> isMultilang()) ? array_keys($this -> langs) : array('');
				$Field -> handleLang($oldLangs, $newLangs, false);
			}
	//	}
	}
	
	private function getName() {
		$this -> name = Sql::FormatDB(empty($this -> name) ? $this -> label : $this -> name);
		return $this -> name;
	}
	
	public function getFolder($forcedFilename = NULL) {
		global $_baseUrl;
		return $_baseUrl . DATA_ROOT_REL . 
			FOLDER_UPLOAD_MEDIAS . 
			FOLDER_UPLOAD_MODULES . 
			$this -> id . 
			'_' . 
			FormatFTPName(empty($forcedFilename) ? $this -> name : $forcedFilename) . 
			'/';
	}
	
	public function getFields() {
		
		if($this -> id == NULL)
			return false;
		
		if(empty($this -> tFields)) {
			$tFields = array();
			$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_FIELD) . '` WHERE `idx_module` = "' . $this -> id . '" ORDER BY `order` ASC';
			if($resSql = $this -> query($strSql))
				while($dataSql = mysql_fetch_assoc($resSql))
					$tFields[] = Field::buildFromType($dataSql);
			
			$this -> tFields = $tFields;
		} else
			return $this -> tFields;
			
		return $tFields;
	}
	
	public function getRubriques() {
		
		if($this -> id == NULL)
			return false;
			
		$tRubriques = array();
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_RUBRIQUE) . '` WHERE `idx_module` = "' . $this -> id . '" ORDER BY `order` ASC';
		if($resSql = $this -> query($strSql)) {
			while($dataSql = mysql_fetch_assoc($resSql)) {
				$tRubriques[] = new Rubrique($dataSql);
			}
		}
		
		return $tRubriques;
	}
	
	public function getCategorie() {
		return Category::buildFromId($this -> idx_category);
	}
	
	
	public function edit() {
		
		if(!Security::userAdmin())
			return $this -> log('right:0:4');
		
		if(empty($_POST['formEntry']))
			return $this -> formEdit();
		
		$proceed = true;
		extract($_POST);
		$this -> label = $moduleLabel;
		$this -> name = $moduleName;
		$this -> rubriques = !isset($moduleRubriques) ? false : true;
		$this -> sortable = !isset($moduleSortable) ? false : true;
		$this -> multilang = !isset($moduleMultilang) ? false : true;
		$this -> order = $moduleOrder;
		$this -> idx_category = $moduleIdxCategory;
		$this -> fblike_objecttype = $fblike_objecttype;
		
		$this -> langs = array();
		for($i = 1; $i <= 3; $i++)
			if(!empty(${'moduleLang' . $i . 'Abr'}))
				$this -> langs[${'moduleLang' . $i . 'Abr'}] = ${'moduleLang' . $i . 'Name'};
		
		$name = $this -> getName();
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_MODULE) . '` WHERE LOWER(`name`) = "' . Sql::secure(strtolower($name)) . '" AND `id` != ' . intval($this -> id) . ' LIMIT 1';
		if($resSql = $this -> query($strSql)) {
			if(mysql_num_rows($resSql) == 1) {
				$proceed = false;
				$this -> log('mod:0:1');	
			}
		} else {
			$proceed = false;
			$this -> log('sql:query');
		}
			
		if($proceed == false)
			return $this -> formEdit();

		if($this -> save()) {
			$this -> log('mod:1:1', true);
			if(!$this -> nav('module'))
				$this -> redirect($this -> url(array('mode' => 'field', 'action' => 'add', 'module' => $this -> id)));
			else
				$this -> redirect($this -> url(array('mode' => 'module', 'action' => 'edit', 'module' => $this -> id)));
		}
	}
	
	private function formEdit() {
		
		$tModules = Module::getAll();
		$optsModules = NULL;
		$selected = false;
		foreach($tModules as $Module) {
			if($Module -> getOrder() > $this -> order && $selected == false) {
				$strSelected = ' selected="selected"';
				$selected = true;
			} else
				$strSelected = NULL;
				
			$optsModules .= '
			<option value="' . $Module -> getId() . '"' . $strSelected . ' ' . 
			($Module -> getId() == $this -> getId() ? ' disabled="disabled"' : NULL) . '>' . 
				$Module -> getLabel() . 
			'</option>';
		}
		$optsModules .= '<option value="last" ' . ($selected == false ? 'selected="selected"' : NULL) . '>Placer en dernier</option>';
		
		
		$tCategories = Category::getAll();
		$optsCategories = '<option value="0">Aucune catégorie</option>';
		foreach($tCategories as $Category) {
			$strSelected = ($Category -> getId() == $this -> idx_category) ? 'selected="selected"' : NULL;				
			$optsCategories .= '
			<option value="' . $Category -> getId() . '"' . $strSelected . '>' . $Category -> getLabel() . '</option>';
		}
		
		$FormGen = new Form();
		$FormGen -> setUrl('');

		$Form = new Form();
		
		$Form -> addLang('', 'Informations sur le module');
		$Form -> setUrl('');
		$Form -> setClass('DataForm');

		$FieldLabel = $Form -> addField('Label', '', false);
		$FieldLabel -> setTitle('Label');
		$FieldLabel -> setHelper('Nom apparent du module. Peut contenir des accents et des caractères spéciaux');
		$FieldLabel -> setField('<input type="text" name="moduleLabel" value="' . $this -> getLabel() . '" />');

		$FieldName = $Form -> addField('Name', '', false);
		$FieldName -> setTitle('Nom');
		$FieldName -> setHelper('Nom du module pour la base de données. Indiquer un nom de module UNIQUE');
		$FieldName -> setField('<input type="text" name="moduleName" value="' . $this -> name . '" />');

		$FieldCategory = $Form -> addField('category', '', false);
		$FieldCategory -> setTitle('Placer dans la catégorie');
		$FieldCategory -> setField('<select name="moduleIdxCategory">' . $optsCategories . '</select>');

		$FieldOrder = $Form -> addField('order', '', false);
		$FieldOrder -> setTitle('Placer avant');
		$FieldOrder -> setField('<select name="moduleOrder">' . $optsModules . '</select>');

		$FieldRubriques = $Form -> addField('rubriques', '', false);
		$FieldRubriques -> setTitle('Rubriques');
		$FieldRubriques -> setField('<input type="checkbox" name="moduleRubriques" ' . ($this -> rubriques ? 'checked="checked"' : NULL) . '" />');

		$FieldSortable = $Form -> addField('sortable', '', false);
		$FieldSortable -> setTitle('Triable');
		$FieldSortable -> setField('<input type="checkbox" name="moduleSortable" ' . ($this -> sortable ? 'checked="checked"' : NULL) . '" />');

		$FFBLike = new FieldEnum();
		$FFBLike -> setName('fblike_objecttype');
		$FFBLike -> setList(
			array('' => array(
				'Activités' => 
				array(
					'activity' => 'Activité',
					'sport' => 'Sport'
					),
					
				'Business' => 
				array(
					'bar' => 'Bar',
					'company' => 'Entreprise',
					'cafe' => 'Café',
					'hotel' => 'Hôtel',
					'restaurant' => 'Restaurant'
					),
					
    			'Groupes' => 
				array(
					'cause' => 'Bar',
					'sports_league' => 'Ligue de sport',
					'sports_team' => 'Equipe de sport'
					),
    			
				'Organisations' => array(
					'band' => 'Groupe de musique',
					'government' => 'Gouvernement',
					'non_profit' => 'Sans profit',
					'school' => 'Ecole',
					'university' => 'Université'
				),
				
				'Personnes' => array(
					'actor' => 'Acteur',
					'athlete' => 'Athlète',
					'author' => 'Auteur',
					'director' => 'Directeur',
					'musician' => 'Musicien',
					'politician' => 'Politicien',
					'public_figure' => 'Figure publique'
				),
				
				'Emplacements' => array(
					'city' => 'Ville',
					'country' => 'Pays',
					'landmark' => 'Département',
					'state_province' => 'Province'
				),
    			
				'Produits & Loisirs' => array(
					'album' => 'Album',
					'book' => 'Livre',
					'drink' => 'Boisson',
					'food' => 'Nourriture',
					'game' => 'Jeu',
					'product' => 'Produit',
					'song' => 'Chanson',
					'movie' => 'Film',
					'tv_show' => 'Emission TV'				
				),
				
				'Site Internet' => array(
				
				    'blog' => 'Blog',
				    'website' => 'Site Internet',
				    'article' => 'Article'			
				)
			)));
					
		
		$FieldFBType = $Form -> addField('fbtype', '', false);
		$FieldFBType -> setTitle('Type d\'object Facebook');
		$FieldFBType -> setField($FFBLike -> showField($this -> fblike_objecttype, false));
		
		$keyLangs = @array_keys($this -> langs);
		//$this -> getInstance('Message') -> display() . $Form -> display();
		$FormGen -> addForm($Form);

		$Form = new Form();
		$Form -> addLang('', 'Gestion des langues');
		$Form -> setUrl('');
		$Form -> setClass('DataForm');

		$Field = $Form -> addField('unilang', '', false);
		$Field -> setTitle('Multilingue');
		$Field -> setField('<input type="checkbox" name="moduleMultilang" ' . ($this -> multilang ? 'checked="checked"' : NULL) . '" />');

		for($i = 1; $i <= 3;$i++) {
			
			$Field = $Form -> addField('lang' . $i, '', false);
			$Field -> setTitle('Langue ' . $i);
			$Field -> setField('
			Nom : 					   
			<input type="text" name="moduleLang' . $i . 'Name" value="' . @$this -> langs[$keyLangs[$i - 1]] . '" /><br />
			Abrévation : 
			<input type="text" name="moduleLang' . $i . 'Abr" value="' . @$keyLangs[$i - 1] . '" />');
		}
		
		$FormGen -> addForm($Form);
		
		$strHtml = Instance::getInstance('Message') -> display() . $FormGen -> display();
		
		return $strHtml;
	}
	
	public static function getAll() {
		
		$tModules = array();
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_MODULE) . '` ORDER BY `order` ASC';
		if($resSql = Sql::query($strSql))
			while($dataSql = mysql_fetch_assoc($resSql))
				$tModules[] = new Module($dataSql);
		
		return $tModules;
	}
	
	public static function showAll() {
		
		$tCategories = Category::getAll();
		$HCategory = new Category(array('label' => 'Hors Catégorie', 'order' => 'last', 'id' => 0));
		$tCategories[] = $HCategory;

		$tModules = Module::getAll();
		$strHtml = '';

		foreach($tCategories as $Category) {
			
			$strHtml .= '<h2>' . $Category -> getLabel() . '</h2>';
			
			$strHtml .= '
			<table cellpadding="0" cellspacing="0" class="Data">	
				<tr><th>Label</th><th>Multilangue</th><th>Ordrable</th></tr>
			';
	
			$i = 0;
			foreach($tModules as $Module) {
				if($Module -> getIdxCategory() == $Category -> getId()) {
					$i++;
					$strHtml .= '
					<tr rel="' . $Module -> getId() . '" class="' . ($i % 2 == 0 ? 'Even' : 'Odd') . '">
						<td class="FirstCol">' . $Module -> getLabel() . '</td>
						<td>' . ($Module -> isMultilang() ? 'Oui' : 'Non') . '</td>
						<td>' . ($Module -> isSortable() ? 'Oui' : 'Non') . '</td>
					</tr>
					';
				}
			}
		
			if($i == 0)
				$strHtml .= '
				<tr>
					<td colspan="3" class="Error FirstCol">Aucun module n\'est installé</td>
				</tr>';
			
			$strHtml .= '</table>';
		}
		
		return $strHtml;
	}
	
	public function remove() {
		
		// Remove from module table
		$strSql = 'DELETE FROM `' . Sql::buildTable(T_CFG_MODULE) . '` WHERE `id` = ' . (int) $this -> id . ' LIMIT 1';
		$this -> query($strSql);
		
		// Remove the fields from field table
		$strSql = 'DELETE FROM `' . Sql::buildTable(T_CFG_FIELD) . '` WHERE `idx_module` = ' . (int) $this -> id;
		$this -> query($strSql);
		
		// Drop the table
		$strSql = 'DROP TABLE `' . $this -> getTable() . '`';
		$this -> query($strSql);
		
		// Remove the rubriques
		$tRubriques = $this -> getRubriques();
		if(is_array($tRubriques))
			foreach($tRubriques as $Rubrique)
				$Rubrique -> remove();
	}
	

	public function handleRubriques() {
		
		$strHtml = NULL;
		$strHtml .= '<div id="HandleRubriques">';
		
		$Rubriques = $this -> getRubriques();
		
		$mainFields = array();
		foreach($this -> getFields() as $Field) {
			if($Field -> getPriority() == 1)
				$mainFields[] = $Field;
		}
		
		$content = array();
		$strSql = 'SELECT * FROM `' . $this -> getTable() . '` WHERE `actif` = 1 ORDER BY `id` ASC';
		if($resSql = Sql::query($strSql)) {
			while($data = mysql_fetch_assoc($resSql)) {
				$content = '<div data-entry-id="' . $data['id'] . '" class="Entry">';
				foreach($mainFields as $Field)
					$content .= $data[$Field -> sqlName()] . ' ';
				$content .= '</div>';
				
				$contentRubriques[$data['idx_rubrique']][$data['id']] = $content;
			}
		}
	
		$i = 0;
		
		$strHtml .= '<table cellpadding="0" cellspacing="5" id="EditRubriques">';
		
		foreach($Rubriques as $Rubrique) {
			
			if($i % 3 == 0)
				$strHtml .= '</tr><tr>';
			
			$strHtml .= '
			<td>
				<div data-rub-id="' . $Rubrique -> getId() . '" '; 
					foreach($this -> getLangs() as $trash => $abr) {
						 $strHtml .= ' data-lang-' . $abr . '="' . $Rubrique -> getLabel($abr) . '"';
					}
					$strHtml .= ' class="Rubrique">
					<div class="Title">' . $Rubrique -> getLabel() . '</div>
					<div class="Content">';
					
					if(!empty($contentRubriques[$Rubrique -> getId()]))
						foreach($contentRubriques[$Rubrique -> getId()] as $content)
							$strHtml .= $content;
						
					$strHtml .= '
					</div>
					<div class="Spacer"></div>
				</div>
			</td>';
			$i++;
		}
		$strHtml .= '
		</tr>
		<tr>
			<td id="NoRubrique">
				<div data-rub-id="0" class="Rubrique NoRubrique">
					<div class="Title">Sans catégorie</div>
					<div class="Content">';
					
				if(!empty($contentRubriques[0]))
					foreach($contentRubriques[0] as $content)
						$strHtml .= $content;
					
				$strHtml .= '
						<div class="Spacer"></div>
					</div>
				</div>
			</td>
		</tr>';
		
		$strHtml .= '</table>';
		$strHtml .= '</div>';	
		$strHtml .= '
		<script language="javascript" type="text/javascript">
		<!--
			$.moduleLangs = ' . json_encode($this -> getLangs()) . ';
		-->
		</script>';
		
		return $strHtml;
	}
}


?>