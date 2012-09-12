<?php

class Plugin extends PluginController {
	
	public $id, $params;
	
	public function __construct($id) {
		global $_baseUrl;
		$this -> id = $id;
		$this -> params = $this -> getParams($id);	
		if(!defined('FOLDER_UPLOAD_GALERIES'))
			define('FOLDER_UPLOAD_GALERIES', $this -> params['image_folder']['value']);
	}
	
	public function run($id) {
		
		global $_baseUrl;
		
		Instance::getInstance('Page') -> addJs($_baseUrl . '/plugins/galeries/scripts.js');
		Instance::getInstance('Page') -> addCss($_baseUrl . '/plugins/galeries/galeries.css');
		Instance::getInstance('Page') -> addNavigation("Galeries");
		
		$strHtml = '<div id="PluginGalerie">';
		$Page = Instance::getInstance('Page');
		
		if($this -> nav('galerie')) {
			$Galerie = $this -> get('galerie', $this -> nav('galerie'));
			$Page -> addNavigation($Galerie['label']);
		}

		if($this -> nav('action') == 'edit' || $this -> nav('action') == 'add') {
			$Back = new Button('Back', 'Retour', true);
			$Back -> setUrl(array('action' => 'show'));
			$Page -> addButton($Back);
		} else {
			$Update = new Button('Reload', 'Actualiser', true);
			$Update -> setUrl(array());
			$Page -> addButton($Update);			
		}
		
		switch($this -> nav('mode')) {
			
			case 'facebook':
				$strHtml .= $this -> fb();
			break;
			case 'galerie':

				if($this -> nav('action') == 'edit' || $this -> nav('action') == 'add')
					$strHtml .= $this -> handleGalerie();
				
				else if($this -> nav('galerie') === NULL)
					$strHtml .= $this -> allGaleries();
				
				else 
					$strHtml .= $this -> allAlbums();
				
			break;
			case 'album':
				$strHtml .= $this -> handleAlbum();
			break;
			case 'image':
				$strHtml .= $this -> handleImages();
			break;
			default:
				$strHtml .= $this -> allGaleries();	
			break;
		}

		$strHtml .= '</div>';
		return $strHtml;
	}

	public function getFolder($idGalerie = false, $idAlbum = false) {
		if($idGalerie === false)
			$idGalerie = $this -> nav('galerie');
		global $_baseUrl;
		$strFolder = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_GALERIES;
		if($idGalerie === false)
			return $strFolder;
		$strFolder .= '/galerie_' . $idGalerie . '/' . ($idAlbum !== false ? 'album_' . $idAlbum : NULL) . '/';
		return $strFolder;
	}
	
	public function get($mode, $id = NULL) {
		
		$tGalerie = array();
		$nav = $id == NULL ? $this -> nav($mode) : $id;

		if(empty($nav))
			return array();

		$strSql = '
		SELECT 
			* 
		FROM 
			`' . Sql::buildTable($this -> params['t_' . $mode . 's']['value']) . '` 
		WHERE 
			`id` = ' . intval($nav) . ' 
		OR 
			`label` = "' . Sql::secure($nav) . '" 
		LIMIT 1';
		
		if($resSql = Sql::query($strSql))
			if($dataSql = mysql_fetch_assoc($resSql)) 
				$tGalerie = $dataSql;
				
		return $tGalerie;
	}
	
