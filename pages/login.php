<?php

	if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

	global $_baseUrl;
	Instance::getInstance('Page') -> addCss($_baseUrl . FOLDER_DESIGN . 'login.css');
?>
<div id="Logo"></div>
<?php 
	$Message = Instance::getInstance('Message');
	$errors = $Message -> nbMessages();
	
?>
<div id="ZoneLogin" <?php if($errors > 0) echo ' class="Error"'; ?>>
<h2>Merci de bien vouloir vous identifier</h2>
<?php
	if($errors > 0) 
	echo '<div id="LoginError">' . $Message -> display() . '</div>';
?>
<form method="post" action="<?php echo Instance::getInstance('Navigation') -> url(array('login' => 1)); ?>">
<div id="Login">
    <p><label>Nom d'utilisateur</label><input type="text" name="username" /></p>
    <p><label>Mot de passe</label><input type="password" name="password" /></p>
    <div id="LoginBottom">
		<p class="Submit">Mot de passe ou login oublié(s) ? <input type="submit" value="Login" /></p> 
    </div>
</div>
</form>
</div>
<div id="Footer">
© 2011 Pi-Com - Tous droits reservés - Pi-easy v2.0
</div>

<script>
<!--

if($.browser.msie) {
$(document).ready(function() {
	$.colorbox({
	html: '\
	<div style="text-align: left">\
	<h3>Mise à jour du navigateur</h3>\
	<p>Nos services détectent que vous utilisez actuellement une version d\'Internet Explorer. Ce navigateur est malheureusement obsolète et n\'utilise aucune des technologies modernes proposées par les autres navigateurs présents sur le marché.</p>\
	<p>Il existe aujourd\'hui plusieurs navigateurs gratuits, intégrant les protocoles de <strong>sécurité</strong> les plus poussés, tout en respectant la <strong>vie privée</strong> et gratuit.</p>\
	<p>Afin de profiter pleinement des fonctionnalités que le Pi-Easy propose, nous vous prions de bien vouloir installer un navigateur récent.</p>\
	<p>Chez Pi-Com, nous conseillons vivement les navigateurs <strong>Mozilla Firefox</strong> et <strong>Google Chrome</strong>.</p>\
	<p>Vous pouvez trouver ci-dessous des liens vers leur site afin de les télécharger</p>\
	<p>\
		<a href="http://www.google.com/chrome/?hl=fr" target="_blank">\
			<img src="http://www.google.com/intl/fr/images/logos/chrome_logo.gif" />\
		</a>\
	</p>\
	<p>\
		<a href="http://www.mozilla.com/fr/firefox/" target="_blank">\
			<img src="http://static.mozilla.com/mozeu/images/firefox-wordmark-horizontal_small.png" />\
		</a>\
	</p>\
	</div>\
	', innerHeight: '500px', innerWidth: '60%'});
});
	
}

-->
</script>