<?php


class Plugin extends PluginController {
	
		
	const STATUS_SUBSCRIBED = 'INSCRIT_EVENEMENT';
	const STATUS_PARTICIPATE = 'INSCRIT_DATE_EVENEMENT';
	const STATUS_PARTICIPATED = 'PARTICIPE_EVENEMENT';
	const STATUS_PAID = 'PAYE_INSCRIPTION';
	const STATUS_WAIT_PAYEMENT = 'ATTENTE_PAIEMENT';
	const STATUS_CONFIRMED = 'PRESENCE_CONFIRMEE';
	const WAIT_CONFIRMATION = 'ATTENTE_CONFIRMATION';
	const UNSUBSCRIBED = 'DESINSCRIT';
		
		
	public $id, $params;
	
	public function __construct($id) {
		$this -> id = $id;
		$this -> params = $this -> getParams($id);	
	}
	
	public function run($id) {
		
		global $_baseUrl;
		
		Instance::getInstance('Page') -> addJs($_baseUrl . '/plugins/affinites_handler/js/scripts.js');
		Instance::getInstance('Page') -> addJs($_baseUrl . '/plugins/affinites_handler/js/dynatree-1.2.0/jquery.dynatree.min.js');
		Instance::getInstance('Page') -> addCss($_baseUrl . '/plugins/affinites_handler/js/dynatree-1.2.0/skin-vista/ui.dynatree.css');		
		Instance::getInstance('Page') -> addCss($_baseUrl . '/plugins/affinites_handler/style.css');		
		Instance::getInstance('Page') -> addNavigation('Gestion des inscriptions');
		
		if(($export = Instance::getInstance('Navigation') -> nav('export')) != false) {
			
			$this -> export($export);
		}
		
		$strHtml = NULL;
		$strHtml .= '<div id="AffinitesTree"></div>';
		$strHtml .= '<div id="AffinitesDetailsSubscription"></div>';
		$strHtml .= '<div class="Spacer"></div>';
		
		return $strHtml;
	}


	public function getStrState($strModule) {
		
		switch($strModule) {
			
			
			case self::STATUS_SUBSCRIBED:
				return 'Inscrit à la date de l\'événenement';
				break;
			case self::STATUS_PARTICIPATE:
				return 'Participe à l\'événenement';
				break;
			case self::STATUS_PARTICIPATED:
					return 'A participé à l\'événement';
				break;
			case self::STATUS_PAID:
					return 'A payé son inscription';
				break;
			case self::STATUS_WAIT_PAYEMENT:
					return 'En attente de paiement';
				break;
			case self::STATUS_CONFIRMED:
					return 'Participation confirmée';
				break;
			case self::WAIT_CONFIRMATION:
					return 'En attente';
				break;
			case self::UNSUBSCRIBED:
					return 'Désinscrit';
				break;
		}
	}
	
