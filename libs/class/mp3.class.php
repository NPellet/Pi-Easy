<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Mp3 extends Security {

	private $file, $tags;
	
	public function __construct($path = NULL) {
		
		if($path == NULL) {
			$this -> fle = NULL;
			$this -> tags = array();
			return;
		}
		
		if(!function_exists('id3_get_tag') || !function_exists('id3_set_tag'))
			return false;
		
		$infos = new InfosFile($path);
		$mime = $infos -> getMime();
		
		if($mime == 'audio/mpeg') {
			$this -> file = $path;
			$this -> tags = id3_get_tag($this -> file);
		} else
			return false;
	}

	public function setTag($tagName, $tagValue) {
		$this -> tags[$tagName] = $tagValue;
	}
	
	public function getTag($tagName) {
		return !empty($this -> tags[$tagName]) ? $this -> tags[$tagName] : NULL;
	}
	
	public function getTags() {
		return $this -> tags;	
	}
	
	public function saveTag() {
	
		id3_remove_tag($this -> file);	
		$result = id3_set_tag($this -> file, $this -> tags, ID3_V1_0 );
		echo $result;
	/*	if(id3_set_tag($this -> file, $this -> tags, ID3_V1_1))
			return true;
		else
			echo 'Error';*/
	}
	
	public function getGenre($gId = NULL) {
		if($gId == NULL)
			return id3_get_genre_list();
		else
			return id3_get_genre_name($genreId);
	}
}

?>