<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldFacebookevent extends Field {
	
	const fieldModel = '
	<input type="hidden" name="%s[published]" value="%s" class="%s" />
	<input type="checkbox" name="%s[publish]" class="%s" %s />
	';
	
	protected $fb_event_infos;
	
	public function __construct($dataSql = NULL) {
		$this -> modFields[''] = 'VARCHAR( 250 ) NOT NULL';
		parent::__construct($dataSql);	
		$this -> fb_event_infos = !empty($dataSql['fb_event_infos']) ?  explode(',', $dataSql['fb_event_infos']) : false;
	}
	
	public function check($value) {		
		return false;
	}
	
	
	public function treat($value) {	
		
		if(!$facebook = Instance::getInstance('Facebook'))
			return $this -> log();
		

		if($value['published'] != NULL && empty($value['publish'])) {
		  try {	
			$v = $facebook -> api('/me/events/' . $value['published'], 'DELETE');
		  } catch (FacebookApiException $e) {
			Instance::getInstance('Message') -> logTxt('fberror_del', $e, 0);
		  }
		}

		if(empty($value['publish']))
			return;
		
		// Titre, Description, Date de début, Date de fin, Heure de début, Heure de fin, Lieu, Image
		$fields = $this -> fb_event_infos;
		foreach($fields as $field)
			$fields[$field] = Field::buildFromId($field);

		//$fields_fb = array('name' => '', 'description' => '', 'start_time' => 0, 'end_time' => 0, 'location' => '');
		$fields_fb = array();
					
		// Event name
		$fields_fb['name'] = NULL;
		if($fields[0]) {
			$content = $_POST[$fields[0] -> formName()];
			switch($fields[0] -> getType()) {
				case 'text': $content = strip_tags($content); break;
			}
			$fields_fb['name'] = $content;
		}
		
		// Event description
		$fields_fb['description'] = NULL;
		if($fields[1]) {
			$content = $_POST[$fields[1] -> formName()];
			switch($fields[1] -> getType()) {
				case 'text': $content = strip_tags($content); break;
			}
			$fields_fb['description'] = $content;
		}
		
		// Start date
		$fields_fb['start_time'] = time();
		if($fields[2]) {	
			$content = $_POST[$fields[2] -> formName()];
			if($fields[2] -> getType() == 'date')
				$fields_fb['start_time'] = mktime(0, 0, 0, $content[1], $content[0], $content[2]);
		}

		if($fields[4]) {	
			$lang = $fields[4] -> getFirstLang();
			$content = $_POST[$fields[4] -> formName()];
			if($fields[4] -> getType() == 'time')
				$fields_fb['start_time'] += $content['hour'] * 3600 + $content['minute'] * 60;
		}

		// End date
		$fields_fb['end_time'] = time();
		if($fields[3]) {	
			$content = $_POST[$fields[3] -> formName()];
			if($fields[3] -> getType() == 'date')
				$fields_fb['end_time'] = mktime(0, 0, 0, $content[1], $content[0], $content[2]);
		}

		if($fields[5]) {	
			$lang = $fields[5] -> getFirstLang();
			$content = $_POST[$fields[5] -> formName()];
			if($fields[5] -> getType() == 'time')
				$fields_fb['end_time'] += $content['hour'] * 3600 + $content['minute'] * 60;
		}

		
		// Event description
		$fields_fb['description'] = NULL;
		if($fields[6]) {
			$content = $_POST[$fields[6] -> formName()];
			switch($fields[0] -> getType()) {
				case 'text': $content = strip_tags($content); break;
			}
			$fields_fb['description'] = $content;
		}
		
		// Picture
		$fields_fb['description'] = NULL;
		if($fields[7]) {
			$lang = $fields[7] -> getFirstLang();
			$content = $_POST[$fields[7] -> formName()];
			if($fields[7] -> getType() == 'picture') {
				$fle = realpath($f -> getImgFolder() . $c['files'][0]);
				$fields_fb['@' . basename($fle) . '.jpg'] = '@' . $fle;	
			}
		}
	
		// On ne poste que sur le premier id
		$posted = true;
		for($i = 1; $i < 6; $i++) {
			if($id = $cfg -> get('FB_ID_' . $i))
				try {
					$value = $facebook -> api('/' . $id . '/events', 'POST', $content);
					$str = $value['id'];
				  } catch (FacebookApiException $e) {
					Instance::getInstance('Message') -> logTxt('fberror', $e, 0);
					$posted = false;
				  }
			else
				$posted = false;
		}	
		
		return $str;

		return $value;
	}
	
	public function showField($value, $error = false) {

		$class = array();		
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'String';
		$class = implode(' ' , $class);

		$fname = $this -> formName();
		return sprintf(self::fieldModel, 
			   $fname,	
			   $value,
	   		   $class, 
			   $fname, 
			   $class, 
			   $value != '' ? ' checked="checked"' : ''
		);
	}
	
	
	public function configFields() {

		extract($_POST);
		return array(
			'fb_event_infos' => implode(',', $FBEvent)
		);
	}
}


?>