<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS")
exit(0);

securePath();
$filePath = findFile();
$mp3 = new Mp3($filePath);

if(!empty($_POST['id3_title'])) {
	
	$t = array('id3_title' => 'title', 'id3_artist' => 'artist', 'id3_album' => 'album', 'id3_genre' => 'genre', 'id3_year' => 'year');
	foreach($t as $post => $tag) {
		$mp3 -> setTag($tag, $_POST[$post]);	
	}
	$mp3 -> saveTag();
} else if(isset($_GET['file'])) {	
	echo json_encode($mp3 -> getTags());
}

?>