	private function handleGalerie() {
	
		if($this -> nav('action') != 'add' && $this -> nav('action') != 'edit')
			return $this -> allGaleries();	
		if(empty($_POST['formEntry'])) 
			return $this -> formGalerie();	
			
		$sql['label'] = $_POST['galerieLabel'];
		$sql['description'] = utf8_encode(html_entity_decode($_POST['galerieDescr']));
		$sql['date'] = strtotime($_POST['galerieDate']);
		
		if(!empty($_POST['link1']))
			$sql['link1'] = ';' . implode(';', $_POST['link1']) . ';';
			
		if(!empty($_POST['link2']))
			$sql['link2'] = ';' . implode(';', $_POST['link2']) . ';';

		$sql['watermark'] = implode(',', $_POST['watermark']['files']);
	//	$sql['watermark_pos'] = implode(',', $_POST['watermark']['text']);
	
		$strSql = Sql::buildSave($this -> params['t_galeries']['value'], $this -> nav('galerie'), $sql);
		
		$proceed = true;

		if($proceed) {
			if(Sql::query($strSql)) {
				if($this -> nav('galerie') == NULL) {
					$idGalerie = mysql_insert_id();
					
					$folder = $this -> getFolder($idGalerie);
					if(!@mkdir($this -> getFolder($idGalerie))) {
						
						$this -> log('galeries:0:2');
						$strSql = 'DELETE FROM `' . Sql::buildTable($this -> params['t_galeries']['value']) . '` WHERE `id` = ' . $idGalerie . ' LIMIT 1';
						Sql::query($strSql);
					}
				} else
					$idGalerie = $this -> nav('galerie');
								
				foreach($_POST['watermark']['files'] as $fle) {
					$fPath = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_TEMP . $fle;
					if($fle && file_exists($fPath))
						rename($fPath, $this -> getFolder($idGalerie) . $fle);
				}
		
			//	Object::order($this -> params['t_galeries'], $_POST['galerieOrder'], $idGalerie);
				return $this -> allGaleries();
			}
		}
		
		return $this -> formGalerie();
	}
	
	private function formGalerie() {
	
		$strSql = 'SELECT * FROM `' . Sql::buildTable($this -> params['t_galeries']['value']) . '` ORDER BY `order`';
		if($resSql = Sql::query($strSql)) {
			while($dataSql = mysql_fetch_assoc($resSql)) {
				$tList[$dataSql['order']] = $dataSql['label'];	
			}
		}

		$tGalerie = $this -> get('galerie');
		if(empty($tGalerie)) {
			$tGalerie = array('label' => '', 'description' => '', 'date' => time(), 'order' => '', 'watermark' => '', 'watermark_pos' => '');	
			for($i = 1; $i < 3; $i++) 
				$tGalerie['link' . $i] = '';
			
			Instance::getInstance('Page') -> addNavigation('Ajouter une galerie');
		} else {
			Instance::getInstance('Page') -> addNavigation('Modifier la galerie');
		}
		
		

		$Albums = new Button('Default', 'Albums');
		$Albums -> setUrl(array('action' => 'show', 'mode' => 'albums', 'galerie' => $tGalerie['id']));

		$Form = new Form();
		$Form -> addLang('', '');
		$Form -> setUrl('');
		$Form -> setClass('DataForm');
		
		$tList['last'] = 'Placer en dernier';
	
		$Field = $Form -> addField('label');
		$Field -> setTitle('Label');
		$Field -> setField('<input type="text" name="galerieLabel" value="' . $tGalerie['label'] . '" />');
		
		$Field = $Form -> addField('description');
		$Field -> setTitle('Description');
		$Textarea = new FieldText();
		$Textarea -> setName('galerieDescr');		
		$Textarea -> setMode('Wysiwyg');
		$Field -> setField($Textarea -> showField($tGalerie['description']));
		
		$Date = $Form -> addField('date', '', false);
		$Date -> setTitle('Date');
		$Date -> setField('<input type="text" name="galerieDate" class="Date" value="' . FormDate($tGalerie['date']) . '" />');

		$fieldOrder = new FieldEnum();
		$fieldOrder -> setName('galerieOrder');
		$fieldOrder -> setList(array('' => $tList));
		$Field = $Form -> addField('order', '', false);
		$Field -> setTitle('Placer après');
		$Field -> setField($fieldOrder -> showField($tGalerie['order']));

		for($i = 1; $i < 3; $i++) {
			
			if(!empty($this -> params['gal_link' . $i . '_field']['value'])) {
				$field = new FieldIdx();
				$field -> setMultiple(true);
				$field -> setIdxField($this -> params['gal_link' . $i . '_field']['value']);
				$field -> setName('link' . $i);
				$fieldForm = $Form -> addField('link' . $i, '', false);
				$fieldForm -> setTitle($this -> params['gal_link' . $i . '_text']['value']);
				$fieldForm -> setField($field -> showField($tGalerie['link' . $i]));
			}
		}
		
		$field = new FieldPicture();
		$field -> setName('watermark');
		$field -> setImgFolder($this -> getFolder());
		$field -> setNbFiles(1);
		$field -> setThb(false);
		$fieldForm = $Form -> addField('watermak', '', false);
		$fieldForm -> setTitle('Watermark');
		$fieldForm -> setField($field -> showField($tGalerie['watermark']));
		
		return $Form -> display();
	}
	
