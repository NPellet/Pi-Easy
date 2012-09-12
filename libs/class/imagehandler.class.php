<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Imagehandler extends Instance {
	
	private $image, $mime;
	
	public function setImageAsFile($fPath) {
		
		$infos = new InfosFile($fPath);
		$mime = $infos -> getMime();
		
		switch($mime) {
			
			case 'image/jpeg':
				$this -> image = imagecreatefromjpeg($fPath);
			break;
			
			case 'image/png':
				$this -> image = imagecreatefrompng($fPath);
			break;
			
			case 'image/gif':
				$this -> image = imagecreatefromgif($fPath);
			break;
		}
		
		$this -> mime = $mime;
	}


	public function setImageAsString($string) {
		$this -> image = imagecreatefromstring($string);
		$this -> mime = 'image/png';
	}
	
	public function process($width, $height, $folder, $filename) {
		$img = $this -> resample($width, $height);
		FileManager::uploadImage($folder, $img, $filename, $this -> mime);
	}
	
	
	public function resample($thumbW, $thumbH) {
		
		$imgW = imagesx($this -> image);
		$imgH = imagesy($this -> image);
		$ratioThb = $thumbW / $thumbH;
		$ratioImg = $imgW / $imgH;
		
		if($ratioThb > $ratioImg)
			$thumbW = $thumbH * $ratioImg;
		else
			$thumbH = $thumbW / $ratioImg;	
		
		$thumb = imagecreatetruecolor($thumbW, $thumbH);
	   
		switch($this -> mime) {
			
			case 'image/png':
				imagealphablending($thumb, false);
				$transp = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
				imagefill($thumb, 0, 0, $transp);
				imagesavealpha($thumb, true);
			break;
			
			case 'image/gif':
				$transp_index = imagecolortransparent($img);
				if ($transp >= 0) {
	
					$transp_color = imagecolorsforindex($this -> image, $transp_index);
					$transp_index = imagecolorallocate($thumb, $transp_color['red'], $transp_color['green'], $transp_color['blue']);
					imagefill($thumb, 0, 0, $transp_index);
					imagecolortransparent($thumb, $transp_index);
				}
			break;
		}
		
		imagecopyresampled($thumb, $this -> image, 0, 0, 0, 0, $thumbW, $thumbH, $imgW, $imgH);

		return $thumb;		
	}
}

?>