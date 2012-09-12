<?php

if(!defined('IN_PIEASY') && !defined('IN_ADMIN'))
	die('Tentative de hacking détectée');

function url($params, $reset = false) {
	global $Navigation;	
	return $Navigation -> url($params, $reset);
}

function FormatterPoids($poids) {
	$poids = $poids / 1024;
	
	if($poids > 1024) {
		return round($poids / 102.4) / 10 . 'Mo';
	}
	return round($poids * 10) / 10 . 'Ko';
}

function FormatterDate($date) {
	return date('d', $date) . '/' . date('m', $date) . '/' . date('Y', $date);	
}

function FormatterChaine($strChaine)
{
}


function ObtenirNomImageFichier($strNomFichier) {
	/* Récupère l'extension du fichier */
	$strExtension = strrchr($strNomFichier, '.');
	
	/* Selon l'extension, créé une variable avec le nom du logo du type de fichier */
	switch($strExtension) {
		default:
			/* Par défaut, indique une image polyvalente */
			$strFileLogo = 'file';
			break;
		
		case '.mp3':
			$strFileLogo = 'mp3';
			break;
		
		case '.pdf':
			$strFileLogo = 'pdf';
			break;
		
		case '.doc':
		case '.dot':
			$strFileLogo = 'doc';
			break;
		
		case '.xls':
		case '.xlt':
			$strFileLogo = 'xls';
			break;
		
		case '.txt':
			$strFileLogo = 'txt';
			break;
		
		case '.ppt':
		case '.pps':
			$strFileLogo = 'ppt';
			break;
	}
	return $strFileLogo;
}


?>