	private function allGaleries() {

		Instance::getInstance('Page') -> addNavigation('Liste des galeries');

		$strSql = 'SELECT * FROM `' . Sql::buildTable($this -> params['t_galeries']['value']) . '` ORDER BY `order`';
		$strHtml = NULL;
		
		$Add = new Button('Add', 'Ajouter');
		$Add -> setUrl(array('action' => 'add', 'mode' => 'galerie', 'galerie' => NULL));

		$Remove = new Button('Remove', 'Supprimer');
		$Remove -> setOptions(array(
			'Selectable' => true,
			'Disablable' => true,
			'Rel' => 'album'
		));

		$Album = new Button('Defaut', 'Albums');
		
		$Album -> setOptions(array(
			'Selectable' => true,
			'Disablable' => true,
			'Rel' => 'album',
			'Class' => 'Albums'
		));
		
		//$strHtml .= '<ul class="Actions Buttons">' . $Add -> display() . '</ul>';
		Instance::getInstance('Page') -> addButton($Add);
		Instance::getInstance('Page') -> addButton($Remove);
		Instance::getInstance('Page') -> addButton($Album);
		
		$strHtml .= Instance::getInstance('Message') -> display();

		
		$strHtml .= '<h2>Administrer les galeries</h2>';
		
		$strTable = '
		<table cellpadding="0" cellspacing="0" class="Data Entries" rel="galerie">
			<tr>
				<th>Label</th>
				<th>Description</th>
				<th>Date</th>
			</tr>		
		';
		
		if($resSql = Sql::query($strSql)) {
			while($dataSql = mysql_fetch_assoc($resSql)) {
				$strTable .= '
				<tr rel="' . $dataSql['id'] . '">
					<td class="FirstCol">' . $dataSql['label'] . '</td>
					<td>' . $dataSql['description'] . '</td>
					<td>' . FormatDate($dataSql['date']) . '</td>
				</tr>';
			}
		}

		$strTable .= '</table>';
		$strHtml .= $strTable;
		return $strHtml;
	}

	public function editAlbum($sql) {
		
		$proceed = true;
		$strSql = Sql::buildSave($this -> params['t_albums']['value'], $this -> nav('album'), $sql);
		
		if($proceed && Sql::query($strSql)) {
			$idAlbum = $this -> nav('album') == NULL ? mysql_insert_id() : $this -> nav('album');
			
			$folder = $this -> getFolder($sql['idx_galerie'], $idAlbum);
			if(!is_dir($folder))
				$this -> createFolder($folder);
				
			
			if(!is_dir($folder . '/thumbs') && !mkdir($folder . '/thumbs')) {
				$proceed = false;
				$this -> log('galeries:0:1');
			}
			
			if(!is_dir($folder . '/cover') && !mkdir($folder . '/cover')) {
				$proceed = false;
				$this -> log('galeries:0:1');
			}
						
			if(!is_dir($folder . '/cover/thumbs') && !mkdir($folder . '/cover/thumbs')) {
				$proceed = false;
				$this -> log('galeries:0:1');
			}						
	
			$this -> log('galeries:1:1', true);
			return $idAlbum;	
		}
		return false;
	}
	
