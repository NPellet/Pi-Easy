<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class ListData extends Security {
	
	private $data = NULL, $Module, $nbEntries, $start = 0;
	public $getData;
	
	public function __construct($Module) {
		$this -> Module = $Module;
		$this -> nbEntries = Instance::getInstance('Config') -> get('nbEntriesParPage');
		$this -> getData = new GetData();
		if($Module -> isSortable())
			$this -> getData -> setOrder(FIELD_ORDER, true);
		else
			$this -> getData -> setOrder(FIELD_DATE_ADDED, false);
		 
	}
	
	public function get() {

		$this -> getData -> setModule($this -> Module);
		if($this -> nbEntries > 0)
			$this -> getData -> filter['limit'] = array($this -> start, $this -> start + $this -> nbEntries);
		$this -> getData -> setWhere('actif', '1', '=');
		$this -> data = $this -> getData -> get();
	}
	
	public function setPage($page) {
		$this -> start = ($page - 1) * $this -> nbEntries;
	}
	
	public function setNbEntries($nbEntries) {
		$this -> nbEntries = $nbEntries;
	}
	
	public function displayTable() {
		
		if($this -> data == NULL)
			$this -> get();
			
		$strHtml = NULL;
		$tRubriques = $this -> Module -> getRubriques();
		$Rubrique = new Rubrique(array('id' => 0, 'idx_module' => $this -> Module -> getId(), 'order' => '1'));
		$Rubrique -> setLabel(NULL, $this -> Module -> hasRubriques() ? '' : '');
		$tRubriques[] = $Rubrique;
		
		foreach($tRubriques as $Rubrique) {
		
			if($this -> nbEntries > 0)
				$this -> getData -> filter['limit'] = array($this -> start, $this -> start + $this -> nbEntries);
		
			$this -> getData -> filter['rubrique'] = $Rubrique -> getId();
			
			$rLabel = $Rubrique -> getLabel();
			if($rLabel != NULL)
				$strHtml .= '<h2>' . $rLabel . '</h2>';
			
			$tButtons = Instance::getInstance('Page') -> getButtons();
			if(count($tButtons) > 0) {
				$strHtml .= '<ul class="Buttons Actions">';
				foreach($tButtons as $Button)
					$strHtml .= $Button -> display(true);	
				$strHtml .= '</ul><div class="Spacer"></div>';
			}
			
			$strHtml .= '
			<table class="Wrapper Entries" cellpadding="0" cellspacing="0" rel="' . $Rubrique -> getId() . '">
			<tr>
				<td class="ListEntries">
					<table class="Data Entries" cellpadding="0" cellspacing="0" rel="' . $Rubrique -> getId() . '">
					<thead>
					<tr>
					';
					//<th colspan="2">Actions</th>
					$Fields = $this -> Module -> getFields();
					$AddDate = new FieldDate();
					$AddDate -> setLabel('Date d\'ajout');
					$AddDate -> setName(FIELD_DATE_ADDED);
					$AddDate -> setPriority(1);
					$Fields[] = $AddDate;
					
					if(is_array($Fields)) {
						$firstcol = true;
						foreach($Fields as $Field) {

							if($Field -> getPriority() > 2)
								continue;
					
							if($firstcol == true) {
								$firstcol = false;
								$tdClass = 'FirstCol';
							} else
								$tdClass = '';
						
							$strHtml .= '
							<th class="Sortable ' . $tdClass . '" rel="' . $Field -> formName() . '">
								' . $Field -> getLabel() . '
							</th>';
						}
					}
					$strHtml .= '<!--<th colspan="2">Exportation</th>--></tr></thead><tbody>';
					$strHtml .= $this -> displayInnerTable();
					$strHtml .= '</tbody></table>';
										
					$nbEntries = $this -> getData -> filter['limit'] = NULL;
					$nbEntries = count($this -> getData -> filter());
					
					$cPage = $this -> start / $this -> nbEntries + 1;
					$tPages = ceil($nbEntries / $this -> nbEntries);
					if($nbEntries == 0)
						$tPages = 1;
					
					$strHtml .= $this -> pagination($cPage, $tPages);
					$strHtml .= '
					</td>
					<td class="OverviewEntry">
						<div><div class="Overview">Passez la souris sur une entrée pour voir les détails</div></div>
					</td>
				</tr>
			</table>
			';
			
		}
		
		return $strHtml;
	}
	
	
	public function displayInnerTable() {
		$strHtml = NULL;
		
		$entries = $this -> getData -> filter();
		$Fields = $this -> Module -> getFields();
		
		$AddDate = new FieldDate();
		$AddDate -> setLabel('Date d\'ajout');
		$AddDate -> setName(FIELD_DATE_ADDED);
		$AddDate -> setPriority(1);
		$Fields[] = $AddDate;
		
		$data = array();
		$i = 0;
		
		$higher = false;
							
		foreach($Fields as $Field) {
			if($Field -> getPriority() > 2)
				continue;
			
			if($Field -> getType() == 'picture' || $Field -> getType() == 'file' || $Field -> getType() == 'textarea') {
				$higher = true;
				break;
			}
		}
		
		if(count($entries) > 0) 
		foreach($entries as $entry) {
			$i++;
			$Entry = new Entry($this -> Module, $entry['id'], $entry);
			
			$strHtml .= '
			<tr rel="' . $Entry -> cfg['id'] . '" class="' . ($higher ? 'Higher ' : NULL) . ($i % 2 == 0 ? 'Even' : 'Odd') . '">
			';
			
			if(is_array($Fields)) {
				
				$firstcol = true;
				foreach($Fields as $Field) {

					$Field -> setFirstLang();
					$value = $Entry -> get($Field -> getName(), $Field -> getLang());

					$data[$Entry -> cfg['id']][$Field -> getLabel()] = $Field -> display($value['treat']);
					if($Field -> getPriority() > 2)
						continue;
					if($firstcol == true) {
						$firstcol = false;
						$tdClass = 'FirstCol';
					} else
						$tdClass = '';
						
					$strHtml .= '<td class="' . $tdClass . '"><div>' . $Field -> display($value['treat']) . '</div></td>';
				}
			}
			
			//$strHtml .= '<td><a href="export/pdf-' . $this -> Module -> getId() . '-' . $entry['id'] . '.html">Balh</a></td><td>Blah</td>';
			$strHtml .= '</tr>';
		} else {
			$nbCols = 2;
			if(is_array($Fields))
				foreach($Fields as $Field)
					if($Field -> getPriority() <= 2) $nbCols++;
			$strHtml .= '<tr><td colspan="' . $nbCols . '" class="FirstCol Error">Aucune entrée ici</td></tr>';
		}

		$strHtml .= '
<script language="javascript" type="text/javascript">
<!--
	if(table == undefined)
		var table = [];
		
	table[' . $this -> getData -> filter['rubrique'] . '] = ' . json_encode($data) . ';
	
	$("table[rel=' . $this -> getData -> filter['rubrique'] . '].Data.Entries tbody tr[rel]").hover(function() {
		$(this).parent().children().removeClass("Selected");
		$(this).addClass("Selected");
		var entry = table[' . $this -> getData -> filter['rubrique'] . '][$(this).attr(\'rel\')];
		var html = "<div class=\"Overview\">";
		
		for(var i in entry) {
			html += "<div class=\"Label\">" + i + "</div><div class=\"Content\">" + ((entry[i] !== false && entry[i] != undefined) ? entry[i] : \'\') + "</div>";
		}
		
		html += "</div>";
		
		var height = $(this).height();
		var number = $(this).parent().children("tr").length;
		var margin = height * $(this).index();
		var $overlay = $(this).parent().parent().parent().parent().find(".OverviewEntry").children("div");
		$overlay.html(html).stop().animate({"margin-top": margin + "px"}, 100);
		
		findImages($overlay);
		
		if(margin + $overlay.height() >  number * height) {
			
			var newHeight = number * height - $overlay.height();
			if(newHeight < 0)
				newHeight = 0;
				
			$overlay.stop().animate({"margin-top": newHeight + "px"}, 100);
		}
		
	}, function() {
		
	});
-->
</script>
		';
		
		return $strHtml;
	}	
	
	private function pagination($c, $t) {
		$strPages = NULL;
		
		for($i = 1; $i <= $t; $i++)
			$strPages .= '<a rel="' . $i . '" ' . ($i == $c ? ' class="Selected"' : NULL) . '>' . $i . '</a> ';
		
		return '<div class="Pages">Page ' . $strPages . '<div class="Spacer"></div></div>';
	}
}

?>