	public function export($idMember) {
		
		
		$Module = Module::buildFromId($this -> params['module_users']['value']);
		
		$getData = new GetData();
		$getData -> setModule($Module);
		$getData -> setWhere('actif', '1', '=');
		$getData -> setWhere('id', (int) $idMember, '=');
		$getData -> get();	
		$entries = $getData -> filter();
		
		$strSql = current($entries);
//		print_r($entries);
		try {
			$pdf = new PDFlib();
			if(!$pdf -> begin_document("",""))
				throw new PDFlibException("Error creating PDF document. ". $pdf -> get_errmsg());

			$pdf -> set_info("Creator", "Pi-Easy");
			$pdf -> set_info("Author", "Norman Pellet for Pi-Com.ch");
			$pdf -> set_info("Title", "Exportation du membre");
			$pdf -> begin_page_ext(595, 842, "");
			
			
			$photo = realpath("../../upload/medias/modules/4_users/photo_img/" . $strSql['photo']);
    		$image = $pdf -> load_image("jpeg", $photo, "");
    		if (!$image) { die("Error: " . $pdf -> get_errmsg()); } 
   
    		$pdf -> fit_image($image, 380, 650, "boxsize {200 200} fitmethod meet");
    		$pdf -> close_image($image);
	
			$normal = $pdf -> load_font("Helvetica", "winansi", "");
			$bold = $pdf -> load_font("Helvetica-Bold", "winansi", "");
			
			$pdf -> setfont($bold, 16);
			$pdf -> set_text_pos(50, 800);
			$pdf -> show(utf8_decode($strSql['firstname'] . ' ' . $strSql['lastname']));
			
			$strY = 760;
			$increment = -15;
			
			$pdf -> setfont($bold, 8);
			$pdf -> set_text_pos(50, $strY);
			
			$pdf -> show('Sexe');
			$pdf -> setfont($normal, 8);
			$pdf -> set_text_pos(120, $strY);
			$pdf -> show($strSql['gender'] == 'male' ? 'Homme' : 'Femme');
			
			$strY += $increment;
			
			
			$pdf -> setfont($bold, 8);
			$pdf -> set_text_pos(50, $strY);
			$pdf -> show('Age');
			
			$pdf -> setfont($normal, 8);
			$pdf -> set_text_pos(120, $strY);
			$pdf -> show($strSql['age'] . " ans");
			
			$strY += $increment;
			
			
			
			$pdf -> setfont($bold, 8);
			$pdf -> set_text_pos(50, $strY);
			$pdf -> show(utf8_decode('Localité'));
			
			$pdf -> setfont($normal, 8);
			$pdf -> set_text_pos(120, $strY);
			$pdf -> show($strSql['place']);
			
			$strY += $increment;
			
						
			
			$pdf -> setfont($bold, 8);
			$pdf -> set_text_pos(50, $strY);
			$pdf -> show('E-mail');
			
			$pdf -> setfont($normal, 8);
			$pdf -> set_text_pos(120, $strY);
			$pdf -> show($strSql['email']);
			
			$strY += $increment;
			$strY -= 40;
			
			for($i = 1; $i < 14; $i++) {
				
				
				$qName = $this -> getQName($i);
					
				$pdf -> setfont($bold, 8);
				$pdf -> set_text_pos(50, $strY);
				$pdf -> show(utf8_decode($qName));
				
				$val = explode(',', $strSql['q' . $i]);
				
				if($i == 1 && $val[0] == 'v15')
					$qVals = array($strSql['q1_1']);
				else
					$qVals = $this -> getQVal($i, $val);
				
				$strY -= 13;
				
				foreach($qVals as $val) {
					$pdf -> setfont($normal, 8);
					$pdf -> set_text_pos(50, $strY);
					$pdf -> show(utf8_decode($val));
					$strY -= 10;
				}
				
				$strY -= 15;
			}
					
			
			$pdf -> end_page_ext("");
			$pdf -> end_document("");
			$buffer = $pdf -> get_buffer();
			$len = strlen($buffer);
			
			header("Content-type: application/pdf");
			header("Content-Length: $len");
   			header("Content-Disposition: inline; filename=membre_dump.pdf");
 			echo $buffer;

		} catch (PDFlibException $e){
			echo 'Error Number:'.$e->get_errnum()."n";
			echo 'Error Message:'.$e->get_errmsg();
	   		exit();
		}
	}

	private function getQName($id) {
		
		$_tArray = array(
			1 => '1. Ma profession',
			2 => '2. Comment vous percevez-vous',
			3 => '3. A quoi attachez vous le plus d\'importance',
			4 => '4. Pour décompresser, j\'ai besoin de',
			5 => '5. Dans quel genre d\'endroit souhaiteriez-vous idéalement habiter',
			6 => '6. Quels sont vos loisirs',
			7 => '7. Etes-vous sportif',
			8 => '8. Pour vous, que représente la vie à deux',
			9 => '9. Qu\'attendez-vous d\'une nouvelle relation',
			10 => '10.	Aujourd\'hui, quels sont vos désires face à l\'amour',
			11 => '11.	Pour vivre en harmonie avec une personne, j\'apprécie',
			12 => '12.	Pour moi, que représente l\'amour',
			13 => '13.	Ma philosophie de la vie'
		);
		
		return $_tArray[$id];
	}


