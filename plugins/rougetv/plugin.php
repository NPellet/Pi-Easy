<?php

class Plugin extends PluginController {
	
	protected $id, $params;
	
	public function __construct($id) {
		
		$this -> params = $this -> getParams($id);
	}

	public function run() {
		global $_baseUrl;
		
		Instance::getInstance('Page') -> addJs($_baseUrl . '/plugins/rougetv/scripts.js');

		$strHtml = '<div id="PluginRougeTV">';
		$strHtml .= $this -> form();
		$strHtml .= '</div>';
		
		return $strHtml;	
	}
	
	public function getUrl($vidId) {
		$vidId = strval($vidId);
		$url = sprintf($this -> params['schema_url']['value'], $vidId);

		$data = FileManager::getExternal($url);
		return $data;
	}
	
	public function downloadFile($url) {
		
		global $_baseUrl;
		
		$fileName = uniqid();
		$name = basename($url);
		echo $url;
		$filePath = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_TEMP . $fileName;
		$endPath = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_MEDIAS . $fileName;
		$fp = fopen($filePath, 'a+');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT,TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 6000);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		
		$fle = curl_exec($ch);
		
		if($fle == false)
			return false;
		
		curl_close($ch);
		
		
		$file = FileManager::parse($url);
		rename($filePath, $endPath);
		FileManager::makeProps(array(
			'path' => $endPath,
			'full_name' => $file['full_name']
		));
		
		return $filePath;
	}
	
	
	public function form() {
		
		$Form = new Form();
		$Form -> setUrl('');
		$Form -> addLang('', 'Télécharger la vidéo');
		$Form -> setClass('DataForm');
		
		$Field = $Form -> addField('id', '', false);
		$Field -> setTitle('Entrer l\'identifiant de la vidéo');
		$Field -> setField('<input type="text" class="idVideo" value="" placeholder="Ex: 2139" />');
		$Field -> setHelper('
			Vous pouvez entrer ici l\'indentifiant de la vidéo Rouge TV. 
			Cet identifiant est un nombre à 4 chiffres. 
			Si vous spécifiez ce numéro, vous n\'êtes pas obligés de spécifier l\'URL');
		
		$Field = $Form -> addField('url', '', false);
		$Field -> setTitle('Adresse Internet');
		$Field -> setHelper('Entrer ici l\'adresse Internet de la page sur laquelle se trouve la vidéo Rouge TV. Si vous spécifiez cette URL, vous n\'êtes pas obligés de spécifier son identifiant');
		$Field -> setField('<input type="text" class="urlVideo" value="" placeholder="http://rougetv.ch/get_video.php?i=21930" />');
		
		$Field = $Form -> addField('dl', '', false);
		$Field -> setTitle('Télécharger le fichier');
		$Field -> setHelper('Si vous cochez cette case, le fichier sera téléchargé dans la médiathèque du Pi-Easy. En laissant cette case décochée, le système ne téléchargera pas le fichier mais fournir son adresse URL directe');
		$Field -> setField('<input type="checkbox" class="dlVideo" />');
		
		
		Instance::getInstance('Page') -> addNavigation('Récupérer une vidéo de Rouge TV');
		return $Form -> display();
	}
}

?>