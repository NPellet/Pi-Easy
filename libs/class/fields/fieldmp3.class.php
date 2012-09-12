<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldMp3 extends Field {
	
	private $fleFolder;
	private $nbFiles = 3;
	
	const fieldModel = '        
	<p>
		<div class="%s" rel="%s">
			<div class="Current">
				%s
			</div>
			<div class="New">
				%s
			</div>
		</div>
	</p>
	';


	public function __construct($t_Data) {
		
		global $_baseUrl;		
		parent::__construct($t_Data);

		$this -> fields['']			= 'VARCHAR( 250 ) NOT NULL';
		$this -> nbFiles			= 1;

		$Module = $this -> getModule();		
		$base = $this -> Module -> getFolder();
		$this -> fleFolder = $base . $this -> getName() . '_mp3/';
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
			$value = array_splice($value, 0, $this -> nbFiles);
			foreach($value as $mp3)
				@rename($_baseUrl . FOLDER_UPLOAD_TEMP . $mp3, $this -> fleFolder . $mp3);
				
			return implode(',', $value);
		}
		
		return NULL;
	}
	
	public function display($value) {

		$value = explode(',', $value);
		$imgs = NULL;
		foreach($value as $fle) {

			$pathFileMain = 'get_file.php?file=' . $fle . '&field=' . $this -> getId();
			$pathFileThumb = '
			<div class="File">
				<a href="get_file.php?file=' . $fle . '&field=' . $this -> getId() . '">
					<img src="' . FieldFile::getLogo(FileManager::getMime($this -> fleFolder . $fle)) . '" alt="" class="File" />
				</a>
			</div>';
			
			$imgs .= $pathFileThumb;
		}
		
		$html = '
		<div class="File Display">
			' . $imgs . '
		</div>';
		
		return $html;
	}

	public function showField($value, $error = false) {
	
		$class = array();
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'MP3';
		$class = implode(' ' , $class);
		
		$fileExists = is_file($this -> fleFolder . $value);
		$fieldName = $this -> formName();
		
		$value = explode(',', $value);
		$tFles = array();
		foreach($value as $fle) {
			$fPath = $this -> fleFolder . $fle;
			
			if(file_exists($fPath) && is_file($fPath)) {
				$mp3 = new Mp3($fPath);
				$tFles[] = array(
					 'fle' => $fle, 
					 'size' => FormatWeight(filesize($fPath)), 
					 'uploaded' => FormatDate(filemtime($fPath)),
					 'mp3' => $mp3
				);
				
			}
		}
		
		$UploadZone = new UploadZone($fieldName, $this -> id);
		$UploadZone -> authorize('audio/mpeg');
		$UploadZone -> setNbFiles($this -> nbFiles);
		
		$Delete = new Button('remove', 'Supprimer');
		$strFiles = NULL;
		for($i = 0; $i < $this -> nbFiles; $i++) {

			if(!isset($tFles[$i])) {
				$imgClass = ' Hidden';	
				$fle = array('file' => '', 'size' => '', 'uploaded' => '', 'mp3' => new Mp3());
			} else {
				$imgClass = NULL;
				$fle = $tFles[$i];	
			}
			
			$mp3 = $fle['mp3'];
			
			$strFiles .= '
			<div class="CurrentFile' . $imgClass . '">
				<div class="Id3Tags">
					<div class="Form">
					<input type="hidden" value="' . $tFles[$i]['fle'] . '" name="id3_fleName" />
					<input type="hidden" value="' . $this -> getId() . '" name="field" />
					<h3>Tags MP3</h3>
					<p>
						<label>Titre</label><input type="text" name="id3_title" value="' . $mp3 -> getTag('title') . '" />
					</p>
					<p>
						<label>Artiste</label><input type="text" name="id3_artist" value="' . $mp3 -> getTag('artist') . '" />
					</p>
					<p>
						<label>Album</label><input type="text" name="id3_album" value="' . $mp3 -> getTag('album') . '" />
					</p>
					<p>
						<label>Année</label><input type="text" name="id3_year" value="' . $mp3 -> getTag('year') . '" />
					</p>
					<p>
						<label>Genre</label>
						<select name="id3_genre">' . Html::buildList($mp3 -> getGenre(), $mp3 -> getGenre($mp3 -> getTag('genre'))) . '</select>
					</p>
					<p>
						<label></label>
						<input type="submit" value="Valider" name="formId3" />
					</p>
					</div>
				</div>
				<div class="Mp3Player">get_file.php?file=' . $this -> getId() .'-' . $fle['fle'] . '</div>
				<div class="UploadedTime">' . $fle['uploaded'] . '</div>
				<div class="UploadedSize">' . $fle['size'] . '</div>
				' . $Delete -> display() . '
				<input type="hidden" class="mp3Name" name="' . $fieldName . '[]" value="' . $img['fle'] . '" />
			</div>
			';
		}
		
		$strFiles .= '<div class="Spacer"></div>';

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
		
		$folderFle = $this -> Module -> getFolder() . FormatFTPName($this -> name) . '_mp3';
		
		if($remove == true) {
			FileManager::remove($folderFle);
			return true;
		}
		
		if($this -> oldName != NULL) {
			$folderFromFle = $this -> Module -> getFolder() . FormatFTPName($this -> oldName) . '_mp3';
			FileManager::renameFolder($folderFromFle, $folderFle);
		} else {
			FileManager::createFolder($folderFle);
		}
	}
}

?>