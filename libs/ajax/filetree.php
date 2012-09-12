<?php
//
// jQuery File Tree PHP Connector
//
// Version 1.01
//
// Cory S.N. LaViska
// A Beautiful Site (http://abeautifulsite.net/)
// 24 March 2008
//
// History:
//
// 21 avril 2011 : Update for working with Pi-Easy (Norman Pellet)
// 1.01 - updated to work with foreign characters in directory/file names (12 April 2008)
// 1.00 - released (24 March 2008)
//
// Output a list of files for jQuery File Tree
//

$_baseUrl = '../../';
$type = NULL;
$mime = array();

require('../includes/ajax.utils.inc.php');

$_POST['dir'] = urldecode($_POST['dir']);
if(!empty($_POST['type'])) $type = $_POST['type'];
if(!empty($_POST['mime'])) $mime = $_POST['mime'];

if(!is_array($type))	$type = array($type);

if(strpos($_POST['dir'], '..'))
	return;

//$_baseUrl = './';

$dir = $_POST['phproot'] == 'ftp' ? $_baseUrl . FTP_ROOT_REL . $_POST['dir'] : $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_MEDIAS . $_POST['dir'];	
$dir = urldecode($dir);
$path = realpath($dir);
	
if(isset($_POST['handleFolder'])) {
	
	$rename = !empty($_POST['current']) ? $_POST['current'] : false;
	$newName = '/' . $_POST['newval'];
	
	if(!$rename)
		mkdir($path . $newName);
	else
		rename($path . $rename, $path . $newName);
	return;	
}

if( file_exists($dir) ) {

	$files = scandir($path);
	natcasesort($files);
	if( count($files) >= 2 ) { 
		echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		
		foreach( $files as $file ) {
			if( file_exists($dir . $file) && $file != '.' && $file != '..' && is_dir($dir . $file) ) {
				echo "
				<li class=\"directory collapsed\">
					<a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">
						" . htmlentities(utf8_decode($file)) . "
					</a>
				</li>";
			}
		}
	
		foreach( $files as $file ) {
			if(file_exists($dir . $file) && $file != '.' && $file != '..' && !is_dir($dir . $file)) {
				
				if(strpos($file, '.props'))
					continue;
				
				$infos = new InfosFile($dir . $file);
				$fileInfos = $infos -> getProps();
				
				$i = 0;
				$c = count($type);
				
				$out = true;

				foreach($type as $t) 
					if($t == NULL || FileManager::filter($t, $fileInfos['mime'])) 
						$out = false;
				
				if($out == true)
					continue;
												
				if(count($mime) > 0 && !FileManager::checkMime($fileInfos['mime'], $mime))
					continue;
					
				$ext = $fileInfos['ext'];
				$name = $fileInfos['name'];
				
				echo "
				<li class=\"file ext_" . $ext . "\">
					<a href=\"#\" rel=\"" . htmlentities($file) . ";" . htmlentities($_POST['dir']) . "\">
						" . htmlentities($name . '.' . $ext) . "
					</a>
				</li>";
			}
		}

		if(!preg_match('!^\./' . FOLDER_UPLOAD_MODULES . '!', $_POST['dir']))
			echo "
			<li class=\"directory collapsed newdirectory\">
				<a href=\"#\" rel=\"" . htmlentities($_POST['dir']) . "\">
					+ Ajouter un dossier
				</a>
			</li>";

		echo "</ul>";	
	}
}

?>