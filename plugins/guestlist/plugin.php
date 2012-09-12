<?php

class Plugin extends PluginController {
	
	protected $id, $params;
	
	public function __construct($id) {
		global $_baseUrl;
		$this -> id = $id;
		$this -> params = $this -> getParams($id);	
	}
	
	public function run($id) {
		global $_baseUrl;
		
		$ModuleSoiree = Module::buildFromName($this -> params['module_soiree']);
		$ModuleGuestlist = Module::buildFromName($this -> params['module_guestlist']);
		
		if(!$ModuleSoiree && !$ModuleGuestlist)
			return $this -> log('guestlist:0:1');	
		
		$FieldsGueslist = $ModuleGuestlist -> getFields();
		$toDumpGuestlist = explode(',', $this -> params['fields_to_dump_guestlist']);
		$toDumpSoiree = explode(',', $this -> params['fields_to_dump_soiree']);
		
		// On supprime les champs à ne pas dumper, on garde les bons
		foreach($Fields as $k => $Field)
			if(in_array($Field -> getName(), array_merge($toDumpGueslist, array($this -> params['order_by']))))
				$tFields[$Field -> getName()] = $Field;
		
		
		$strOrder = NULL;
		if(array_key_exists($this -> params['order_by'], $tFields))
			$strOrder = '
			ORDER BY ' . 
				$tFields[$this -> params['order_by']] -> formName()
			. ' ' . (in_array(strtolower($this -> params['order_type']), array('asc', 'desc')) ? $this -> params['order_type'] : 'ASC');
		
		$strSql = '
		SELECT
			`soiree`.`id`
			`soiree`.`date` AS soireeDate,
			`soiree`.`nom` AS soireeNom,
			`soiree`.`club` AS soireeClub,
			`guestlist`.`firstname`,
			`guestlist`.`lastname`,
			`guestlist`.`email`,
			`guestlist`.`telephone`
		FROM 
			`' . Sql::buildTable($ModuleSoiree -> getTable()) . '` `soiree`
		LEFT JOIN 
			`' . Sql::buildTable($ModuleGuestlist -> getTable())'` `guestlist`
		ON 
			`soiree`.`id` = `guestlist`.`idx_soiree`
		GROUP BY 
			`soiree`.`id`
		ORDER BY 
			`soiree`.`date` DESC,
			`guestlist`.`name` ASC
		';

		if($resSql = Sql::query($strSql))
			while($dataSql = mysql_fetch_assoc($resSql))
				$tEntries[$dataSql['id']][] = $dataSql;
		
		$soireeNom = new FieldString();
		
		foreach($tEntries as $$guestlist) {
			
			$strHtml .= '
			<table>
				<tr>
					<th colspan="2">' . 
						$soireeNom -> display($guestlist['soireeNom']) . 
					', ' . 
						$soireeClub -> display($guestlist['soireeClub']) . 
					', ' . 
						$soireeDate -> display($guestlist['soireeDate']) . 
					'</th>
				</tr>
			';
			
			foreach($guestlist as $guest)
				
			
		}
	}

?>