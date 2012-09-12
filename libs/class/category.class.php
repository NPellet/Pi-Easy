<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Category extends Object {
	
	private $id, $label, $order;
	
	public static function buildFromId($id) {
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_CATEGORY) . '` WHERE `id` = ' . intval($id) . ' LIMIT 1';
		if($resSql = Sql::query($strSql))
			if($dataSql = mysql_fetch_assoc($resSql))
				$obj = new Category($dataSql);
			else {
				$obj = new Category(array('id' => '', 'order' => '', 'label' => 'Hors catégorie'));
			
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
	}
	
	public function getId() { return $this -> id; }
	public function getLabel() { return $this -> label; }
	public function getOrder() { return $this -> order; }
	public function save() {
		
		$sql['label'] = $this -> label;

		if($this -> query(Sql::buildSave(T_CFG_CATEGORY, $this -> id, $sql))) {
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
		$this -> label = $categorieLabel;
		$this -> order = $categoryOrder;
		$this -> id = $this -> nav('category');
		
		$proceed = true;
		
		$strSql = '
		SELECT * FROM `' . Sql::buildTable(T_CFG_CATEGORY) . '` 
		WHERE LOWER(`label`) = "' . Sql::secure(strtolower($this -> label)) . '" 
		LIMIT 1';
			
		if($resSql = $this -> query($strSql)) {
			if(mysql_num_rows($resSql) == 1) {
				$dataExisting = mysql_fetch_assoc($resSql);
				if($dataExisting['id'] != $this -> id) {
					$proceed = false;
					$this -> log();	
				}
			}
		} else {
			$proceed = false;
			$this -> log();
		}
			
		if($proceed == false)
			return $this -> formEdit();
		
		if($this -> save()) {
			$this -> log('cat:1:1');
			$this -> redirect($this -> url(array('categorie' => NULL, 'action' => 'show')));
		}
	}
	
	private function formEdit() {
		
		
		$tCategories = Category::getAll();
		$optsCategories = NULL;
		$selected = false;
		
		foreach($tCategories as $Category) {
			if($this -> order != NULL && $Category -> getOrder() > $this -> order && $selected == false) {
				
				$strSelected = ' selected="selected"';
				$selected = true;
			} else
				$strSelected = NULL;
				
			$optsCategories .= '
			<option value="' . $Category -> getOrder() . '"' . $strSelected . ' ' . 
			($Category -> getId() == $this -> getId() ? ' disabled="disabled"' : NULL) . '>' . 
				$Category -> getLabel() . 
			'</option>';
		}
		$optsCategories .= '<option value="last" ' . ($selected == false ? 'selected="selected"' : NULL) . '>Placer en dernier</option>';
		
		
		$Form = new Form();
		$Form -> setUrl('');
		$Form -> setClass('DataForm');
		$Form -> addLang('', 'Informations de la catégorie');
		
		$Field = $Form -> addField('label', '', false);
		$Field -> setTitle('Nom de la catégorie');
		$Field -> setHelper('Spécifier un nom clair et de préférence unique');
		$Field -> setField('<input type="text" name="categorieLabel" value="' . $this -> label . '" />');
		

		$Field = $Form -> addField('order', '', false);
		$Field -> setTitle('Placer avant');
		$Field -> setField('<select name="categoryOrder">' . $optsCategories . '</select>');
		

		return $Form -> display();
	}
	
	public static function getAll() {
		
		$tCategories = array();
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_CATEGORY) . '` ORDER BY `order` ASC';
		if($resSql = self::query($strSql)) {
			while($dataSql = mysql_fetch_assoc($resSql)) {
				$tCategories[] = new Category($dataSql);
			}
			
			return $tCategories;
		}
	}
	
	public static function showAll() {
		
		$strHtml = '
		<table cellpadding="0" cellspacing="0" class="Data">	
			<tr><th>Label</th></tr>
		';
		$tCategories = Category::getAll();

/*
<td class="Action Edit"><a href="' . self::url(array('action' => 'edit', 'category' => $Category -> getId())) . '">Editer</a></td>
<td class="Action Remove"><a rel="category:' . $Category -> getId() . '">Supprimer</a></td>
*/
		
		$i = 0;
		foreach($tCategories as $Category) {
			$strHtml .= '
			<tr rel="' . $Category -> getId() . '" class="' . ($i % 2 == 0 ? 'Even' : 'Odd') . '">
				<td class="FirstCol">' . $Category -> getLabel() . '</td>
			</tr>
			';
			$i++;
		}
		
		if(count($tCategories) == 0) {
			$strHtml .= '<tr><td class="FirstCol Error">Aucune catégorie n\'a été installée</td></tr>';	
		}
		
		$strHtml .= '</table>';
		
		return self::getInstance('Message') -> display() . $strHtml;
	}
	
	public function remove() {
		
		// Remove category from categories table
		$strSql = 'DELETE FROM `' . Sql::buildTable(T_CFG_CATEGORY) . '` WHERE `id` = ' . intval($this -> id) . ' LIMIT 1';
		$this -> query($strSql);
		
		// Uncategorify the corresponding modules
		$strSql = 'UPDATE `' . Sql::buildTable(T_CFG_MODULES) . '` SET `idx_category` = 0 WHERE `idx_category` = ' . intval($this -> id);
		$this -> query($strSql);
	}
}

?>