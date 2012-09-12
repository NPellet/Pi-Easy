Bienvenue sur le Pi-Easy

<?php

$auth = Instance::getInstance('Config') -> get('AUTH_KEY');

if($auth) {

	$urlExternal = 'http://pi-com.ch/clients/pi-easy.home.php?auth=' . sha1($auth);
	if($file = FileManager::getExternal($urlExternal)) {
		$dom = new DOMDocument;
		if($dom -> loadXML($file)) {
			$s = simplexml_import_dom($dom);
			foreach($s -> news as $news) {
				echo '
				<div class="News">
					<h3>
						' . $news -> title[0] . '
					</h3>
					<p class="Date">
						' . FormatDate($news -> date[0]) . '
					</p>
					<p class="Content">
						' . $news -> content[0] . '
					</p>
				</div>';
			}
		}
	} 
}

?>