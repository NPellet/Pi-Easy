<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldPicture extends Field {
	
	protected $imgHeight, $imgWidth, $imgThumb, $thumbHeight, $thumbWidth;
	private $imgFolder, $adminFolder, $thumbFolder;
	private $nbFiles;
	private $urlFormat = 'get_file.php?file=<id>&field=<fieldid>';
	
	const FIELD_IMG_MAX_HEIGHT 	= 'img_maxi_h'; 
	const FIELD_IMG_MAX_WIDTH 	= 'img_maxi_w';
	const FIELD_IMG_THUMB 		= 'img_miniature';
	const FIELD_THB_HEIGHT 		= 'img_min_h'; 
	const FIELD_THB_WIDTH 		= 'img_min_w';
	const NB_FILES 				= 'nb_files';
	
	const fieldModel = '        
	
	<div class="%s" rel="%s">
		<div class="Current">
			%s
		</div>
		<div class="New">
			%s
		</div>
	</div>
	
	';

	public function __construct($t_Data = NULL) {
		
		global $_baseUrl;
		
		if(empty($t_Data))
			return;
		
		$this -> imgHeight 			= !empty($t_Data[self::FIELD_IMG_MAX_HEIGHT]) 	? $t_Data[self::FIELD_IMG_MAX_HEIGHT] : FIELD_IMG_MAXWIDTH;
		$this -> imgWidth 			= !empty($t_Data[self::FIELD_IMG_MAX_WIDTH]) 	? $t_Data[self::FIELD_IMG_MAX_WIDTH] : FIELD_IMG_MAXHEIGHT;		
		$this -> imgThumb 			= !empty($t_Data[self::FIELD_IMG_THUMB]) 		? $t_Data[self::FIELD_IMG_THUMB] : false;
		$this -> thumbHeight	 	= !empty($t_Data[self::FIELD_THB_HEIGHT]) 		? $t_Data[self::FIELD_THB_HEIGHT] : NULL;
		$this -> thumbWidth 		= !empty($t_Data[self::FIELD_THB_WIDTH]) 		? $t_Data[self::FIELD_THB_WIDTH] : NULL;
		$this -> nbFiles			= !empty($t_Data[self::NB_FILES])		 		? $t_Data[self::NB_FILES] : 1;
		$this -> resImage 			= new Imagehandler();
		
		parent::__construct($t_Data);

		$this -> fields['']			= 'VARCHAR( 250 ) NOT NULL';

		$Module = $this -> getModule();
		
		if(!empty($Module)) {
			$base = $Module -> getFolder();
			$this -> imgFolder = 	$base . $this -> getName() . '_img/';
			$this -> thumbFolder = 	$base . $this -> getName() . '_thb/';
		} else {
			$base = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_MEDIAS;
			$this -> imgFolder = $base;
		}

		
/*		$this -> imgFolder = DATA_ROOT_REL . FOLDER_UPLOAD_MEDIAS;
		$this -> thumbFolder = DATA_ROOT_REL . FOLDER_UPLOAD_MEDIAS . '/thumbs';
	*/	
	/* 10 juillet 2011 : Les textes sont maintenant enregistrés dans le fichier .props */
	//	$this -> modFields = array();
	//	$this -> modFields['files'] = 'VARCHAR( 250 ) NOT NULL';
	//	$this -> modFields['text'] = 'VARCHAR( 250 ) NOT NULL';
	}
	
	public function getImgHeight() { return $this -> imgHeight; }
	public function getImgWidth() { return $this -> imgWidth; }
	public function getThb() { return $this -> imgThumb; }
	public function getThbHeight() { return $this -> thbHeight; }
	public function getThbWidth() { return $this -> thbWidth; }
	
	public function getImgFolder() { return $this -> imgFolder; }
	public function getThbFolder() { return $this -> thumbFolder; }
	
	public function setImgHeight($intHeight) { $this -> imgHeight = intval($intHeight); }
	public function setImgWidth($intWidth) { $this -> imgWidth = intval($intWidth); }
	public function setThb($blnThb) { $this -> imgThumb = (bool) $blnThb; }
	public function setThbWidth($thbWidth) { $this -> thumbWidth = intval($thbWidth); }
	public function setThbHeight($thbHeight) { $this -> thumbHeight = intval($thbHeight); }
	public function setNbFiles($nbFiles) { $this -> nbFiles = $nbFiles; }
	
	public function setImgFolder($folder) { $this -> imgFolder = $folder; }
	public function setThbFolder($folder) { $this -> thumbFolder = $folder; }

	public function setUrlFormat($format) { $this -> urlFormat = $format; } 
	
	
	public function check($value) {
	
		return false;
	 	if($this -> isRequired() && !file_exists($this -> imgFolder . $value['current']))
			return false;
		return true;
	}
	
	public function treat($value) {
		
		global $_baseUrl;
		
		if(!empty($value) && is_array($value)) {
			$value['files'] = array_slice($value['files'], 0, $this -> nbFiles);
			
			
			foreach($value['files'] as $key => $image) {
				
				$fname = $value['name'][$key];
				$fext = $value['ext'][$key];
				if($image == NULL)
					continue;
				
				if(file_exists($_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_TEMP . $image)) {
					
					rename($_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_TEMP . $image, $this -> imgFolder . $image);	
					chmod($this -> imgFolder . $image, 0777);
					FileManager::emptyTempFolder();

					$Infos = new InfosFile($this -> imgFolder . $image);
					$Infos -> setProp('name', $fname);
					$Infos -> setProp('ext', $value['ext'][$key]);
					$Infos -> setProp('title', $value['title'][$key]);
					$Infos -> setProp('description', $value['desc'][$key]);
					$Infos -> setMime();
					$Infos -> save();
					$this -> createThumbImage($this -> imgFolder . $image, $image, array($fname, $fext));			
				}
			}
			
			return implode(',', $value['files']);
		}
		
		return NULL;
	}
	
	public function display($value) {
		
		global $_baseUrl;
		$value = explode(',', $value);

		$imgs = NULL;
		foreach($value as $key => $img) {
			
			if($img != NULL) {
				$pathFileMain = $_baseUrl . str_replace(array(
					'<id>', '<fieldid>'				
				), array(
					$img, $this -> getId()
				), $this -> urlFormat);	

				if(!file_exists($this -> imgFolder . $img))
					continue;
					
				$pathFileThumb = $pathFileMain . ($this -> imgThumb ? '&thb' : '');
				$altText = $img;
				$imgs .= '<a href="' . $pathFileMain . '" rel="box"><img src="' . $pathFileThumb . '" alt="" class="Image" /></a>';
			}
		}
		
		$html = '
		<div class="Image Display">
			<div class="Image">
				' . $imgs . '
			</div>
		</div>';
		return $html;
	}

	public function showField($value, $error = false) {
		
		$class = array();
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'Picture';
		$class = implode(' ' , $class) . ' ' . implode(' ', $this -> _class);
		
		$fileExists = is_file($this -> imgFolder . $value);
		$fieldName = $this -> formName();
		$value = explode(',', $value);

		$tImgs = array();
		foreach($value as $key => $img) {
			
			if($img == NULL)
				continue;
				
			$fPath = $this -> imgFolder . $img;
			$fPathThb = $this -> thumbFolder . $img;
			
			$infos = new InfosFile($fPath);
			$file = $infos -> getProps($infos);
			
			if(file_exists($fPath) && is_file($fPath))
				$tImgs[] = array(
					 'img' => $img, 
					 'filepath' => $fPath,
					 'filepaththumb' => $fPathThumb,
					 'filename' => $file['name'], 
					 'size' => FormatWeight(filesize($fPath)), 
					 'uploaded' => FormatDate(filemtime($fPath)), 
					 'thb' => file_exists($fPathThb),
					 'desc' => $file['description'],
					 'title' => $file['title'],
					 'ext' => $file['ext']
				);
		}
		
		$UploadZone = new UploadZone($fieldName, $this -> id);
		$UploadZone -> authorize('image/*');
		$UploadZone -> setNbFiles($this -> nbFiles);
		$UploadZone -> allowMedia(true);
		
		$Delete = new Button('Remove', 'Supprimer');
		$Edit = new Button('Edit', 'Editer');
		$Edit -> setOptions(array('Selectable' => true, 'Validable' => true));
		
		$Add = new Button('Add', 'Ajouter');
		
		$strImages = NULL;
		
		
		for($i = 0; $i < $this -> nbFiles; $i++) {

			$img = array('img' => '', 'size' => '', 'filename' => '', 'uploaded' => '', 'thb' => '', 'ext' => '', 'name' => '', 'title' => '', 'desc' => '');
			
			if(isset($tImgs[$i])) {
				$img = array_merge($img, $tImgs[$i]);
				$imgClass = NULL;
			} else
				$imgClass = ' Hidden';
			
			
			$pathFileMain = $pathFileMain = $_baseUrl . str_replace(array(
					'<id>', '<fieldid>'
				), array(
					$img['img'], $this -> getId()
				), $this -> urlFormat);	
			
			
			
			$pathFileThumb = $pathFileMain . ($this -> imgThumb ? '&thb' : '');

			$strImages .= '
			<div class="CurrentFile' . $imgClass . '">
				<div class="Content">
					<a href="' . $pathFileMain . '" rel="box" />
						<img src="' . (file_exists($img['filepaththumb']) ? $pathFileThumb : $pathFileMain) . '" />
					</a>
				</div>
				
				<div class="UploadedInfos">
					<div class="Content">
					
						<label>Nom :</label><span rel="filename">' . $img['title'] . '</span>
						<div class="Spacer"></div>
						<label>Poids :</label><span rel="fileweight">' . $img['size'] . '</span>
						<div class="Spacer"></div>
						
						<input rel="fileext" type="hidden" name="' . $fieldName . '[ext][]" value="' . $img['ext'] . '" />
						<input rel="filename" type="hidden" name="' . $fieldName . '[name][]" value="' . $img['filename'] . '" />
						<input rel="filetitle" type="hidden" name="' . $fieldName . '[title][]" value="' . $img['title'] . '" />
						<input rel="filedesc" type="hidden" name="' . $fieldName . '[desc][]" value="' . $img['desc'] . '" />
						<input rel="filefile" type="hidden" name="' . $fieldName . '[files][]" value="' . $img['img'] . '" />
					
					</div>
					<div class="Spacer"></div>
					<div>
						<ul class="Actions Buttons">' . $Delete -> display() . '<li class="Spacer"></li>' . $Edit -> display() . '</ul>
					</div>
				</div>
				
				<div class="Spacer"></div>
				
				</div>
			';
		}
		
		$strImages .= '<div class="Spacer"></div><ul class="Buttons Actions Hidden">' . $Add -> display() . '</ul><div class="Spacer"></div>';


		return sprintf(
				self::fieldModel, 
				$class,
				$this -> getName(),
				$strImages,
				$UploadZone -> display()
		);
	}
	
	private function createThumbImage($image, $imagename, $name) {

		$imageManager = new ImageHandler();
		$imageManager -> setImageAsFile($image);

		if($this -> imgThumb) {
			$imageManager -> process($this -> thumbWidth, $this -> thumbHeight, $this -> thumbFolder, $imagename);
			/*
			$Infos = new InfosFile($this -> thumbFolder . $imagename);
			$Infos -> setPropsFromName($props[0]);
			$Infos -> setProp('title', $value['title'][$key]);
			$Infos -> setProp('description', $value['desc'][$key]);
			*/
		//	$Infos -> save();
		}
		
		return $image;
	}	

	public function deleteFile($image) {
		Filemanager::remove($this -> imgFolder . $image);
		Filemanager::remove($this -> thumbFolder . $image);
	}

	public function getCfg() {
		/* On crée le dossier */
		return array(
			self::FIELD_IMG_MAX_HEIGHT =>	$this -> imgHeight,
			self::FIELD_IMG_MAX_WIDTH =>	$this -> imgWidth,
			self::FIELD_IMG_THUMB =>		$this -> imgThumb,
			self::FIELD_THB_HEIGHT =>		$this -> thbHeight,
			self::FIELD_THB_WIDTH =>	 	$this -> thbWidth
		);
	}	
	
	protected function handleDir($remove = false) {
		
		$folderImg = $this -> Module -> getFolder() . FormatFTPName($this -> name) . '_img';
		$folderThb = $this -> Module -> getFolder() . FormatFTPName($this -> name) . '_thb';
		
		if($remove == true) {
			FileManager::remove($folderImg);
			FileManager::remove($folderThb);
			return true;
		}
		
		if($this -> oldName != NULL) {
			$folderFromImg = $this -> Module -> getFolder() . FormatFTPName($this -> oldName) . '_img';
			$folderFromThb = $this -> Module -> getFolder() . FormatFTPName($this -> oldName) . '_thb';
			FileManager::renameFolder($folderFromImg, $folderImg);		
			FileManager::renameFolder($folderFromThb, $folderThb);		
		} else {
			FileManager::createFolder($folderImg);
			FileManager::createFolder($folderThb);
		}
	}
	
	public function configFields() {
		
		extract($_POST);
		return array(
			self::FIELD_IMG_MAX_WIDTH => $fieldPictureImgWidth,
			self::FIELD_IMG_MAX_HEIGHT => $fieldPictureImgHeight,
			self::FIELD_IMG_THUMB => isset($fieldPictureThb) ? true : false,
			self::FIELD_THB_HEIGHT => $fieldPictureThbHeight,
			self::FIELD_THB_WIDTH => $fieldPictureThbWidth,
			self::NB_FILES => $fieldPicturenbFiles
		);
	}
}

?>