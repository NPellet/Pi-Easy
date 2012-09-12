<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class FieldIdx extends Field {
	
	public static $data = array();
	protected $idxMultiple = false, $idxFields = array();

	public function __construct($dataSql = NULL) {
		
		parent::__construct($dataSql);
		$this -> modFields[''] = 'VARCHAR (250)';
		$this -> idxMultiple = !empty($dataSql['ext_idx_field_multiple']) ? true : false;
		$this -> idxFields = explode(',', @$dataSql['ext_idx_field']);
	}
	
	public function setIdxField($idxField) {
		$this -> idxFields = explode(',', $idxField);
	}
	
	public function setMultiple($bln) {
		$this -> idxMultiple = (bool) $bln;
	}
	
	public function configFields() {
		extract($_POST);
		$this -> idxMultiple = isset($fieldIdxMultiple) ? true : false;
		$this -> idxFields = $fieldIdxFields;

		return array(
			'ext_idx_field_multiple' => $this -> idxMultiple,
			'ext_idx_field' => implode(',', $this -> idxFields)
		);		
	}
	
	public function treat($value) { return $this -> idxMultiple && is_array($value) ? ';' . implode(';', $value) . ';' : $value; }
	public function check($value) { return false; }
	public function display($value) { 
		
		$strHtml = NULL;
		$tOptions = $this -> getData($value);
		if(count($tOptions) == 0 || !is_array($tOptions))
			return;
		
		foreach($tOptions as $option) {
			if(is_array($option)) {
				foreach($option as $theoption) {
					if($theoption != NULL)
						$strHtml .= ', ' . $theoption;
				}
			} else if ($option != NULL)
				$strHtml .= ', ' . $option;
		}
		
		return substr($strHtml, 2);
	}
	
	public function showField($value) {
	
		$tOptions = $this -> getData();
		
		if(!$this -> idxMultiple)
			$tOptions = array('0' => 'Aucun') + $tOptions;

		$class[] = 'Field';
		$class[] = 'Idx';
		$class = implode(' ', $class);
		$name = $this -> formName();
		
		return '
		<select class="' . $class . '" name="' . $name  . ($this -> idxMultiple ? '[]" multiple="multiple"' : '"') . ' title="Veuillez effectuer une sélection">
			' . Html::buildList($tOptions, explode(';', $value)) . '
		</select>';
	}
	
	
	private function getData($value = false) {
		
		$tFields = $this -> idxFields;
			
		$key = implode('.', $tFields);
		$datas = array();

		if($value !== false)
			$value = explode(';', $value);
			
		if(isset(self::$data[$this -> getId()]))
			$datas = self::$data[$this -> getId()];			
		else {
			$fField = Field::buildFromId($tFields[0]);
			$Module = $fField -> getModule();
	
			$Data = new GetData();
			$Data -> setModule($Module);
			$Data -> setWhere('actif', '1');
			$Data -> setOrder($fField -> sqlName());
		/*	if($value !== false)
				$Data -> setWhere('id', $value, 'in');	*/
			$Data -> get();
			$datas = $Data -> getData();

			self::$data[$this -> getId()] = $datas;
		}
		
		$tOptions = array();
		if(!@$this -> multiple)
			$tOptions[0] = '';
		
		if($fField) { 
			$Rubriques = $fField -> getModule() -> getRubriques();
			$Rubrique = new Rubrique(array('id' => 0, 'idx_module' => $fField -> Module -> getId(), 'order' => '1'));
			$Rubrique -> setLabel(NULL, $fField -> Module -> hasRubriques() ? 'Sans rubrique' : '');
			$Rubriques[] = $Rubrique;
			
			$tOptions[$Rubrique -> getLabel()] = array();
					
			foreach($Rubriques as $Rubrique) {
				
				foreach($datas as $data) {
					
					if($data['idx_rubrique'] != $Rubrique -> getId())
						continue;
	
					if($value == false || in_array($data['id'], $value)) {
						
						$tOptions[$Rubrique -> getLabel()][$data['id']] = NULL;		
						$i = 0;
						foreach($tFields as $idField) {
							
							$Field = Field::buildFromId($idField);
							$fieldName = $Field -> sqlName();
							$i++;
							$Field -> setFirstLang();
							
							$tOptions[$Rubrique -> getLabel()][$data['id']] .= ($i > 1 ? ', ' : '') . $Field -> display($data[$fieldName]);
						}
					}
				}
				
			}
		} else {
			
			
			$tOptions = array();
			foreach($datas as $data) {
		
				if($value == false || in_array($data['id'], $value)) {
					
					$tOptions[$data['id']] = NULL;		
					$i = 0;
					foreach($tFields as $idField) {
						$Field = Field::buildFromId($idField);
							
						$fieldName = $Field -> sqlName();
						$i++;
						$Field -> setFirstLang();
						
						$tOptions[$data['id']] .= ($i > 1 ? ', ' : '') . $data[$fieldName];
					}
				}
			}
		}

		return $tOptions;
	}
}

?>