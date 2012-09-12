<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldVideo extends Field {
	
	const fieldModel = '
	<h3>Adresse Internet / Code flash : </h3>
	<div>
		<input type="text" class="%s video_url" placeholder="%s" /> <input type="submit" value="Charger la vidéo" class="loadVideo" />
		<input type="hidden" name="%s[platform]" value="%s" class="video_platform" />
		<input type="hidden" name="%s[id]" value="%s" class="video_id" />
		<div class="video build_video" data-video-id="%s" data-video-platform="%s"></div>
	</div>';
	
	public function __construct($dataSql = NULL) {
		parent::__construct($dataSql);	
		$this -> modFields = array();
		$this -> modFields['platform'] = 'VARCHAR( 250 ) NOT NULL';
		$this -> modFields['id'] = 'VARCHAR( 250 ) NOT NULL';
	}
	
	public function check($value) {
		if($this -> isRequired() && $value == NULL)
			return $this -> errors['empty'];
		
		return false;
	}
	
	public function display($value) {
		return '<div class="build_video" data-video-id="' . $value['id'] . '" data-video-platform="' . $value['platform'] . '"></div>';
	}
	
	public function treat($value) {
		return $value;
	}
	
	public function showField($value, $error = false) {

		$class = array();		
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'String';
		$class = implode(' ' , $class);

		return sprintf(
			self::fieldModel, 
			$class, 
			$this -> placeholder,
			$this -> formName(), 
			$value['platform'], 
			$this -> formName(), 
			$value['id'] ,
			$value['id'],
			$value['platform']
		);
	}
}

?>