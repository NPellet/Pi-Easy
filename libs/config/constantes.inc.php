<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

/* Version */
define('VERSION', '2.0.2');

/***********************/
/* Défini les dossiers */
/***********************/

define('FOLDER_LIBS', 'libs/');
define('FOLDER_FILES', 'files/');
define('FOLDER_CONFIG', 'config/');
define('FOLDER_PAGES', 'pages/');
define('FOLDER_PLUGINS', 'plugins/');
define('FOLDER_DESIGN', 'design/');

/* Dossiers spécifies à libs */
define('FOLDER_LIBS_AJAX', FOLDER_LIBS . 'ajax/');
define('FOLDER_LIBS_CLASS', FOLDER_LIBS . 'class/');
define('FOLDER_LIBS_INCLUDES', FOLDER_LIBS . 'includes/');
define('FOLDER_LIBS_CONFIG', FOLDER_LIBS . 'config/');
define('FOLDER_LIBS_JQUERY', FOLDER_LIBS . 'jquery/');
define('FOLDER_LIBS_JAVASCRIPT', FOLDER_LIBS . 'js/');

define('FOLDER_LIBS_CLASS_FIELDS', 'fields/');

/* Dossiers spécifies à files */
define('FOLDER_FILES_AJAX', 'ajax/');
define('FOLDER_FILES_MODULES', 'modules/');
define('FOLDER_FILES_OPTIONS', 'options/');
define('FOLDER_FILES_PLUGINS', 'plugins/');
define('FOLDER_FILES_RUBRIQUES', 'rubriques/');
define('FOLDER_FILES_USERS', 'users/');

/*
define('FTP_PUBLIC_FOLDER', 'D:/Documents/Serveur/pieasy/');
*/

define('FOLDER_UPLOAD', 'upload/');
define('FOLDER_UPLOAD_MODULES', 'modules/');
define('FOLDER_UPLOAD_TEMP', 'temp/');
define('FOLDER_UPLOAD_MEDIAS', 'medias/');
define('FOLDER_UPLOAD_DROPBOX', 'dropbox/');


define('NOM_SITE_APPEND', 'Gestion de contenu');

define('SESSION_TRIES', 5);
define('SECURITY_SALT', 'f98hG67d92z876UAdtC');
define('SESSION_EXPIRACY', 3600);
define('BRUTEFORCE_EXPIRACY', 300);

define('IMG_MAX_WIDTH', 800);
define('IMG_MAX_HEIGHT', 600);

define('THB_MAX_WIDTH', 200);
define('THB_MAX_HEIGHT', 100);

/****************************/
/* Défini les nom de tables */
/****************************/

define('T_CFG_MODULE',				'config_modules');
define('T_CFG_FIELD', 				'config_fields');
define('T_CFG_GROUPFIELDS',			'config_group_fields');
define('T_CFG_CONFIG', 				'config');
define('T_CFG_CATEGORY',			'config_categories');
define('T_CFG_USER',				'config_users');
define('T_CFG_RUBRIQUE',			'config_rubriques');
define('T_CFG_RUBRIQUE_LANGS',		'config_rubriques_lang');
define('T_CFG_SESSIONS',			'config_sessions');
define('T_CFG_RIGHTS',				'config_rights');
define('T_CFG_OPTIONS',				'config_options');
define('T_CFG_BRUTEFORCE',			'config_bruteforce');
define('T_CFG_PLUGINS',				'config_plugins');
define('T_CFG_PLUGINS_PARAMETERS',	'config_plugins_params');

define('T_PREFIX', 					't_');
define('T_PREFIX_MODULES', 			'mod_');

define('FIELD_DATE_ADDED', 			'date_added');
define('FIELD_DATE_UPDATED', 		'date_updated');
define('FIELD_IDX_PREFIX', 			'idx_');
define('FIELD_ORDER',				'order');

$_fieldTypes = array(
				
	'Texte' => array(
		'Texte court' => 'string',
		'Texte long' => 'textarea',
		'Liste déroulante' => 'enum'
	),
	
	'Numérique' => array(
		'Case à cocher' => 'checkbox',
		'Date' => 'date',
		'Heure' => 'time',
		'Nombre' => 'numeric'
	),
	
	'Internet' => array(
		'Lien' => 'link',
		'E-mail' => 'email'
	),
	
	'Média' => array(
		'Fichier' => 'file',
		'Image' => 'picture',
		'Vidéo' => 'video',
		'MP3' => 'mp3'
	),
	
	'Pi-Easy' => array(
		'Clé étrangère' => 'idx'
	),
	
	'API externes' => array(
		'Google Map' => 'map',
		'Post facebook' => 'fbpost',
		'Event facebook' => 'fbevent'
	)
);

$_tRights = array('view', 'add', 'edit', 'remove', 'rubriques');

$_tCfg = array(
	'META_DESCRIPTION' => array('Tag META "description"', 'string', 1, ''),
	'META_KEYWORDS' => array('Tag META "keywords"', 'string', 1, ''),
	'FB_APPID' => array('ID de l\'application Facebook', 'string', 1, ''),
	'nbEntriesParPage' => array('Nombre d\'entrées par page', 'numeric', 0, 20) ,
	'LOGO' => array('Logo du site', 'picture', 0, ''),
	'FB_USE' => array('Utiliser l\'API Facebook', 'checkbox', 0, 1),
	'FB_APPID' => array('ID de l\'application Facebook', 'string', 1, ''),
	'FB_APPSECRET' => array('Clé secrète de l\'application Facebook', 'string', 1, ''),
	'FB_ID_1' => array('Identifiant Facebook 1', 'string', 0, ''),
	'FB_ID_2' => array('Identifiant Facebook 2', 'string', 0, ''),
	'FB_ID_3' => array('Identifiant Facebook 3', 'string', 0, ''),
	'FB_ID_4' => array('Identifiant Facebook 4', 'string', 0, ''),
	'FB_ID_5' => array('Identifiant Facebook 5', 'string', 0, ''),
	'FB_ID_6' => array('Identifiant Facebook 6', 'string', 0, '')
);


?>