	private function createFolder($folder) {
		
		if(!file_exists($folder) && !mkdir($folder, 0777, true)) {
			$proceed = false;
			$this -> log('galeries:0:1');
		}
		
		if(!file_exists($folder . '/thumbs') && !mkdir($folder . '/thumbs')) {
			$proceed = false;
			$this -> log('galeries:0:1');
		}
		 
		if(!file_exists($folder . '/cover') && !mkdir($folder . '/cover')) {
			$proceed = false;
			$this -> log('galeries:0:1');
		}
		 
		if(!file_exists($folder . '/cover/thumbs/') && !mkdir($folder . '/cover/thumbs/')) {
			$proceed = false;
			$this -> log('galeries:0:1');
		}
		
		return $proceed;
	} 
	
	
	private function handleAlbum() {
		
		global $_baseUrl;
		
		if($this -> nav('action') != 'add' && $this -> nav('action') != 'edit')
			return $this -> allAlbums($this -> nav('galerie'));

		if(empty($_POST['formEntry'])) 
			return $this -> formAlbum();	
		
		$sql['idx_galerie'] = $_POST['albumIdxGalerie'];
		$sql['label'] = $_POST['albumLabel'];
		if(empty($_POST['albumLabel']))
			return $this -> formAlbum(true);
		
		$sql['description'] = utf8_encode(html_entity_decode($_POST['albumDescription']));
		$sql['date'] = strtotime($_POST['albumDate']);	
		$sql['cover'] = !empty($_POST['cover']) ? implode(',', $_POST['cover']['files']) : '';
		
		if(!empty($_POST['link1']))
			$sql['link1'] = ';' . implode(';', $_POST['link1']) . ';';
			
		if(!empty($_POST['link2']))
			$sql['link2'] = ';' . implode(';', $_POST['link2']) . ';';

		$idAlbum = $this -> editAlbum($sql, $this -> get('album'));

		if($idAlbum != false) {
			$this -> makeAlbumCover($_POST['albumIdxGalerie'], $idAlbum, $_POST['cover']);
			Instance::redirect(Instance::url(array('action' => 'edit', 'mode' => 'album', 'album' => $idAlbum)));
			
		}
		
		return $this -> formAlbum();
	}
	
