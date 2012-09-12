<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldFacebookpost extends Field {
	
	const fieldModel = '
	<input type="hidden" name="%s[published]" value="%s" />
	<input type="checkbox" name="%s[publish]" class="%s" %s />
	%s
	';
	
	protected $fb_post_infos;
	
	public function __construct($dataSql = NULL) {
		$this -> modFields[''] = 'VARCHAR( 250 ) NOT NULL';
		
		parent::__construct($dataSql);	
		$this -> fb_post_infos = !empty($dataSql['fb_post_infos']) ?  explode(',', $dataSql['fb_post_infos']) : false;
	}
	
	public function check($value) {		
		return false;
	}
	
	public function treat($value) {	
		/*
		if(!empty($value['keep']))
			return $value['published'];
			
		if(!$facebook = Instance::getInstance('Facebook'))
			return $this -> log();

		if($value['published'] != NULL) {
		  try {
			$value['published'] = explode(',', $value['published']);
			foreach($value['published'] as $val)	
				$v = $facebook -> api('/' . $val, 'DELETE');
		  } catch (FacebookApiException $e) {
			Instance::getInstance('Message') -> logTxt('fberror_del', $e, 0);
		  }
		}
			
		if(empty($value['publish']))
			return;
			
		// Titre, Message (court), Image, Lien, Description du lien
		$fields = $this -> fb_post_infos;
		$nfields = array();
		foreach($fields as $key => $field)
			$fields[$key] = Field::buildFromId($field);

		//$fball = array('name', 'message', 'picture', 'link', 'description');
		$fields_fb = array();
		
		// Post title
		if($fields[0]) {
			$content = $_POST[$fields[0] -> formName()];
			switch($fields[0] -> getType()) {
				case 'text': $content = strip_tags($content); break;
			}
			$fields_fb['name'] = $content;
		}

		// Post message
		if($fields[1]) {
			$content = $_POST[$fields[1] -> formName()];
			switch($fields[1] -> getType()) {
				case 'text': $content = strip_tags($content); break;
			}
			if(!empty($content))
				$fields_fb['message'] = $content;
		}

		// Post picture
		if($fields[2]) {
			$content = $_POST[$fields[2] -> formName()];
			if($fields[2] -> getType() == 'picture')
				$fields_fb['picture'] = BASE_URL . $fields[2] -> getImgFolder() . $content['files'][0];
		}

		// Post message
		if($fields[3]) {
			$content = $_POST[$fields[3] -> formName()];
			$content_text = NULL;
			$content_url = NULL;
			switch($fields[3] -> getType()) {
				case 'text': $content_url = strip_tags(trim($content)); break;
				case 'link': $content_url = $content['url']; $content_text = !empty($content['text']) ? $content['text'] : $content['url']; break;
			}
			$fields_fb['link'] = $content_url;
			$fields_fb['caption'] = $content_text;
		}
		
		// Post description
		if($fields[4]) {
			$content = $_POST[$fields[4] -> formName()];
			switch($fields[4] -> getType()) {
				case 'textarea': $content = trim(strip_tags($content)); break;
			}
			$fields_fb['description'] = $content;
		}
				
		if($fields_fb['link'] == NULL)	
			$fields_fb['link'] = Instance::getInstance('Config') -> get('FB_DEFAULT_URL');
		
		$cfg = Instance::getInstance('Config');
		$posted = 0;
		$str = array();
		
		for($i = 1; $i < 6; $i++) {
				
			if($id = $cfg -> get('FB_ID_' . $i))
				try {
					$value = $facebook -> api('/' . $id . '/feed', 'POST', $fields_fb);
					$str[] = $value['id'];
				  } catch (FacebookApiException $e) {
					Instance::getInstance('Message') -> logTxt('fberror', $e, 0);
					$posted++;
				  }
			else
				$posted++;
		}
*/
		return implode(',', $value);
	}
	
	
	public function showField($value, $error = false) {

		$class = array();		
		if($error)
			$class[] = FIELD_ERROR_CLASS;
		
		$class[] = 'Field';
		$class[] = 'String';
		$class = implode(' ' , $class);

		$fname = $this -> formName();
		
		$fields = $this -> fb_post_infos;
		$fb_infos = array('name', 'description', 'link', 'caption', 'picture', 'message');
		$pieasy_fields = array();
		
		foreach($fields as $key => $field)
			$pieasy_fields[$fb_infos[$key]] = Field::buildFromId($field);
		
		foreach($pieasy_fields as $key => $field) {
			
			$name = $field -> formName();
			switch($field -> getType()) {
				
				case 'link':
					switch($key) {
						case 'link':
							$appJs = "$('[name=\"" . $field -> formName() . "[url]\"]').val()";
						break;
						
						default:
						case 'caption':
							$appJs = "$('[name=\"" . $field -> formName() . "[text]\"]').val()";
						break;
					}	
				break;
				
				case 'picture':
					switch($key) {
						case 'picture':
							$appJs = '"' . BASE_URL . $field -> getImgFolder() . "\" + $('[name=\"" . $field -> formName() . "[files][]\"]:first').val()";
						break;
							
						default:
							$appJs = "$('[name=\"" . $field -> formName() . "[text]\"]:first').val()";
						break;
					}
				break;
				
				default:
					$appJs = "$('[name=" . $field -> formName() . "]').val()";	
				break;
			}
			
			$strJs .= ',
			' . $key . ': ' . $appJs . '';
		}
		
		$strJs = substr($strJs, 1);
		
		return '
		<div id="FBPost"><a class="Post">Poster sur Facebook</a></div>
		<script language="javascript" type="text/javascript">
		
		(function($) {
						
			$("#FBPost .Post").bind("click", function() {
				
				$.fb_postFields = {
					' . $strJs . '
				};
			
				$(this).unbind("click");
				
				var toPost = {};
				for(var i in $.fb_postFields) {
					toPost[i] = $.fb_postFields[i];
				}
				
				toPost[\'fb_id\'] = "' . $value . '";
				toPost[\'fieldname\'] = "' . $this -> formName() . '";
				$(this).parent().html(\'Chargement en cours...\');
				
				$.get("./libs/ajax/fbpost.php", toPost, function(data) {
					$("#FBPost").html(data);
					/*$.colorbox({
						html: data
					});*/
				});
			});
		}) (jQuery);
		
		</script>
		';
		
		
		
	}
	
	
	public function configFields() {

		extract($_POST);
		return array(
			'fb_post_infos' => implode(',', $FBPost)
		);
	}

}


?>