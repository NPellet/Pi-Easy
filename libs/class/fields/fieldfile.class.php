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

		$this -> nbFiles			= !empty($t_Data['nb_files']) ? $t_Data['nb_files'] : 1;

		$Module = $this -> getModule();		

		if(!empty($Module)) {
			$base = $Module -> getFolder();
			$this -> fleFolder = 	$base . $this -> getName() . '_fle/';
		} else {
			$base = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_MEDIAS;
			$this -> fleFolder = $base;
		}

		$this -> modFields = array();
		$this -> modFields['files'] = 'VARCHAR( 250 ) NOT NULL';
		$this -> modFields['text'] = 'VARCHAR( 250 ) NOT NULL';
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

		$texts = $value['text'];
	
		if(!empty($value) && is_array($value)) {
			$value['files'] = array_splice($value['files'], 0, $this -> nbFiles);

			foreach($value['files'] as $key => $file) {
				
				$fname = $value['filename'][$key];
				$fext = $value['ext'][$key];
				
				if($file == NULL)
					continue;
					
				rename($_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_TEMP . $file, $this -> fleFolder . $file);
				$Infos = new InfosFile($this -> fleFolder . $file);
				$Infos -> setPropsFromName($fname . '.' . $fext);
				$Infos -> save();
				
			}
			
			if($this -> oneField) 
				return implode(',', $value['files']) . ';' .  implode(',', $texts);
			else
				return array('files' => implode(',', $value['files']), 'text' => implode(',', $texts));
		}
		
		return NULL;
	}
	
	public function display($value) {
	
		if($this -> oneField) {
			$value = explode(';', $value);
			$value['text'] = $value[1];
			$value['files'] = $value[0];
		}
		
		$texts = explode(',', $value['text']);
		$value = explode(',', $value['files']);
	
		$imgs = NULL;
		foreach($value as $fle) {
			
			if($fle != NULL && file_exists($this -> fleFolder . $fle)) {
				
				$infos = new InfosFile($this -> fleFolder . $fle);
				$mime = $infos -> getMime();
				$pathFileMain = 'get_file.php?file=' . $fle . '&field=' . $this -> getId();
				$pathFileThumb = $this -> getLogo($mime);
				
				$altText = $fle;
				$imgs .= '
				<a href="' . $pathFileMain . '">
					<img src="' . $pathFileThumb . '" alt="' . $altText . '" class="Image" />
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
		$class = implode(' ' , $class);
		
		$fileExists = is_file($this -> fleFolder . $value);
		$fieldName = $this -> formName();
		
		if($this -> oneField) {
			$value = explode(';', $value);
			$value['text'] = @$value[1];
			$value['files'] = @$value[0];
		}
		
		$texts = explode(',', $value['text']);
		$value = explode(',', $value['files']);
			
		$tFles = array();
		foreach($value as $key => $fle) {
			
			$fileName = Filemanager::parse($texts[$key]);
			$fileExt = $fileName['extension'];
			$fileName = $fileName['basename'];
			
			$fPath = $this -> fleFolder . $fle;
			$infos = new InfosFile($fPath);
			$mime = $infos -> getMime(); 
			
			if(file_exists($fPath) && is_file($fPath))
				$tFles[] = array(
					 'file' => $fle, 
					 'size' => FormatWeight(filesize($fPath)), 
					 'uploaded' => FormatDate(filemtime($fPath)), 
					 'logo' => $this -> getLogo($mime),
					 'text' => $fileName,
					 'ext' => $fileExt
				);
		}
		
		$UploadZone = new UploadZone($fieldName, $this -> id);
		$UploadZone -> authorize('*/*');
		$UploadZone -> allowMedia(true);
		$UploadZone -> setNbFiles($this -> nbFiles);
		
		$Delete = new Button('Remove', 'Supprimer');
		$Add = new Button('Add', 'Ajouter');
		//$Add -> setClass('Hidden');
		
		$strFiles = NULL;

		for($i = 0; $i < $this -> nbFiles; $i++) {

			if(!isset($tFles[$i])) {
				$imgClass = ' Hidden';	
				$img = array('file' => '', 'size' => '', 'uploaded' => '', 'logo' => '', 'text' => '', 'ext' => '');
			} else {
				$imgClass = NULL;
				$img = $tFles[$i];	
			}


			$strFiles .= '
			<div class="CurrentFile' . $imgClass . '">
				<a href="get_file.php?file=' . $img['file'] . '&field=' . $this -> getId() . '" target="_blank" />
					<img src="' . $img['logo'] . '" />
				</a>
				<div class="UploadedTime">' . $img['uploaded'] . '</div>
				<div class="UploadedSize">' . $img['size'] . '</div>
				
				<div class="UploadedFilename">
					<label>Nom du fichier : </label>
					<input type="text" class="Field Text Filename" name="' . $fieldName . '[filename][]" value="' . $img['text'] . '" /> 
					<span class="Disabled Extension">
						<span>.' . $img['ext'] . '</span>
						<input type="hidden" name="' . $fieldName . '[ext][]" value="' . $img['ext'] . '" class="Fileext" />
					</span>
				</div>
		
				<div class="UploadedText">
					<label>Texte associé : </label>
					<input type="text" class="Field Text" name="' . $fieldName . '[text][]" value="' . $img['text'] . '" class="Filetext" /> 
				</div>
				
				<ul class="Buttons Actions">' . $Delete -> display() . '</ul>
				<input type="hidden" name="' . $fieldName . '[files][]" value="' . $img['file'] . '" />
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
			
			/*case 'image/gif':
			case 'image/png':
			case 'image/jpeg':
				$strPath .= 'pic.png';
			break;*/
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