	public function makeAlbumCover($galerie, $album, $cover) {
		
		$picture = new FieldPicture();//$galerie, $album, $cover);
	
		$picture -> setImgHeight($this -> params['thumb_height']['value']);
		$picture -> setImgWidth($this -> params['thumb_width']['value']);

		$picture -> setThbHeight($this -> params['thumb_height']['value']);
		$picture -> setThbWidth($this -> params['thumb_width']['value']);

		$picture -> setThb(true);
		
		$picture -> setImgFolder($_baseUrl . $this -> getFolder($galerie, $album) . '/cover/');
		$picture -> setThbFolder($_baseUrl . $this -> getFolder($galerie, $album) . '/cover/thumbs/');
		$picture -> treat($cover);
	}
	
	
	private function formAlbum($errorLabel = false) {

		$tList = array();
		$tGaleries = array();
		
		$strSql = 'SELECT * FROM `' . Sql::buildTable($this -> params['t_albums']['value']) . '` ORDER BY `order`';
		if($resSql = Sql::query($strSql))
			while($dataSql = mysql_fetch_assoc($resSql))
				$tList[$dataSql['order']] = $dataSql['label'];	

		$tGaleries[0] = 'Aucune galerie';
		$strSql = 'SELECT * FROM `' . Sql::buildTable($this -> params['t_galeries']['value']) . '` ORDER BY `order`';
		if($resSql = Sql::query($strSql))
			while($dataSql = mysql_fetch_assoc($resSql))
				$tGaleries[$dataSql['id']] = $dataSql['label'];	
		
		$Form = new Form();
		$Form -> addLang('', '');
		$Form -> setUrl('');
		$Form -> setClass('DataForm');

	
		$tAlbum = $this -> get('album');
		if(empty($tAlbum)) {
			$tAlbum = array('label' => '', 'idx_galerie' => $this -> nav('galerie'), 'description' => '', 'date' => time(), 'order' => '');	
			for($i = 1; $i < 3; $i++) 
				$tAlbum['link' . $i] = '';
		}
		
		
		$tList['last'] = 'Placer en dernier';
	
		$Label = $Form -> addField('label', '', false);
		if($errorLabel)
			$Label -> setError("Vous devez remplir le nom de l'album");
			
		$Label -> setTitle('Label');
		$Label -> setField('<input type="text" name="albumLabel" value="' . $tAlbum['label'] . '" />');
		
		$IdxGalerie = $Form -> addField('idx_galerie', '', false);
		$IdxGalerie -> setTitle('Galerie');
		$fieldIdxGalerie = new FieldEnum();
		$fieldIdxGalerie -> setName('albumIdxGalerie');
		$fieldIdxGalerie -> setList(array('' => $tGaleries));
		$IdxGalerie -> setField($fieldIdxGalerie -> showField($tAlbum['idx_galerie'], false));
		
		$Description = $Form -> addField('description', '', false);
		$Description -> setTitle('Description');
		$Textarea = new FieldText();
		$Textarea -> setName('albumDescription');		
		$Textarea -> setMode('Wysiwyg');

		$Description -> setField($Textarea -> showField($tAlbum['description']));
		
		$Date = $Form -> addField('date', '', false);
		$Date -> setTitle('Date');
		$Date -> setField('<input type="text" name="albumDate" class="Date" value="' . FormDate($tAlbum['date']) . '" />');

		$Order = $Form -> addField('order', '', false);
		$Order -> setTitle('Place avant');
		$fieldOrder = new FieldEnum();
		$fieldOrder -> setName('albumOrder');
		$fieldOrder -> setList(array('' => $tList));
		$Order -> setField($fieldOrder -> showField($tAlbum['order'], false));
		
		//print_r($this -> params);
		
		for($i = 1; $i < 3; $i++) {
			if(!empty($this -> params['alb_link' . $i . '_field']['value'])) {
				$field = new FieldIdx();
				$field -> setIdxField($this -> params['alb_link' . $i .'_field']['value']);
				$field -> setName('link' . $i);
				$field -> setMultiple(true);
				$fieldForm = $Form -> addField('link' . $i, '', false);
				$fieldForm -> setTitle($this -> params['alb_link' . $i . '_text']['value']);
				$fieldForm -> setField($field -> showField($tAlbum['link' . $i]));
			}
		}

		if($this -> nav('album')) {
			
			$field = new FieldPicture();
			$field -> setName('cover');
			$field -> setImgFolder($this -> getFolder($tAlbum['idx_galerie'], $tAlbum['id']) . 'cover/');
			$field -> setUrlFormat('./plugins/galeries/get_file.php?file=<id>&album=' . $tAlbum['id'] . '&cover');
			$field -> setNbFiles(1);
			$field -> setThb(false);
			$fieldForm = $Form -> addField('cover', '', false);
			$fieldForm -> setTitle('Couverture de l\'album');
			
			$fieldForm -> setField($field -> showField($tAlbum['cover']));

		
			$field = new FieldPicture();
			$field -> setName('images');
			$field -> setImgFolder($this -> getFolder());
			$field -> setNbFiles(1);
			$field -> setThb(false);
			$fieldForm = $Form -> addField('imgs', '', false);
			$fieldForm -> setTitle('Images de l\'album');
			$fieldForm -> setField($field -> showField(''));

			$Album = $this -> get('album', $this -> nav('album'));
			Instance::getInstance('Page') -> addNavigation('Editer l\'album ' . $Album['label']);
			$strHtml = $Form -> display(true);
			$strHtml .= $this -> handleImages();
			
		} else {
			Instance::getInstance('Page') -> addNavigation('Ajouter un album');
			$strHtml = $Form -> display(true);
		}		
		return $strHtml;
	}

