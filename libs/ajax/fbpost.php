<?php

$_baseUrl = '../../';
require('../includes/ajax.utils.inc.php');

$Config = new Config();

$facebook = new Facebook(array(
			'appId' => $Config -> get('FB_APPID'),
			'secret' => $Config -> get('FB_APPSECRET'),
			'cookie' => true,
			'fileUpload' => true
		));
		
$_fbSession = $facebook -> getSession();
$_requiredPerms = array('publish_stream', 'create_event', 'offline_access', 'manage_pages');

if(!empty($_GET['post'])) {
	
	$pageId = $_GET['pageId'];
	
	if(!empty($_GET['post_id'])) {
		try {
			$value = $facebook -> api($_GET['post_id'], 'DELETE');
		} catch (FacebookApiException $e) {
		}
	}
	
	
	try {
		unset($_GET['pageId']);
		unset($_GET['post_id']);
		$value = $facebook -> api('/' . $pageId . '/feed', 'POST', $_GET);
		echo $value['id'];
		exit;
	} catch (FacebookApiException $e) {
	}
	
}


$me = null;
if ($_fbSession) {
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
}


if($me && $logged == true) {
	echo '
	<fb:profile-pic size="square" width="21" height="21" uid="' . $uid . '"></fb:profile-pic>
	<span>
		<strong>' . $me['name'] . '</strong>
	</span>
	';		
	
	$proceed = true;
} else {
	echo '
	<fb:login-button perms="' . implode(',', $_requiredPerms) . '"></fb:login-button>
	';	
	
	$proceed = false;
}

echo "
<div id='fb-root'></div>
<script>
window.fbAsyncInit = function() {
	FB.init({
	appId : '" . $facebook -> getAppId() . "',
	session : " . json_encode($_fbSession) . ", // don't refetch the session when PHP already has it
	status : true, // check login status
	cookie : true, // enable cookies to allow the server to access the session
	xfbml : true // parse XFBML
	});

	// whenever the user logs in, we refresh the page
	FB.Event.subscribe('auth.login', function() {console.log('Logged');
		$.get('./libs/ajax/fbpost.php', " . json_encode($_GET) . ", function(data) {
			$(document).ready(function() {
				$(\"#FBPost\").html(data);
			});
		});
	});
};

(function() {
	var e = document.createElement('script');
	e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
	e.async = true;
	document.getElementById('fb-root').appendChild(e);
}());
</script>
";


if(!$proceed) {
	exit;
}

/* Do we need to edit the FB post */
$post_id = !empty($_GET['fb_id']) ? explode(',', $_GET['fb_id']) : array();
$fieldname = !empty($_GET['fieldname']) ? $_GET['fieldname'] : '';
$message = strip_tags(@$_GET['message']);
$picture = $_GET['picture'];
$link = !empty($_GET['link']) ? strip_tags(@$_POST['link']) : $Config -> get('FB_DEFAULT_URL');
// Name of the link
$name = strip_tags(@$_GET['name']);
// Caption of the link (appears under the link name)
$caption = strip_tags(@$_GET['caption']);

$order   = array("\r\n", "\n", "\r");
$replace = '';
$description = str_replace($order, $replace, strip_tags(@$_GET['description']));

/*$description = str_replace("\n", "", strip_tags(@$_GET['description']));*/

$strHtmlPreview = '
<h1>Aperçu du message</h1>

<div class="Facebook Post">
	<div class="Message">
	 ' . $message . '
	</div>
	<div class="Picture">
		<img src="' . $pictureUrl . '" />
	</div>
	<div class="LinkInfos">
		<div class="Link">
			<a href="' . $link . '">' . $name . '</a>
		</div>
		<div class="Caption">
		' . $caption . '
		</div>
		<div class="Description">
		' . $description . '
		</div>
	</div>
</div>';

$tAccountToken = array();
try {
	$accounts = $facebook -> api('/me/accounts', 'GET');
	foreach($accounts['data'] as $account)
		$tAccountToken[$account['id']] = $account['access_token'];	
		
} Catch (Exception $e) {
	errorlog($e);	
}		

$strHtmlPostTo = '
<h3>Poster sur le mur de :</h3>
<ul>';

try {

for($i = 1; $i < 6; $i++) {
	if($id = $Config -> get('FB_ID_' . $i)) {
	
		if(empty($tAccountToken[$id]))
			$tAccountToken[$id] = $_fbSession['access_token'];
		
		$infos = $facebook -> api('/' . $id, 'GET');
		
		$strHtmlPostTo .= '
		<li>
			<table>
				<tr>
					<td rowspan="3" class="Picture">
						<a href="' . $infos['link'] . '" target="_blank">
			
							' . (empty($infos['picture']) ? '<fb:profile-pic uid="' . $id . '"></fb:profile-pic>' : '<img src="' . $infos['picture'] . '" alt="' . $infos['name'] . '" />') . '
						</a>
					</td>
					<td>
						<span>' . $infos['name'] . '</span>
					</td>
				</tr>
				<tr>
					<td>
						<div class="Posted Helper">
							' . (!empty($post_id[$i - 1]) ? 'Cet article a déjà été posté sur cette page' : 'Cet article n\'a pas encore été posté sur cette page') . '
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<input type="submit" class="Send" value="Poster sur cette page" data-fb-page-id="' . $id . '" data-fb-access-token="' . $tAccountToken[$id] . '" />		
					</td>
				</tr>
			</table>
			<input type="hidden" name="' . $fieldname . '[]" value="' . $post_id[$i - 1] . '" />
		</li>
		';
	}
}	

} Catch(Exception $e) {
	errorlog($e);
}

$strHtmlPostTo .= '</ul>';
echo $strHtmlPostTo;

?>

<script language="javascript" type="text/javascript">

	$(document).ready(function() {
	
		$(".Send").bind('click', function(e) {
		
			var $li = $(this).parent().parent().parent().parent().parent();
			e.preventDefault();
			
			var that = this;
			var pageId = $(this).data('fb-page-id');
			var accessToken = $(this).data('fb-access-token');
			
			var data = {
		      <?php echo 'link: \'' . str_replace("'", "\'", $link) . '\''; ?>
		      <?php echo !empty($name) ? ', name: \'' . str_replace("'", "\'", $name) . '\'' : ''; ?>
		      <?php echo !empty($picture) ? ', picture: \'' . str_replace("'", "\'", $picture) . '\'' : ''; ?>
		      <?php echo !empty($cation) ? ', caption: \'' . str_replace("'", "\'", $caption) . '\'' : ''; ?>
		      <?php echo !empty($description) ? ', description: \'' . str_replace("'", "\'", $description) . '\'' : ''; ?>
		      <?php echo !empty($message) ? ', message: \'' . str_replace("'", "\'", $message) . '\'' : ''; ?>
			  ,pageId: pageId,
			  access_token: accessToken,
			  post: true
			 }
			
			data.post_id = $li.children('input[type="hidden"]').val();
			
			function post() {
				$.get('./libs/ajax/fbpost.php', data, function(data) {
					$li.children('input[type="hidden"]').val(data);
					$li.overlay({
						message: 'Message posté sur Facebook',
						color: 'Green',
						mode: 'div'
					});
				});
			}
			
			if($li.children('input[type="hidden"]').val() != '') {
			
				$li.overlay({
					message: 'Ce message a déjà été posté sur Facebook. Cliquer pour écraser',
					color: 'Orange',
					mode: 'div',
					onLayerClick: post
				});
		
			} else
				post();
		});
	});

</script>