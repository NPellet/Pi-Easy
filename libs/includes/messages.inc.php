<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

$_tMessages = array();
	
$_tMessages['mod'] = array(
						   
	1 => array(
		1 => 'Le module a correctement été sauvegardé'
	),
	0 => array(
		1 => 'Un autre module possédant le même nom est déjà existant'		
	)
);

$_tMessages['field'] = array(
	0 => array(
		1 => 'Un autre champ possédant le même nom est déjà existant'
	),
	1 => array(
		1 => 'Le champ a correctement été sauvegardé'
	)						 
);


$_tMessages['grpfields'] = array(
	0 => array(
	
	),
	
	1 => array(
		1 => 'Le groupe a correctement été sauvegardé'
	)
);

$_tMessages['cat'] = array(
	1 => array(
		1 => 'La catégorie a correctement été sauvegardée'
	)						 
);

$_tMessages['entry'] = array(
	1 => array(
		1 => 'L\'entrée a correctement été sauvegardée'
	)						 
);

$_tMessages['right'] = array(
	0 => array(
		1 => 'Vous ne possédez pas les droits pour éditer des entrées de ce module',
		2 => 'Vous ne possédez pas les droits pour ajouter des entrées dans ce module',
		3 => 'Vous ne possédez pas les droits pour supprimer des entrées dans ce module',
		4 => 'Des droits d\'administrateur sont requis pour entrer ici'
	)						 
);

$_tMessages['file'] = array(
	0 => array(
		1 => 'Le fichier ne peut être lu',
		2 => 'Erreur lors de l\'écriture du fichier',
		3 => 'Impossible d\'obtenir le hash du fichier',
		4 => 'Impossible de déplacer le fichier temporaire',
		5 => 'Impossible de renommer le fichier ou le dossier',
		6 => 'Le dossier source n\'existe pas',
		7 => 'Impossible de lire le dossier source',
		8 => 'Impossible de copier le dossier',
		9 => 'Le fichier a supprimer n\'existe pas',
		10 => 'Ce fichier ne peut être supprimé',
		11 => 'Impossible d\'obtenir le type MIME du fichier'
	)						 
);


$_tMessages['config'] = array(

	1 => array(
		1 => 'La configuration a correctement été sauvegardée',
		2 => 'La clé de configuration a correctement été sauvegardée'
	),
	
	0 => array(
		1 => 'Erreur de sauvegarde. Vérifier la version du Pi-Easy (champ group rajouté)' 
	)
);

$_tMessages['sql'] = array(
						   
	0 => array(
		1 => 'La base de données est introuvable.',
		2 => 'Connexion à la base de donnée refusée. Veuillez vérifier les données de connexion'
	)
);


$_tMessages['login'] = array(
	0 => array(
		1 => 'Le nom d\'utilisateur ou le mot de passe que vous avez saisi est incorrect.',
		2 => 'Impossible de récupérer les informations de la base de données. Merci de contacter un administrateur',
		3 => 'Vous avez essayé de vous connecter plus de 5 fois sans succès. Votre compte a été temporairement bloqué. Merci de réessayer dans quelques minutes ou contacter un administrateur'
			   
	)
);


$_tMessages['install'] = array(
						   
	1 => array(
		1 => 'Installation du Pi-Easy réussie !'
	),
	0 => array(
		1 => 'Erreur lors de l\'installation du Pi-Easy. Impossible de créer les tables',
		2 => 'Erreur lors de l\'installation du Pi-Easy. Impossible d\'écrire dans le fichier de configuration',
		3 => 'Attention, les droits d\'accès sur le dossier d\'instance (' . FOLDER_UPLOAD . ') ne sont pas correctement définis',		
		4 => 'Attention, les droits d\'accès sur le dossier de configuration (' . FOLDER_LIBS_CONFIG . ') ne sont pas correctement définis',
		5 => 'Erreur lors de l\'installation du Pi-Easy. Impossible d\'enregistrer les données de configuration par défaut',
		6 => 'Erreur lors de l\'installation du Pi-Easy. Impossible de créer le dossier d\'upload principal',
		7 => 'Erreur lors de l\'installation du Pi-Easy. Impossible de créer un des dossiers d\'upload'
	),
);


?>