	private function allAlbums() {
		

		$galerie = intval($this -> nav('galerie'));
		$strSql = 'SELECT * FROM `' . Sql::buildTable($this -> params['t_albums']['value']) . '` WHERE `idx_galerie` = ' . $galerie . ' ORDER BY `order`';
		
		Instance::getInstance('Page') -> addNavigation('Liste des albums');
		$strHtml = NULL;
		$strHtml .=  Instance::getInstance('Message') -> display();

		/**
		 * Administrer la galerie
		 */
		//$strHtml .= '<h2>Administrer la galerie</h2>';

		$EditGal = new Button('Edit', 'Editer la galerie', true);
		$EditGal -> setUrl(array('action' => 'edit', 'mode' => 'galerie', 'album' => false, 'galerie' => $galerie));

		$RemoveGal = new Button('Remove', 'Supprimer la galerie', true);
		$RemoveGal -> setOptions(array(
			'Selectable' => true,
			'Disablable' => true,
			'Validable' => true,
			'class' => 'DirectGalerieRemove',
			'rel' => $galerie
		));

		
		/**
		 * Administrer les albums
		 * 
		 */
	//	$strHtml .= '<h2>Liste des albums</h2>';

		$Add = new Button('Add', 'Ajouter', true);
		$Add -> setUrl(array('action' => 'add', 'mode' => 'album', 'galerie' => $galerie, 'album' => NULL));
		
/*
		$Images = new Button('Defaut', 'Images', true);
		$Images -> setOptions(array(
			'Selectable' => true,
			'Disablable' => true,
			'Class' => 'Images',
			'Rel' => 'image'
		));
*/
		$Remove = new Button('Remove', 'Supprimer', true);
		$Remove -> setOptions(array(
			'Selectable' => true,
			'Disablable' => true,
			'Validable' => true,
			'Rel' => 'album'
		));
		
		
		Instance::getInstance('Page') -> addButton($Add);
		//Instance::getInstance('Page') -> addButton($Edit);
	//	Instance::getInstance('Page') -> addButton($Images);
		Instance::getInstance('Page') -> addButton($Remove);
		Instance::getInstance('Page') -> addButton($EditGal);
		Instance::getInstance('Page') -> addButton($RemoveGal);
		//$strHtml .= '<ul class="Buttons Actions">' . $EditGal -> display() . $RemoveGal -> display() . $Add -> display() . $Edit -> display() . $Remove -> display() . $Images -> display() . '</ul>';
		
		$strTable = '
		<table cellpadding="0" cellspacing="0" class="Data Entries">
			<tr>
				<th>Label</th>
				<th>Description</th>
				<th>Date</th>
			</tr>		
		';
		
		if($resSql = Sql::query($strSql)) {
			while($dataSql = mysql_fetch_assoc($resSql)) {
				$strTable .= '
				<tr rel="' . $dataSql['id'] . '">
					<td class="FirstCol">' . $dataSql['label'] . '</td>
					<td>' . $dataSql['description'] . '</td>
					<td>' . FieldDate::display($dataSql['date']) . '</td>
				</tr>
				';
			}
		}

		if(mysql_num_rows($resSql) == 0)
			$strTable .= '<tr><td colspan="3" class="FirstCol Error">Aucun album dans cette galerie</td></tr>';
		
		$strTable .= '
		</table>';
		
		$strHtml .=  $strTable;
		return $strHtml;
	}


	private function handleImages() {

	
		$strHtml = $this -> showImages($galerie, $album);
		
		return $strHtml;
	}
	
