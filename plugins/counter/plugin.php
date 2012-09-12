<?php

class Plugin extends PluginController {
	
	protected $id, $params;
	
	public function __construct($id) {
		global $_baseUrl;
		$this -> id = $id;

		$this -> params = $this -> getParams($id);	
	}
	
	public function run($id) {
		
		$strHtml = NULL;
		$strSql = 'SELECT * FROM `' . Sql::secure($this -> params['tablename']['value']) . '` ORDER BY `module` ASC';
		if($resSql = Sql::query($strSql)) {
			
			$mod = 0;
			$i = 0;
			while($dataSql = mysql_fetch_assoc($resSql)) {
			
				if($dataSql['module'] != $mod) {
					
					if($mod != 0)
						$strHtml .= '</table>';
					
					$mod = $dataSql['module'];
					$Module = Module::buildFromId($mod);
				
					$strHtml .= '<h2>' . $Module -> getLabel() . '</h2>
					<table cellpadding="0" cellspacing="0" class="Data Entry">
					<tr>
						<th>Nombre de clics</th>
						<th>Mode</th>';
					
					$Fields = $Module -> getFields(1);
					foreach($Fields as $Field) {
						if($Field -> getPriority() > 1)
						continue;
						
						$strHtml .= '<th>' . $Field -> getLabel() . '</th>';
					}
					
					$strHtml .= '</tr>';
					
					$Data = new GetData();
					$Data -> setModule($Module);
					$Data -> setWhere('actif', '1', '=');
					$data = $Data -> get();
				}
				
				$Entry = new Entry($Module, $dataSql['id'], $data[$dataSql['id']]);
				$i++;
				$strHtml .= '<tr' . ($i % 2 == 0 ? ' class="Even"' : ' class="Odd"') . '><td class="FirstCol">' . $dataSql['count'] . '</td><td>' . ($dataSql['method'] == 'audio' ? 'Audio' : 'Vidéo') . '</td>';
				
				foreach($Fields as $Field) {
					if($Field -> getPriority() > 1)
						continue;
				
					$value = $Entry -> get($Field -> getName(), $Field -> getLang());
					$strHtml .= '<td>' . $Field -> display($value['treat']) . '</td>';
				}
				
				$strHtml .= '</tr>';
			}
			
			
		}
		
		return $strHtml;
	}

}

?>