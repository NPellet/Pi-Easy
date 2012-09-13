<?php

class InfosFile {

	private $path, $props = array();
	
	public function __construct($fPath) {

		$this -> path = $fPath;
		$this -> findProps();	
	}
	
	private function findProps() {
		$strUrl = $this -> path . '.props';
		if(!file_exists($strUrl))
			touch($strUrl);
			
		if(@$infos = parse_ini_file($strUrl))
			$this -> props = $infos;
		else
			$this -> props = array();
	}
	
	public function setProp($prop, $value) {
		$this -> props[FormatFTPName($prop)] = $value;
	}
	
	public function getProp($prop) {
		if(array_key_exists($prop, $this -> props))
			return $this -> props[$prop];
		else
			return false;
	}
	
	public function save() {
	
		$props = $this -> props;
		if(empty($props['filesize']))
			$props['filesize'] = filesize($props['path']);
		
		$props['uploaded'] = time();
		
		if(!touch($this -> path . '.props'))
			return Instance::getInstance('Message') -> log();
		
		$strHtml = NULL;
		foreach($props as $name => $prop)
			$strHtml .= '
			' . $name . ' = "' . $prop . '"';

		if(file_put_contents($this -> path . '.props', $strHtml))
			return true;
			
		return Instance::getInstance('Message') -> log();
	
	}
	
	public function setPropsFromName($fileName) {
		$parsedname = FileManager::parse($fileName);
		$this -> setProp('full_name', $fileName);
		$this -> setProp('filename', $parsedname['basename']);
		$this -> setProp('mime', $this -> getMime());
		$this -> setProp('ext', $parsedname['extension']);
	}
	
	public function getProps() {
		return $this -> props;	
	}
	
	public function getPath() {
		return $this -> path;
	}
	
	public function getMime() {
		
		if($mime = $this -> getProp('mime'))
			return $mime;
					
		if(defined('FILEINFO_MIME_TYPE')) 
			$info = new finfo(FILEINFO_MIME_TYPE);
		else {
			$finfo = finfo_open(FILEINFO_MIME);
			if (!$finfo)
				return false;
			return @finfo_file($finfo, is_file(getcwd() . '/' . $this -> path) ? getcwd() . '/' . $this -> path : $this -> path);				
		}

		if(is_file(getcwd() . '/' . $this -> path))
			return $info -> file(getcwd() . '/' . $this -> path);
		else if($mime = $info -> file($this -> path))
			return $mime;
		else
			return false;
	}
	

	public function getFileUrl() {
		switch(@$fName['mime']) {
			case 'image/jpeg':
			case 'image/gif':
			case 'image/png':
				return 'get_file.php?file=' . basename($this -> path) . '&folder=' . dirname($this -> path);
			break;
			
			default:
				return FieldFile::getLogo(@$fName['mime']);
			break;
		}
	}

}

?>