<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldFile extends Field {
	
	private $fleFolder;
	private $nbFiles = 3;
	
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


	public function __construct($t_Data) {
		
		global $_baseUrl;		
		parent::__construct($t_Data);

		$this -> nbFiles = !empty($t_Data['nb_files']) ? $t_Data['nb_files'] : 1;

		$Module = $this -> getModule();		

		if(!empty($Module)) {
			$base = $Module -> getFolder();
			$this -> fleFolder = 	$base . $this -> getName() . '_fle/';
		} else {
			$base = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_MEDIAS;
			$this -> fleFolder = $base;
		}
/*
		$this -> modFields = array();
		$this -> modFields['files'] = 'VARCHAR( 250 ) NOT NULL';
		$this -> modFields['text'] = 'VARCHAR( 250 ) NOT NULL';
*/
	}	
	
	
	public function getFolder() { return $this -> fleFolder; }

	public function check($value) {
	
		return false;
	 	if($this -> isRequired() && !file_exists($this -> fleFolder . $value['current']))
			return false;
		return true;
	}
	
	
	public function treat($value) {
		global $_baseUrl;
	
		if(!empty($value) && is_array($value)) {

			foreach($value['files'] as $key => $file) {
				
				$fname = $value['name'][$key];
			
				if($file == NULL)
					continue;
					
				rename($_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_TEMP . $file, $this -> fleFolder . $file);
				$Infos = new InfosFile($this -> fleFolder . $file);
				
				$Infos -> setProp('name', $fname);
				$Infos -> setProp('ext', $value['ext'][$key]);
				$Infos -> setProp('title', $value['title'][$key]);
				$Infos -> setProp('description', $value['desc'][$key]);
			
				$Infos -> save();
			}
			//echo implode(',', $value['files']);
			return implode(',', $value['files']);
		}
		
		return NULL;
	}
	
	public function display($value) {
		global $_baseUrl;
		$value = explode(',', $value);
	
		$imgs = NULL;
		foreach($value as $fle) {
			
			if($fle != NULL && file_exists($this -> fleFolder . $fle)) {
				
				$infos = new InfosFile($this -> fleFolder . $fle);
				$mime = $infos -> getMime();
				$pathFileMain = $_baseUrl . 'get_file.php?file=' . $fle . '&field=' . $this -> getId();
				$pathFileThumb = $_baseUrl . $this -> getLogo($mime);
				
				$altText = $fle;
				$imgs .= '
				<a href="' . $pathFileMain . '">
					<img src="' . $pathFileThumb . '" alt="' . $infos -> getProp('name') . '" class="Image" />
					' . (($infos -> getProp('title') != NULL) ? $infos -> getProp('title') : $infos -> getProp('name')) . '.' . $infos -> getProp('ext') . '
				</a>
				';
			}
		}
		
		$html = '
		<div class="File Display">
			<div class="File">
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
		$class[] = 'File';
		$class = implode(' ' , $class) . ' ' . implode(' ', $this -> _class);
		
		$fileExists = is_file($this -> fleFolder . $value);
		$fieldName = $this -> formName();
		$value = explode(',', $value);

		$tImgs = array();
		foreach($value as $key => $fle) {
			
			if($fle == NULL)
				continue;
				
			$fPath = $this -> fleFolder . $fle;	
			$infos = new InfosFile($fPath);
			$file = $infos -> getProps($infos);
		
			$mime = $infos -> getMime();
		
			if(file_exists($fPath) && is_file($fPath))
				$tFles[] = array(
					 'file' => $fle,
					 'filename' => $file['name'], 
					 'size' => FormatWeight(filesize($fPath)), 
					 'uploaded' => FormatDate(filemtime($fPath)), 
					 'logo' => $this -> getLogo($mime),
					 'text' => $file['name'],
					 'ext' => $file['ext'],
					 'desc' => $file['description'],
					 'title' => $file['title']
				);
		}
		
		$UploadZone = new UploadZone($fieldName, $this -> id);
		$UploadZone -> authorize('*/*');
		$UploadZone -> allowMedia(true);
		$UploadZone -> setNbFiles($this -> nbFiles);
		
		$Delete = new Button('Remove', 'Supprimer');
		$Add = new Button('Add', 'Ajouter');
		$Edit = new Button('Edit', 'Editer');
		$Edit -> setOptions(array('Selectable' => true, 'Validable' => true));
		
		$strFiles = NULL;

		for($i = 0; $i < $this -> nbFiles; $i++) {

			$img = array(
				'file' => '',
				'filename' => '',
				'size' => '',
				'uploaded' => '',
				'logo' => '',
				'text' => '',
				'ext' => '',
				'desc' => '',
				'title' => ''
			);
			
			
			$imgClass = NULL;
			if(isset($tFles[$i]))
				$img = array_merge($img, $tFles[$i]);			
			else
				$imgClass = ' Hidden';

			$pathFileMain = 'get_file.php?file=' . $img['file'] . '&field=' . $this -> getId();
			
			$strFiles .= '
			<div class="CurrentFile' . $imgClass . '">
				<div class="Content">
					<a href="' . $pathFileMain . '" />
						<img src="' . $img['logo'] . '" />
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
						<input rel="filefile" type="hidden" name="' . $fieldName . '[files][]" value="' . $img['file'] . '" />
					
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
	
		$strFiles .= '<div class="Spacer"></div><ul class="Buttons Actions Hidden">' . $Add -> display() . '</ul><div class="Spacer"></div>';

	/*	$Manager = new PublicFileManagerDisplayer();
		$Manager -> setRoot(FTP_PUBLIC_FOLDER);
		$Manager -> setMode('files');
		$Manager -> setLinker($this -> getId());
		$manager = '<a class="showFileManager">Afficher le File Manager</a>' . $Manager -> displayContent(true, true);
*/

		return sprintf(
				self::fieldModel, 
				$class,
				$this -> getName(),
				$strFiles,
				$UploadZone -> display()
		);
	}
	

	public function deleteFile($file) {
		Filemanager::remove($this -> fleFolder . $file);
	}

	
	protected function handleDir($remove = false) {
		
		$folderFle = $this -> Module -> getFolder() . FormatFTPName($this -> name) . '_fle';
		
		if($remove == true) {
			FileManager::remove($folderFle);
			return true;
		}
		
		if($this -> oldName != NULL) {
			$folderFromFle = $this -> Module -> getFolder() . FormatFTPName($this -> oldName) . '_fle';
			FileManager::renameFolder($folderFromFle, $folderFle);
		} else {
			FileManager::createFolder($folderFle);
		}
	}
	
	public static function getLogo($mime) {
		
//		global $_baseUrl;
		$strPath = './' . FOLDER_DESIGN . 'images/';
		
		switch($mime) {
			
			case 'application/pdf':
				$strPath .= 'pdf.gif';
			break;
			case 'audio/mpeg':
				$strPath .= 'mp3.gif';
			break;
			/*
			case 'image/gif':
			case 'image/png':
			case 'image/jpeg':
				$strPath .= 'pic.png';
			break;
			*/
	/*		
			case 'text/html':
				$strPath .= 'html.png';
			break;
*/
			case 'text/plain':
				$strPath .= 'txt.gif';
			break;

			case 'application/zip':
				$strPath .= 'zip.gif';
			break;
			
			case 'application/msword':
			case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
				$strPath .= 'doc.gif';
			break;
			
			case 'application/vnd.ms-excel':
			case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
				$strPath .= 'xls.gif';
			break;
			
			default:
				$strPath .= 'file.gif';
			break;
		}
		
		return $strPath;
	}
	
	public function configFields() {
		
		extract($_POST);
		return array(
			'nb_files' => $fieldFilenbFiles

		);
	}
}

?>