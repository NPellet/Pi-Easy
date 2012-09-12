<?php

$_baseUrl = '../../../';
require('../../../libs/includes/ajax.utils.inc.php');


$Plugin = PluginController::getPlugin('galeries');
$Cfg = new Config();
$facebook = new Facebook(array(
			'appId' => $Cfg -> get('FB_APPID'),
			'secret' => $Cfg -> get('FB_APPSECRET'),
			'cookie' => true,
			'fileUpload' => true
		));
		
$pluginTableAlbums = Sql::buildTable($Plugin -> params['t_albums']['value']);
$_requiredPerms = array('publish_stream', 'email', 'create_event', 'user_photos', 'friends_photos', 'offline_access');
$user = $facebook->getUser();

try {
	$uid = $facebook->getUser();
	$me = $facebook->api('/me');
	$perms = $facebook -> api('/me/permissions');
	$logged = true;

	foreach($_requiredPerms as $p) {
		if(!array_key_exists($p, $perms['data'][0])) {
				$logged = false;
				break;
			}	
		}	
} catch (FacebookApiException $e) {
	error_log($e);
}
			
if($user && $logged == true || file_exists('./fbtokens.php')) {
	$proceed = true;
} else {
	
echo "
<div id=\"fb-root\"></div>
<script language=\"javascript\" type=\"text/javascript\">
<!--
	window.fbAsyncInit = function() {
		
		FB.init({
			appId: '" . $facebook->getAppID() . "',
			cookie: true,
			status: true,
			xfbml: true,
			oauth: true
		});
		
		FB.Event.subscribe('auth.login', function(response) {
			$(\"#FBSynch\").html('Connexion effectuée. Actualiser pour synchroniser');
		});
	};

-->
</script>

<script language=\"javascript\" type=\"text/javascript\">
<!--
(function(d){
     var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
     js = d.createElement('script'); js.id = id; js.async = true;
     js.src = \"//connect.facebook.net/en_US/all.js\";
     d.getElementsByTagName('head')[0].appendChild(js);
   }(document));
  
-->
</script>
 	 
<fb:login-button scope='" . implode(',', $_requiredPerms) . "'></fb:login-button>
";
	$proceed = false;
}

// No need to go further at this point
if(!$proceed)
	exit;


function getPieasy() {
	
	global $Plugin, $pluginTableAlbums;
	$albums = array();
	$strSql = 'SELECT * FROM `' . $pluginTableAlbums . '`';
	if($resSql = Sql::query($strSql)) {
		while($dataSql = mysql_fetch_assoc($resSql)) {
			
			$albums[$dataSql['id']] = array(
				'id' => $dataSql['id'],
				'label' => $dataSql['label'],
				'fbid' => explode(',', $dataSql['fbid']),
				'images' => array()
			);
					
			$i = sizeof($albums) - 1;
			$folder = $Plugin -> getFolder($dataSql['idx_galerie'], $dataSql['id']);
			if($dir = opendir($folder)) {
				while($file = readdir($dir)) {		
					if(is_dir($folder . $file))
						continue;
					
					if(strpos($file, '.props'))
						continue;
						
					$infos = new InfosFile($folder . $file);
					// Ne prend que les images
					if(FileManager::filter('image', $infos -> getMime()))
						$albums[$dataSql['id']]['images'][] = array(
											'file' => $file,
											'fbid' => explode(',', $infos -> getProp('fbid'))
											);
				}
			}
		}
	}
	return $albums;
}


function getFBAlbums($tkid) {
	global $tokens, $facebook;
	$albums = array();
	try {
		$albums = $facebook -> api('/' . $tokens[$tkid]['account'] . '/albums', array('access_token' => $tokens[$tkid]['token']));
	} catch(FacebookApiException $e) { }
	
	$data = $albums['data'];
	if(!$data)
		return Array();
	
	$toreturn = array();
	foreach($data as $id => $val) {
		
		$toreturn[$val['id']] = $val;
		$cover = $facebook -> api('/' . $val['cover_photo'], array('access_token' => $tokens[$tkid]['token']));
		$cover = $cover['images'][3]['source'];
		$toreturn[$val['id']]['cover'] = $cover;
		
	}
	
	return $toreturn;
}

function getFBImages($tkid, $albumid = NULL) {
	global $tokens, $facebook;
	$images = array();
	try {
		$images = $facebook -> api(($albumid == NULL ? '' : '/' . $albumid) . '/photos', array('access_token' => $tokens[$tkid]['token'], 'limit' => 200));
	} catch(FacebookApiException $e) {
		
	}

	$data = $images['data'];
	if(!$data)
		return Array();
	
	
	$toreturn = array();
	foreach($data as $id => $val)
		$toreturn[$val['id']] = $val;
	
	return $toreturn;	
}

function gettokens() {
	
	if(file_exists('./fbtokens.php')) {
		//unlink('./fbtokens.php');
		return include('./fbtokens.php');
	}
	
	global $Cfg;
	global $facebook, $user;
	$tokens = array();	
	$accounts = $facebook -> api('/me/accounts', 'GET');
	
	
	$str = '<?php

$tokens = array();
';

	for($i = 1; $i < 6; $i++) {
		
		echo $id . ' ' . $Cfg -> get('FB_ID_' . $i);
		if($id = $Cfg -> get('FB_ID_' . $i)) {
				
			if($id == $user) {
				$tokens[$i] = array('token' => $facebook -> getAccessToken(), 'account' => $id);
				$str .= '$tokens[$i]' . " = array('token' => \"" . $facebook -> getAccessToken() . "\", 'account' => \"" . $id . "\");";
				
			} else {	
			
				foreach($accounts['data'] as $d)
					if($d['id'] == $id) {
						$tokens[$i] = array('token' => $d['access_token'], 'account' => $id);
						$str .= '$tokens[$i]' . " = array('token' => \"" . $d['access_token'] . "\", 'account' => \"" . $id . "\");";
						break;
					}
			}
		}
	}
	print_r($tokens);
	$str .= '

return $tokens;

?>';
	
	file_put_contents('./fbtokens.php', $str);
	return $tokens;
}


$strHtml = NULL;
$tokens = gettokens();
$synchro = array();
$synchro2 = array();


$strHtml .= '<div class="PieasyToFB">

<h2>de Pieasy à Facebook</h2>';

foreach($tokens as $tkid => $tkval) {
	
	$strHtml .= '<div class="SynchroPage">';
	$strHtml .= '<h3>Synchronisation avec ' . $tkval['name'] . '</h3>';
	
	$albums = getPieasy();
	$albumsfb = getFBAlbums($tkid);
	
	$strHtml .= '<div class="Message Orange">Veuillez sélectionner les albums que vous désirez synchroniser entre Pi-Easy et Facebook</div>';
	$strHtml .= '<ul class="SynchroSession tkid' . $tkid . '">';
	
	foreach($albums as $album) {
		
		$newimages = 0;		
		$albumFbId = $album['fbid'][$tkid];	
		
		$s = array('mode' => 'pieasytofb', 'type' => 'album', 'tkid' => $tkid, 'tk' => $tkval['token'], 'idpieasy' => $album['id']);
		if(empty($albumFbId) || empty($albumsfb[$albumFbId])) {
			$albumstocreate[] = array($tkid, $album['id']);
			$stralbum = 'Non-existant sur Facebook';
			$class = 'New';
			$s['create'] = true;
		} else {
			$stralbum = 'Existant';
			$class = 'Exists';
			$s['create'] = false;
			$s['facebookid'] = $albumFbId;
		}
		$synchro[] = $s;	

		$imagesfb = getFBImages($tkid, $albumFbId);

		$imagespieasy = $album['images'];
		foreach($imagespieasy as $imagepieasy) {
			if(empty($imagesfb[$imagepieasy['fbid'][$tkid]])) {
				$synchro[] = array('mode' => 'pieasytofb', 'type' => 'image', 'tkid' => $tkid, 'tk' => $tkval['token'], 'idpieasy' => $album['id'], 'image' => $imagepieasy['file']);
				$newimages++;
			}
		}
		
		$strHtml .= '<li class="album' . $album['id'] . ' ' . $class . '"><span class="AlbumLabel">' . $album['label'] . '</span> (<span class="albumstate">' . $stralbum . '</span>) <br />(<span class="nbnewimages">' . $newimages . '</span> nouvelles images)<div class="Spacer"></div></li>';
	}
	
	$strHtml .= '</ul>';
	$strHtml .= '</div>';
}


$Sync = new Button("Synchro", "Synchroniser Facebook", true);
$Sync -> setOptions(array('id' => "BeginSynchroPieasyToFB"));
$strHtml .= '<ul class="Buttons Actions">' . $Sync -> display() . '</ul>';


$strHtml .= '</div>';
$strHtml .= '<div class="FBToPieasy">
<h2>de Facebook à Pieasy</h2>
<p></p>
';

foreach($tokens as $tkid => $tkval) {
	
	$strHtml .= '<div class="SynchroPage">';
	$strHtml .= '<h3>Synchronisation avec ' . $tkval['name'] . '</h3>';
	
	$albums = getPieasy();
	$albumsfb = getFBAlbums($tkid);
	
	$strHtml .= '<div class="Message Orange">Veuillez sélectionner les albums que vous désirez synchroniser entre Facebook et Pi-Easy</div>';
	$strHtml .= '<ul class="SynchroSession tkid' . $tkid . '">';
	
	foreach($albumsfb as $album) {
		
		$newimages = 0;
		$foundalbum = false;		
		$albumFbId = $album['id'];	
		foreach($albums as $albumpieasy) {
			if(in_array($albumFbId, $albumpieasy['fbid'])) {
				$foundalbum = $albumpieasy['id'];
				break;
			}
		}
		
		$s = array('mode' => 'fbtopieasy', 'type' => 'album', 'tkid' => $tkid, 'tk' => $tkval['token'], 'idfb' => $album['id'], 'idalbumfb' => $albumFbId);
		if(!$foundalbum) {
			$albumstocreate[] = array($tkid, $album['id']);
			$stralbum = 'Non-existant sur le Pi-Easy';
			$class = 'New';
			$s['create'] = true;
		} else {
			$stralbum = 'Existant';
			$class = 'Exists';
			$s['create'] = false;
			$s['pieasyid'] = $foundalbum;
		}
		$synchro2[] = $s;	
		
		$imagesfb = getFBImages($tkid, $album['id']);
		foreach($imagesfb as $imagefb) {
			
			$found = false;
			if($foundalbum) {
				foreach($albums[$foundalbum]['images'] as $imagepieasy) {
					if(in_array($imagefb['id'], $imagepieasy['fbid']))
						$found = true;
				}
			}
			if(!$found) {
				$synchro2[] = array('mode' => 'fbtopieasy', 'idalbumfb' => $albumFbId, 'idalbumpieasy' => $foundalbum, 'type' => 'image', 'tkid' => $tkid,  'tk' => $tkval['token'], 'idfb' => $imagefb['id']);
				$newimages++;
			}
		}

		$strHtml .= '<li class="fbalbum' . $album['id'] . ' ' . $class . '"><img src="' . $album['cover'] . '" /><span class="AlbumLabel">' . $album['name'] . '</span> (<span class="albumstate">' . $stralbum . '</span>) <br />(<span class="nbnewimages">' . $newimages . '</span> nouvelles images)<div class="Spacer"></div></li>';
	}
	
	$strHtml .= '</ul>';
	$strHtml .= '</div>';
}


$Sync = new Button("Synchro", "Synchroniser le Pi-Easy", true);
$Sync -> setOptions(array('id' => "BeginSynchroFBToPieasy"));
$strHtml .= '<ul class="Buttons Actions">' . $Sync -> display() . '</ul>';

$strHtml .= '</div>';

if(@$_GET['json'] == true) {
	
	echo json_encode(array('pieasytofacebook' => $synchro, 'facebooktopieasy' => $synchro2));
	return; 
}

echo $strHtml;

?>
<script language="javascript" type="text/javascript">
<!--

$.synchro = <?php echo json_encode($synchro); ?>;
$.synchro2 = <?php echo json_encode($synchro2); ?>;

$(document).ready(function() {
	
	$("li").bind("click", function() {
		$(this).toggleClass('Selected');
	});
	
	$("#BeginSynchroPieasyToFB").bind('click', function() {
		iteratorPieayToFb = 0;
		var result = true;
		iteratePieasyToFb(iteratorPieayToFb);
		$(this).parent().hide('slow', function() {
			messagePieasy = $('<div class="Message Orange"><div class="Image"><img src="design/images/ajax-loader-orange.gif" /></div>Le Pi-Easy est en train de synchroniser vos albums. Cette opération peut prendre plusieurs minutes. Merci de ne pas quitter la page tant que l\'opération n\'est pas terminée</div>').insertAfter($(this));
		});
	});
	
	function iteratePieasyToFb(iteration) {
		
		if(!$.synchro[iteration]) {
			messagePieasy.removeClass("Orange").addClass("Green").html("Les albums ont été correctement synchronisés.");
			return;
		}
		
		var album = $(".tkid" + $.synchro[iteration]['tkid']).find(".album" + $.synchro[iteration]['idpieasy']);
		
		if(album.is(".Selected"))
			$.get('plugins/galeries/ajax/fbsynchroelement.ajax.php', $.synchro[iteration], function(data) {
				
				
				if($.synchro[iteration]['type'] == 'album') {
					album.find('.albumstate').html('Existant');
				} else {
					nb = album.find('.nbnewimages');
					nb.html(parseInt(nb.html()) - 1);
				}
				
				iteratorPieayToFb++;
				iteratePieasyToFb(iteratorPieayToFb);
				
			});
		else {
			iteratePieasyToFb++;
			iteratePieasyToFb(iteratorPieayToFb);
		}
	}

	var messageFb;
	$("#BeginSynchroFBToPieasy").bind('click', function() {
		iteratorFbToPieasy = 0;
		var result = true;
		iterateFbToPieasy(0);
		$(this).parent().hide('slow', function() {
			messageFb = $('<div class="Message Orange"><div class="Image"><img src="design/images/ajax-loader-orange.gif" /></div>Le Pi-Easy est en train de synchroniser vos albums. Cette opération peut prendre plusieurs minutes. Merci de ne pas quitter la page tant que l\'opération n\'est pas terminée</div>').insertAfter($(this));
		});
	});
	
	var albumId;
	function iterateFbToPieasy(iteration) {
		
		if(!$.synchro2[iteration]) {
			messageFb.removeClass("Orange").addClass("Green").html("Les albums ont été correctement synchronisés. Les nouveaux albums se trouvent à présent dans: <strong>albums non classés</strong>");
			return;
		}
		
		var album = $(".tkid" + $.synchro2[iteration]['tkid']).find(".fbalbum" + $.synchro2[iteration]['idalbumfb']);
		if(!$.synchro2[iteration]['idalbumpieasy'])
			$.synchro2[iteration]['idalbumpieasy'] = albumId;
		
		if(album.is(".Selected"))
			$.get('plugins/galeries/ajax/fbsynchroelement.ajax.php', $.synchro2[iteration], function(data) {
				if($.synchro2[iteration]['type'] == 'album') {
					album.find('.albumstate').html('Existant');
					album.addClass('Exists');
					albumId = data;
					
				} else {
					nb = album.find('.nbnewimages');
					nb.html(parseInt(nb.html()) - 1);
				}				
				iteratorFbToPieasy++;
				iterateFbToPieasy(iteratorFbToPieasy);
			});
		else {
			iteratorFbToPieasy++;
			iterateFbToPieasy(iteratorFbToPieasy);
		}
	}
});

-->
</script>