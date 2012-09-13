<?php

$params = array(
	't_galeries' => array('label' => 'Nom de la table des galeries', 'type' => 'string', 'admin' => 1, 'value' => 'plg_galeries_gal'),
	't_albums' => array('label' => 'Nom de la table des albums', 'type' => 'string', 'admin' => 1, 'value' => 'plg_galeries_alb'),
	'image_folder' => array('label' => 'Nom du dossier dans lequel stocker les images', 'type' => 'string', 'admin' => 1, 'value' => 'galeries/'),
	'thumb' => array('label' => 'Créer des miniatures', 'type' => 'checkbox', 'admin' => 1, 'value' => 1),
	'thumb_width' => array('label' => 'Largeur max. des miniatures', 'type' => 'numeric', 'admin' => 0, 'value' => 300),
	'thumb_height' => array('label' => 'Hauteur max. des miniatures', 'type' => 'numeric', 'admin' => 0, 'value' => 100),
	'img_width' => array('label' => 'Largeur max. des images', 'type' => 'numeric', 'admin' => 0, 'value' => 300),
	'img_height' => array('label' => 'Hauteur max. des images', 'type' => 'numeric', 'admin' => 0, 'value' => 100),
	'gal_link1_field' => array('label' => '1<sup>er</sup> lien sur la galerie', 'type' => 'select_fields', 'admin' => 1, 'value' => ''),
	'gal_link1_text' => array('label' => 'Texte du 1<sup>er</sup> lien', 'type' => 'string', 'admin' => 1, 'value' => ''),
	'gal_link2_field' => array('label' => '2<sup>ème</sup> lien sur la galerie', 'type' => 'select_fields', 'admin' => 1, 'value' => ''),
	'gal_link2_text' => array('label' => 'Texte du 2<sup>ème</sup> lien', 'type' => 'string', 'admin' => 1, 'value' => ''),
	'alb_link1_field' => array('label' => '1<sup>er</sup> lien sur la galerie', 'type' => 'select_fields', 'admin' => 1, 'value' => ''),
	'alb_link1_text' => array('label' => 'Texte du 1<sup>er</sup> lien', 'type' => 'string', 'admin' => 1, 'value' => ''),
	'alb_link2_field' => array('label' => '2<sup>ème</sup> lien sur la galerie', 'type' => 'select_fields', 'admin' => 1, 'value' => ''),
	'alb_link2_text' => array('label' => 'Texte du 2<sup>ème</sup> lien', 'type' => 'string', 'admin' => 1, 'value' => ''),
	'facebook' => array('label' => 'Synchroniser avec Facebook', 'type' => 'checkbox', 'admin' => 1, 'value' => 1)
);

return $params;

?>