<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class GetData extends Security {

	private $order = array(), $limit, $where = array(), $lang;
	private $data = array();
	public $filter = array();
	private $fields = array();
	private $Module;
	
	public function setModule($Module) { $this -> Module = $Module; foreach($Module -> getFields() as $field) { $this -> fields[$field -> getName()] = $field; }; }
	
	public function setOrder($field, $asc = true) {
		$this -> order = array('field' => $field, 'order' => $asc == true ? 'asc' : 'desc');
	}
	
	public function setLimit($intStart, $intStop = NULL) {
		$this -> limit = array('from' => $intStart, 'to' => $intStop);
	}
	
	public function setWhere($field, $value, $mode = '=') {
		$this -> where[] = array('field' => $field, 'mode' => $mode, 'value' => $value);	
	}
	
	public function getData() { return $this -> data; }	
	
	public function setLang($langAbr) {
		$this -> lang = $langAbr;
	}

	public function get() {

		$moduleTable = $this -> Module -> getTable();
		$strSql = 'SELECT * FROM `' . $moduleTable . '`';

		$strWhere = array();
		$strLimit = NULL;
		$strOrder = NULL;

		// WHERE
		
		foreach($this -> where as $where) {
			
			if(in_array($where['field'], array('id', 'actif'))) {
				
				$fieldName = $where['field'];
				
			} else {
				
				if(empty($this -> fields[$where['field']]))
					return false;

				$field = $this -> fields[$where['field']];

				if($field -> isMultilang() == 1) {
					
					if($this -> lang == NULL)
						return false;
					$fieldName = $where['field'] . '_' . $this -> lang;				
				} else 
					$fieldName = $this -> fields[$where['field']] -> sqlName();
				
			}
			
			$value = is_array($where['value']) ? '"' . implode('", "', $where['value']) . '"' : '"' . $where['value'] . '"';
			
			switch($where['mode']) {
			
				case '<':
					if(is_array($where['value']))
						return false;
						
					$strWhere[] = $fieldName . ' < ' . intval($value);
				break;
				
				case '>':
					
					if(is_array($where['value']))
						return false;
					
					$strWhere[] = $fieldName . ' > ' . intval($value);
					
					
				break;

				
				case 'in':
					$strWhere[] = $fieldName . ' IN(' . $value . ')';
				break;
				
				default:				
				case '=':
					if(is_array($where['value']))
						return false;
						
					$strWhere[] = $fieldName . ' = ' . $value;
			
				break;
			}
		}
		
		
		if(count($strWhere) > 0)
			$strWhere = ' WHERE ' . implode(' AND ', $strWhere);
	

		if(!empty($this -> order)/* && !empty($this -> fields[$this -> order['field']])*/) {
			
		//	$field = $this -> fields[$this -> order['field']];	
			//if($field['multilang'] == 1) {
				//if($this -> lang == NULL)
					//return false;
		//		$fieldName = $this -> order['field'] . '_' . $this -> lang;				
//			}
			
			$strOrder = ' ORDER BY `' . $this -> order['field'] . '` ' . strtoupper($this -> order['order']) . ' ';
		}
		
		// LIMIT
		if(!empty($this -> limit))
			$strLimit = ' LIMIT BY ' . $this -> limit['from'] . ($this -> limit['to'] != NULL ? ', ' . $this -> limit['to'] : NULL) . ' ';
		

		$strSql .= $strWhere . $strOrder . $strLimit;

		if($resSql = $this -> query($strSql)) {
			while($dataSql = mysql_fetch_assoc($resSql)) {
				$this -> data[$dataSql['id']] = $dataSql;
			}
			
			return $this -> data;
		}
		return false;
	}
	
	public function filter() {
		$newData = array();
		$i = 0;
	
		if(is_array($this -> data) && count($this -> data) > 0)
			foreach($this -> data as $data) {

				if(isset($this -> filter['rubrique'])) {
					if(@intval($data['idx_rubrique']) !== intval($this -> filter['rubrique']))
						continue;

				}
				
				$i++;
			//	echo $i;
					
				if(!empty($this -> filter['limit'])) {

					if($i <= $this -> filter['limit'][0])
						continue;

					if($i > $this -> filter['limit'][1]) {
				//		echo 'break';
						break;			
					}
				}
				
				$newData[] = $data;
			}
		return $newData;
	}
}

?>