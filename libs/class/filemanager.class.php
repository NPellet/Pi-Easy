<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Filemanager extends Security {
	
	public static function uploadImage($folder, $file, $forcedFilename, $imageType) {
		
		if(!is_dir($folder))
			mkdir($folder, 0777);

		switch($imageType) {
			
			case 'image/jpeg':
				imagejpeg($file, $folder . $forcedFilename);
				return true;
			break;
			
			case 'image/png':
				imagepng($file, $folder . $forcedFilename);
				return true;
			break;
			
			case 'image/gif':
				imagegif($file, $folder . $forcedFilename);
				return true;
			break;
		}
		
		return false;
	}
	
	public static function renameFolder($oldName, $newName) {
		return !rename($oldName, $newName) ? self::log('file:0:5') : true;
	}
	
	public static function createFolder($name) {
		if(@mkdir($name)) {
			return true;	
		}
	}
	
	public static function duplicateDir($source, $destination) {
		
		if(!file_exists($source))
			mkdir($source, 0777);
			
		if(!is_dir($source))
			return self::log('file:0:6');
			
		$dir = opendir($source);
		if(!$dir)
			return self::log('file:0:7');
			
		while($file = readdir($dir)) {
			
			if($file == '.' || $file == '..')
				continue;
			
			$filePath = $source . '/' . $file;	
			if(is_dir($filePath))
				Filemanager::duplicateDir($filePath, $destination . '/' . $file);
			else
				rename($filePath, $destination . '/' . $file);
		}
	}
	
	public static function copy($filePath) {
		return copy($source, $destination) ? true : self::log('file:0:8');
	}
	
	public static function remove($filePath, $logError = true, $removeSelf = true) {
		
		if(!file_exists($filePath))
			return $logError ? self::log('file:0:9') : false;
		
		if(is_dir($filePath)) {
			
			$dir = opendir($filePath);
			while($file = readdir($dir)) {
				
				if($file == '.' || $file == '..')
					continue;
				
				Filemanager::remove($filePath . '/' . $file);
			}
			//return true;
		}
		
		if($removeSelf)
			return (is_dir($filePath) ? rmdir($filePath) : unlink($filePath)) ? true : ($logError ? self::log('file:0:10') : false);
	}
	
	
	public static function parse($filename) {
	
		$fname = strrpos($filename, '/') ? substr($filename, strrpos($filename, '/') + 1) : $filename;
		$extension = NULL;
		if(strrpos($fname, '.')) {
			$extension = strtolower(substr($fname, strrpos($fname, '.') + 1));
			$filename = substr($fname, 0, strrpos($fname, '.'));
		} else 
			$filename = $fname;
			
		return array('basename' => $filename, 'extension' => $extension, 'fullname' => $fname);
	}
	
	
	public static function getExternal($file) {
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $file);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT,TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT,50);
		
		$fle = curl_exec($ch);
		
		if($fle == false)
			return false;
		
		curl_close($ch);
		return $fle;
	}
	
	

	public function filter($type, $mime) {
		
		switch($mime) {
			
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
				if($type == 'image') return true; else return false;
			break;
			
			case 'audio/basic':
			case 'audio/mid':
			case 'audio/mpeg':
			case 'audio/x-aiff':
			case 'audio/x-mpegurl':
			case 'audio/x-pn-realaudio':
			case 'audio/x-wav':
				if($type == 'audio') return true; else return false;
			break;
			
			case 'video/mpeg':
			case 'video/quicktime':
			case 'video/x-la-asf':
			case 'video/x-ms-asf':
			case 'video/x-msvideo':
			case 'video/x-sgi-movie':
				if($type == 'video') return true; else return false;
			break;
	
			case 'multipart/mixed':
			case 'multipart/alternative':
			case 'multipart/related':
				if($type == 'archive') return true; else return false;
			break;
	
			default:
				if($type == 'document') return true; else return false;
			break;	
		}		
	}

	public function checkMime($mime, $tmimes) {
		
		if(!is_array($tmimes)) $tmimes = array($tmimes);
		if(in_array($mime, $tmimes))
			return true;
			
		$mime = explode('/', $mime);
		foreach($tmimes as $tmime) {
			$tmime = explode('/', $tmime);
			if($tmime[0] == '*' || ($mime[0] == $tmime[0] && ($mime[1] == $tmime[1] || $tmime[1] == '*')))
				return true;
		}
		return false;
	}
	
	public static function scandir($root, $mode = 'all') {
		
		$scanned = array();
		
		if($dir = opendir($root)) {
			while($file = readdir($dir)) {
				if($file != '.' && $file != '..') {
					if(is_dir($root . '/' . $file)) {
						$scanned[$file] = self::scandir($root . '/' . $file, $mode);
					} else if($mode == 'all') {
						$scanned[$file] = $file;
					}
				}
			}
		}

		return $scanned;
	}
}

?>