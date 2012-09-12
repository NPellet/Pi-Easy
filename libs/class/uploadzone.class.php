<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class UploadZone extends Security {

	private $nbFiles = 0, $mode, $zip = false, $fields = array('dragndrop', 'standard');
	private $field = array();
	private $media, $FTP;
	
	public function __construct($name, $id) {
		if(!empty($name)) {
			$this -> field['name'] =  $name;	
			$this -> field['id'] =  $id;	
		} else
			$this -> field = array('name' => '', 'id' => '');
	}
	
	public function setNbFiles($nbFiles) {
		$this -> nbFiles = (int) $nbFiles;	
	}
	
	public function setZip($blnZip) {
		$this -> zip = (bool) $blnZip;	
	}
	
	public function authorize($toAuthorize) {
		$toAuthorize = strtolower($toAuthorize);
		$this -> authorize[] = $toAuthorize;
	}
	
	public function allowMedia($bln) { $this -> media = (bool) $bln; }
	public function allowFTP($bln) { $this -> FTP = (bool) $bln; }
	
	public function display() {
		
		if(count($this -> authorize) == 0)
			return false;
			
		$strHtml = NULL;
		
		$strHtml .= '<div class="UploadZone">';
		
		$strHtml .= '
		<h3>Sélection du fichier</h3>
		<div>
		<ul class="UploadMethods">
			<li class="HDD"><span></span>Du disque dur</li>
			<li class="DnD"><span></span>Glisser-Déposer</li>
			<li class="URL"><span></span>Adresse Internet</li>
			' . ($this -> media ? 
				 	'<li class="Media"><a href="' . FOLDER_LIBS_AJAX . 'rootfiletree.php" rel="box_filetree"><span></span>Médiathèque</a></li>'
				:	'') . '
			<div class="Spacer"></div>
		</ul>
		
		<div class="HDD Hidden">	
			<h3>Fichier sur votre disque dur : </h3>
			<input type="file" name="" ' . 
			($this -> nbFiles > 1 ? 'multiple="multiple"' : NULL) . ' />
		</div>';
		
	
		$strHtml .= '
		<div class="DnD Hidden">
			<h3>Glisser le fichier depuis votre explorateur : </h3>

			<span class="Hidden" rel="Config">nbFiles:' . $this -> nbFiles . ';type:' . implode(',', $this -> authorize) . ';fieldName:' . $this -> field['name'] . ';fieldId:' . $this -> field['id'] . '</span>
			<div>
				
			</div>
		</div>
		';	
		
		$strHtml .= '
		<div class="URL Hidden">
			<h3>Adresse Internet : </h3>
			<input type="text" class="External" />
		</div>';

		
		/*
		if($this -> FTP == true) {
			$strHtml .= 'Ou';
			
			$strHtml .= '
			<div class="Tree">
				<h3>Médiathèque : </h3>
				<div class="Filetree FTP"></div>
				<div class="InfosFile"></div>
			</div>';
		}*/

		$strHtml .= '
		</div>
		<div class="Spacer"></div>
		<div class="List">
		<table>
			<tr>
				<td>
					<table class="FileList" cellspacing="0" cellpadding="0">
						<tr><th>Nom du fichier</th><th>Etat</th><th>Téléchargé</th><th>Total</th></tr>
						<tr class="Even"><td colspan="4">&nbsp;</td></tr>
						<tr class="Odd"><td colspan="4">&nbsp;</td></tr>
						<tr class="Even"><td colspan="4">&nbsp;</td></tr>
						<tr class="Odd"><td colspan="4">&nbsp;</td></tr>
					</table>
				</td>
			</tr>
			
		</table>
		</div>
		<div class="Spacer"></div>
		';
		
		$strHtml .= '</div>';
		return $strHtml;
	}
}


?>