<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

$strHtml = '';
$facebook = Instance::getInstance('Facebook');
if(empty($facebook))
	return;
	
$_fbSession = $facebook -> getSession();

$strHtml .= "
<div id=\"fb-root\"></div>
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
	FB.Event.subscribe('auth.login', function() {
		window.location.reload();
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

$me = null;
$_requiredPerms = array('publish_stream', 'create_event', 'offline_access', 'manage_pages');

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
//	echo '<pre>'; print_r($me); echo '</pre>';
	$strHtml .= '
	<fb:profile-pic size="square" width="21" height="21" uid="' . $uid . '"></fb:profile-pic>
	<span>&nbsp; Facebook :  <strong>' . $me['name'] . '</strong></span>';
	} else {
	$strHtml .= '<fb:login-button perms="' . implode(',', $_requiredPerms) . '"></fb:login-button>';	
}


return $strHtml;
?>