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


function gettokens() {
	return include('./fbtokens.php');
}


$strHtml = NULL;
$tokens = gettokens();

$synchro = $_GET;

if($synchro['mode'] == 'pieasytofb') {
	
	$album = $synchro['idpieasy'];
	$strSql = 'SELECT * FROM `' . $pluginTableAlbums . '` WHERE `id` = ' . intval($album) . ' LIMIT 1';
	if($resSql = Sql::query($strSql))
		$albumSql = mysql_fetch_assoc($resSql);
	
	if($synchro['type'] == 'album') {
		
		$fbid = explode(',', $albumSql['fbid']);
		$args = array();
		
		$args['access_token'] = $synchro['tk'];		
		$args['description'] = strip_tags($albumSql['description']);
		$args['name'] = $albumSql['label'];
		$args['type'] = 'album';
			
		try {
			
			if($synchro['create'] == true) {
				$infos = $facebook -> api('/' . $tokens[$k]['account'] . '/albums/', 'POST', $args);
				$fbid[$synchro['tkid']] = $infos['id'];

				$strSql = '
					UPDATE `' . $pluginTableAlbums . '`
					SET `fbid` = "' . Sql::secure(implode(',', $fbid)) . '" WHERE `id` = ' . $albumSql['id'];
					
				Sql::query($strSql);

			} else {
				$infos = $facebook -> api('/' . $synchro['facebookid'], 'POST', $args);
			}
						
		} catch(FacebookApiException $e) { }
		
	} else if($synchro['type'] == 'image') {
		
		$image = $Plugin -> getFolder($albumSql['idx_galerie'], $albumSql['id']) . $synchro['image'];
		$props = new InfosFile($image);
		$fbids = explode(',', $props -> getProp('fbid'));
		
		// Actual synchro
		try {
			
			$args = array();
			$args['message'] = 'test';
			$args['image'] = '@' . realpath($image);
			$args['access_token'] = $synchro['tk'];
			
			$album = explode(',', $albumSql['fbid']);
			$album = $album[$synchro['tkid']];
			
			$imagefb = $facebook -> api('/' . $album . '/photos', 'POST', $args);
			$fbids[$synchro['tkid']] = $imagefb['id'];
			$props -> setProp('fbid', implode(',', $fbids));
			$props -> save();
					
		} catch(FacebookApiException $e) {
			echo "Error while upload file";
		}	
	}
} else {
	
	
	if($synchro['type'] == 'album') {
		
		try {
			$infos = $facebook -> api('/' . $synchro['idfb'], 'GET', array('access_token' => $synchro['tk']));
			
			$fbid = array(0, 0, 0, 0, 0, 0);
			$fbid[$tkid] = $infos['id'];
			$fbid = implode(',', $fbid);
			
			$cover = $facebook -> api('/' . $infos['cover_photo'], 'GET', array('access_token' => $synchro['tk']));
			$cover = FileManager::getExternal($cover['source']);
			
			if($synchro['create'] == 'true') {
				$strSql = '
					INSERT INTO `' . $pluginTableAlbums . '`
					(`idx_galerie`, `label`, `description`, `fbid`, `date`, `cover`)
					VALUES
					(0, "' . Sql::secure($infos['name']) . '", "' . Sql::secure($infos['description']) . '", "' . $fbid . '", "' . strtotime($infos['created_time']) . '", "")
				';
				
				$folder = $Plugin -> getFolder(0);
				Sql::query($strSql);
				$id = mysql_insert_id();
				
				
				$dirname = $folder . 'album_' . $id . '/';
				if(!is_dir($dirname))
					mkdir($dirname);
				
				if(!is_dir($dirname . 'thumbs'))
					mkdir($dirname . 'thumbs');
				
				if(!is_dir($dirname . 'cover'))
					mkdir($dirname . 'cover');
				
				if(!is_dir($dirname . 'cover/thumbs'))
					mkdir($dirname . 'cover/thumbs');
			
			} else
				$id = $synchro['pieasyid'];
			 
			$coverData = array();
			$idcover = uniqid();
			
			global $_baseUrl;
			$fileTemp = $_baseUrl . DATA_ROOT_REL . FOLDER_UPLOAD_TEMP . $idcover;
			file_put_contents($fileTemp, $cover);
			chmod($fileTemp, 0777);
			$coverData['files'] = array($idcover);
			$coverData['filename'] = array('cover photo');
			$coverData['text'] = array('');
			$coverData['ext'] = array('');
			print_r($coverData);
			$Plugin -> makeAlbumCover(0, $id, $coverData);
		
			$strSql = 'UPDATE `' . $pluginTableAlbums . '` SET `cover` = "' . $idcover . '" WHERE `id` = ' . $id . ' LIMIT 1';
			Sql::query($strSql);
			
			echo $id;
			
		} catch(FacebookApiException $e) {
			
			echo $e -> getMessage();
		}
		
	} else if($synchro['type'] == 'image') {
		
		
		// Actual synchro
		try {
			
			$args = array();
			$args['access_token'] = $synchro['tk'];
			$infos = $facebook -> api('/' . $synchro['albumid'] . '/' . $synchro['idfb'], 'GET', $args);
			
			$ressource = FileManager::getExternal($infos['source']);
			
			$fbid = array(0, 0, 0, 0, 0, 0, 0);
			$fbid[$synchro['tkid']] = $synchro['idfb'];

			$Plugin -> savePicture($ressource, $synchro['idalbumpieasy'], array('fbid' => implode(',', $fbid)), 'ressource');
			
		} catch(FacebookApiException $e) {
			print_r($e);
		}	
	}
}	

?>	