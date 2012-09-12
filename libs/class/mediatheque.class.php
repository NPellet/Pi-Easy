<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Mediatheque extends Instance {

	public function __construct() {
		$this -> folder = DATA_ROOT_REL . FOLDER_UPLOAD_MEDIAS;
		$this -> getInstance('Page') -> addNavigation('Médiathèque');
	}

	public function processLevel($array, $level, $root) {
		
		$strHtml = '<option value="' . $root . '/">(racine)</option>';
		foreach($array as $key => $content) {
			
			if($key == 'modules')
				continue;
				
			$spaces = NULL;
			for($i = 0; $i < $level; $i++) 
				$spaces .= '&nbsp;&nbsp;&nbsp;';
			$strHtml .= '<option value="' . $root . '/' . $key . '/">' . $spaces . $key . '</option>';


			if(is_array($content))
			
				$strHtml .= $this -> processLevel($content, $level + 1, $root . '/' . $key);	
			/*else {
				$spaces = NULL;
				for($i = 0; $i < $level; $i++) 
					$spaces .= '   ';
				$strHtml .= '<option value="' . $root . '/' . $key . '">' . $spaces . $content . '</option>';
			}*/
		}
		
		return $strHtml;
	}
	
	public function form() {
		global $_baseUrl;
		$zone = new UploadZone('Media', 'Media');
		$zone -> authorize('all');
		$this -> getInstance('Page') -> addNavigation('Ajouter des fichiers');
		
		
		$content = FileManager::scandir($_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_MEDIAS, 'dir');
		$optsFolders = $this -> processLevel($content, 0, '.');
	
		return '
		<p>Uploader les fichiers dans le dossier : <select name="rootFolder">' . $optsFolders . '</select></p>
		<div id="Mediatheque">' . $zone -> display() . '</div><div id="ListFiles"></div>';
	}
	
	public function showList() {
		
		$this -> getInstance('Page') -> addNavigation('Visionner');		
		if(!@$type)
			$type = 'document';

		$strHtml = '		
		<div class="Tree">
		
			<div class="SelectMediaType">
				<input type="checkbox" name="image" checked="checked" /> Images
				<input type="checkbox" name="audio"  checked="checked" /> Audio
				<input type="checkbox" name="video"  checked="checked" /> Vidéos
				<input type="checkbox" name="archive" checked="checked" /> Archives
				<input type="checkbox" name="document" checked="checked" /> Documents
			</div>
			<h3>Dossiers disponibles</h3>			
			<div class="Filetree Media ' . $type . '"></div>
			<div class="InfosFile"></div>
		</div>';
			/*
		foreach($tFiles as $file) {
			
			$fileRead = FileManager::readFile($this -> folder . $file);
		
			if($type == 'image') 
				$content = '<a href="get_file.php?file=' . $file . '"><img src="get_file.php?file=' . $file . '" /></a>';
			else {
				$logo = FieldFile::getLogo($fileRead['mime']);	
				$content = '<a href="get_file.php?file=' . $file . '"><img src="' . $logo . '"></a>';
			}
			
			
			$strHtml .= '
			<tr>
				<td class="FirstCol">
					' . $content . ' 
				</td>
				<td>
					' . $fileRead['name'] . '
				</td>
				<td>
					' . FormatWeight($fileRead['size']) . '
				</td>
				<td>
					' . FormatDate($fileRead['uploaded']) . '
				</td>
			</tr>
			';
		}
		
		if(count($tFiles) == 0) {
			$strHtml .= '
			<tr>
				<td colspan="4" class="FirstCol">
					Aucun fichier ici
				</td>
			</tr>
			';
		}
		$strHtml .= '</table>';
		*/
		return $strHtml;
	}	
}

?>