	private function showImages($galerie, $album) {
	
		$Delete = new Button('Remove', 'Supprimer');
		$Delete -> setOptions(array('rel' => 'image', 'Selectable' => true, 'Validable' => true));	
		
		$strHtml = '<h1>Liste des images dans l\'album</h1>
		
		<ul class="Buttons Actions">
		' . $Delete -> display() . '
		</ul>
			
		<div id="ListImages">';

		$album = $this -> get("album", $album);
		$folder = $this -> getFolder($album['idx_galerie'], $album['id']);
		
		if(!file_exists($folder)) {
			$this -> createFolder($folder);
		}
		
		if($dir = opendir($folder))
			while($file = readdir($dir)) {
				if(!is_dir($folder . '/' . $file) && !preg_match('!\.props$!', $file))
					$strHtml .= $this -> displayImage($galerie, $album['id'], $file);
			}
		
		
		$strHtml .= '
		<div class="Spacer"></div>
		</div>';
		
		
		$Back = new Button('Back', 'Retour', true);
		$Back -> setUrl(array('action' => 'show', 'mode' => 'album', 'album' => NULL));
		$strHtml .= '<div class="Spacer"></div><ul class="Actions Buttons">' . $Back -> display() . '</ul>';
		return $strHtml;
	}
	
	
	private function displayImage($galerie, $album, $file) {
		
		return '
		<div class="Image Galerie" rel="'. $file . '">
			<!--<a href="./plugins/galeries/get_file.php?file=' . $file . '&album=' . $album . '&galerie=' . $galerie . '" rel="box">-->
				<img src="./plugins/galeries/get_file.php?file=' . $file . '&album=' . $album . '&galerie=' . $galerie . '&thb" />
			<!--</a>-->
		</div>
		';
	}
	
	public function fb() {
		
		$Sync = new Button('List', 'Lister', true);
		$Sync -> setClass('Synchro');
		Instance::getInstance("Page") -> addNavigation("Synchronisation Facebook");
		Instance::getInstance("Page") -> addButton($Sync);
		
		$strHtml .= '
		<p class="Message Grey">Cette page vous permet de synchroniser vos galeries photos entre le Pi-Easy et Facebook. <br />A chaque ajout d\'images dans un album Pi-Easy ou Facebook, vous pouvez resynchroniser vos albums via cette page. <br />Attention, la synchronistion ne détecte pas les images supprimées.</p>
		<p class="Message Grey">Pour choisir les albums à synchroniser. Appuyez sur le bouton Lister</p>
		<div id="FBSynch"></div>';
		
		return $strHtml;
	}
	
	public function savePicture($uploaded, $album, $props = array(), $mode = 'ressource') {
	
		global $_baseUrl;
		$dbalbum = $this -> get('album', $album);
		$dest = $this -> getFolder($dbalbum['idx_galerie'], $dbalbum['id']);
		
		//echo $dest;
		if(is_dir($dest)) {
		
			$destpath = $dest . $uploaded;

			if($mode !== 'ressource') {
				$fPath = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_TEMP . $uploaded;
				$image = new ImageHandler();
				$image -> setImageAsFile($fPath);
				$image -> process($this -> params['image_width']['value'], $this -> params['image_height']['value'], $dest, $uploaded);
			} else {
				$flename = uniqid();
				$destpath = $dest . $flename;
				$image = new ImageHandler();
				$image -> setImageAsString($uploaded);
				$image -> process($this -> params['image_width']['value'], $this -> params['image_height']['value'], $dest, $flename);
			}

			$infos = new InfosFile($destpath);
			$infos -> setProp('path', $destpath);
			foreach($props as $k => $v)
				$infos -> setProp($k, $v);
			
			$infos -> save();
			$image -> process($this -> params['thumb_width']['value'], $this -> params['thumb_height']['value'], $dest . 'thumbs/', $uploaded);
		}		
	}
}

?>