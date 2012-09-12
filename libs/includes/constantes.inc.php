<?php
/*********************************************************************
|	Nom : 		Constantes.php
|	Auteur : 	Cristian Muller
|	Date : 		13.08.07
|	Société :	Pi-com
|---------------------------------------------------------------------
|	But : 	Contient les constantes du site
/********************************************************************/

/* Version */
define('VERSION', '1.9.2');


/***********************/
/* Défini les dossiers */
/***********************/

define('FOLDER_LIBS', 'libs/');
define('FOLDER_FILES', 'files/');
define('FOLDER_INSTANCE', 'instance/');
define('FOLDER_CONFIG', 'config/');
define('FOLDER_PAGES', 'pages/');
define('FOLDER_PLUGINS', 'plugins/');

/* Dossiers spécifies à libs */
define('FOLDER_LIBS_CLASS', 'class/');
define('FOLDER_LIBS_INCLUDES', 'includes/');
define('FOLDER_LIBS_CONFIG', 'config/');
define('FOLDER_LIBS_JQUERY', 'jquery/');
define('FOLDER_LIBS_JAVASCRIPT', 'js/');

define('FOLDER_LIBS_CLASS_FIELDS', 'fields/');

/* Dossiers spécifies à files */
define('FOLDER_FILES_AJAX', 'ajax/');
define('FOLDER_FILES_MODULES', 'modules/');
define('FOLDER_FILES_OPTIONS', 'options/');
define('FOLDER_FILES_PLUGINS', 'plugins/');
define('FOLDER_FILES_RUBRIQUES', 'rubriques/');
define('FOLDER_FILES_USERS', 'users/');

define('FTP_PUBLIC_FOLDER', 'D:/Documents/Serveur/pieasy/');

define('FOLDER_UPLOAD', 'instance/');
define('FOLDER_UPLOAD_MODULES', FOLDER_UPLOAD . 'modules/');
define('FOLDER_UPLOAD_GALERIES', FOLDER_UPLOAD . 'galeries/');
define('FOLDER_UPLOAD_TEMP', FOLDER_UPLOAD . 'temp/');
define('FOLDER_UPLOAD_MEDIAS', FOLDER_UPLOAD . 'medias/');

define('SECURITY_SALT', 'f98hG67d92z876UAdtC');
define('SESSION_EXPIRACY', 3600);

/****************************/
/* Défini les nom de tables */
/****************************/

define('T_CFG_MODULE',				'config_modules');
define('T_CFG_FIELD', 				'config_fields');
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



$fieldTypeList = array(
				  
	'Général' => array(
		'string' => 'Chaine de caractères',
		'textarea' => 'Texte',
		'date' => 'Date',
		'numeric' => 'Nombre'
	),
	
	'Média' => array(
		'picture' => 'Image',
		'video' => 'Vidéo',
		'file' => 'Fichier'			 
	),
	
	'Contact' => array(
		'mail' => 'E-mail',
		'link' => 'Adresse URL'
	),
					  
	'Listes à choix' => array(
		'idx' => 'Lien sur un module',
		'enum' => 'Enumération'
	),
	
	'Divers' => array(
		'color' => 'Couleur'
	)
				  
);

?>