	private function getQVal($id, $vals) {
		$array = array(

			1 => array(
				'Dirigeants et cadres d\'entreprise ',
				'Spécialistes exerçant des professions intellectuelles et scientifiques ',
				'Employés de type administratif ',
				'Education &amp; Enseignement',
				'Professions de la santé',
				'Marketing, commercial, personnel des services, vendeurs de magasin ',
				'Hôtellerie, Restauration, Tourisme ',
				'Agriculteurs et travailleurs qualifiés dans les domaines de l\'agriculture ',
				'Bâtiment et construction',
				'Ouvriers ',
				'Transports, Conducteurs d\'installations et de machines, ouvriers de l\'assemblage ',
				'Ouvriers et employés non-qualifiés',
				'Armée (militaires) ',
				'Retraité - Sans emploi',
				'Autre'
			),
			
			2 => array(
				'Aimant la vie est sachant en profiter pleinement',
				'Respectueux/se des valeurs',
				'Aimant vivre à son rythme et faire ce qu\'il me plait',
				'N\'aimant pas les surprises et les imprévus'
			),
			
			3 => array(
			
				'La famille, les amis ',
				'L\'amour, le partage',
				'Ambitions professionnelles',
				'Ma satisfaction personnelle'
			),
			
			4 => array(
			
				'Sortir, faire la fête',
				'Rencontrer les personnes que j\'aime',
				'Une balade dans le calme',
				'Fuir sur une île déserte'
			),
			
			5 => array(
			
				'Une grande ville',
				'Petite ville ou village',
				'Au calme, à la campagne',
				'C\'est égal, je me sens bien partout'
			),
			
			6 => array(
			
				'La lecture',
				'Le sport ',
				'Les balades',
				'Les jeux de société ',
				'Se laisser vivre ',
				'Sortir',
				'Rencontrer ses amis ',
				'Shopping ',
				'L\'art',
				'Autres'
			),
			
			7 => array(
				
				'Oui',
				'Non',
				'Occasionnellement',
				'Pour moi, c\'est indispensable'
			),
			
			8 => array(
				
				'L\'échange affectif',
				'Financièrement, la vie est plus facile à deux',
				'Ne pas vieillir seul(e)',
				'Avoir des intérêts communs',
				'Veuillez remplir au moins une réponse'
			),
			
			9 => array(
				
				'Une relation fusionnelle  ',
				'Une vie à deux dans le respect et l\'harmonie',
				'Une nouvelle vie à définir',
				'Garder son indépendance, partager les bons moments'
			),
			
			10 => array(
				
				'L\'amour avec un grand A',
				'Une relation saine est durable',
				'Avoir un/e partenaire tout en gardant mon indépendance',
				'Construire un avenir à deux'
			),
			
			11 => array(
			
				'Une relation exclusive',
				'L\'esprit de famille ',
				'Une vie sociale bien remplie',
				'Le goût de l\'aventure'
			),
			
			12 => array(
			
				'Le partage et la complicité',
				'La confiance et les sentiments ',
				'Le respect et la liberté de chacun',
				'Pas grand chose pour le moment'
			),
			
			13 => array(
				'La spiritualité en fait partie ',
				'Le respect de chacun',
				'Vivre au jour le jour',
				'Sans attaches'
			)
		);
		
		$q = $array[$id];
		$toReturn = array();
		foreach($vals as $val) {
			
			$valId = str_replace("v", "", $val);
			$toReturn[] = $q[$valId - 1];
		}
		
		return $toReturn;
	}

}

?>