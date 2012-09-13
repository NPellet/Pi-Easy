<?php

class Install extends Security {
	
	private $data, $error;
	
	public function __construct() {
		global $_baseUrl;
		Instance::getInstance('Page') -> addCss($_baseUrl . FOLDER_DESIGN . 'pi-easy.css');
	}
	
	
	public function installPieasy() {
		global $_baseUrl, $_tCfg;

		if(empty($_POST['formEntry']))
			return $this -> form();
			
		$strHtml = NULL;

		$this -> data['NOM_SITE'] = $_POST['nomsite'];
		$this -> data['BASE_URL'] = $_POST['url'];
		$this -> data['DATA_ROOT_REL'] = $_POST['dataroot'];
		$this -> data['FTP_ROOT_REL'] = $_POST['ftproot'];
		$this -> data['DB_HOST'] = $_POST['dbhost'];
		$this -> data['DB_USER'] = $_POST['dbuser'];
		$this -> data['DB_PASSWORD'] = $_POST['dbpassword'];
		$this -> data['DB_NAME'] = $_POST['dbname'];
		$this -> data['MASTER_PASSWORD'] = sha1(mt_rand(1000000, 9999999));

		foreach($this -> data as $k => &$d) {
			if(!isset($d)) {
				$this -> log('install:0:2', $d);
				return $this -> form();
			}
			
			if($d == NULL && $k != 'DB_PASSWORD')
				$this -> error['null:' . $k] = true;
			
			$d = htmlspecialchars($d);
			$strHtml .= '
define(\'' . $k . '\', \'' . $d . '\');';
		}

		$strHtml = '<?php ' . $strHtml . '
?>';

		$sql = new Sql();
		if(!$sql -> connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpassword'], $_POST['dbname'])) {
			$this -> error['sql_connect'] = true;
			return $this -> form();
		}
		
		if(count($this -> error) > 0)
			return $this -> form();		
					
		/*if(!mkdir($this -> data['DATA_ROOT'] . FOLDER_UPLOAD)) {
			$this -> log('install:0:6');
			return $this -> form();
		}
		*/
		
		$oldumask = umask(0000);

		$folderTemp = $_POST['dataroot'] . FOLDER_UPLOAD_TEMP;
		
		$folderDrop = $_POST['dataroot'] . FOLDER_UPLOAD_DROPBOX;
		$folderMedias = $_POST['dataroot'] . FOLDER_UPLOAD_MEDIAS;
		$folderModules = $_POST['dataroot'] . FOLDER_UPLOAD_MEDIAS . FOLDER_UPLOAD_MODULES;
		
		if(
			(!file_exists($folderTemp) && !mkdir($folderTemp)) 
		||  (!file_exists($folderMedias) && !mkdir($folderMedias)) 
		||  (!file_exists($folderModules) && !mkdir($folderModules))
		||  (!file_exists($folderDrop) && !mkdir($folderDrop))
		) {
			$this -> log('install:0:7');
			return $this -> form();		
		}
		umask($oldumask);

		Instance::setInstance($sql, 'Sql');
		
		if(!file_put_contents($_baseUrl . 'config/config.inc.php', $strHtml)) {
			$this -> log('install:0:2');
			return $this -> form();
		}
		
		include($_baseUrl . 'config/config.inc.php');

		ob_start();
		include($_baseUrl . 'install/install.sql');
		$tTables = ob_get_contents();
		ob_end_clean();
	
		$tSql = explode(';', $tTables);
		foreach($tSql as $strSql)
			Sql::query($strSql);
			
		$strSql = '
		INSERT INTO 
			`' . Sql::buildTable(T_CFG_USER) . '` 
		VALUES
			("", "admin", "' . sha1('picnic01' . SECURITY_SALT) . '", "admin@pi-com.ch", 1, 0)';	
		Sql::query($strSql);
		
		
		$strSql = '
		INSERT INTO 
			`' . Sql::buildTable(T_CFG_CONFIG) . '` 
			(`key`, `label`, `value`, `admin`, `type`) 
		VALUES ';

		$tSql = array();
		foreach($_tCfg as $key => $data)
			$tSql[] = '
				("' . Sql::secure($key) . '", "' . Sql::secure($data[0]) . '", "' . Sql::secure($data[3]) . '", ' . intval($data[2]) . ', "' . Sql::secure($data[1]) . '")';
				
		$strSql .= implode(',', $tSql);
		if(Sql::query($strSql))				
			$this -> log('install:1:1');
		else
			$this -> log('install:0:5');
		
		return Instance::getInstance('Message') -> display();
	}
	
	private function form() {
		
		if(!isset($this -> data['DATA_ROOT'])) 
			$this -> data['DATA_ROOT'] = 'upload/';

		if(!isset($this -> data['FTP_ROOT'])) 
			$this -> data['FTP_ROOT'] = 'ftp/';

		if(!isset($this -> data['BASE_URL'])) 
			$this -> data['BASE_URL'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

		if(!is_writable(FOLDER_CONFIG))
			$this -> log('install:0:4');
		
		$tFields = array();
		
		$Form = new Form();
		$Form -> addLang('', 'Configuration générale');
		$Form -> setUrl('');
		$Form -> setClass('DataForm');

		$field = $Form -> addField('nomsite', '', false);
		$field -> setTitle('Nom du site');
		if(isset($this -> error['null:NOM_SITE']))
			$field -> setError('Ce champ doit être rempli');
		$field -> setField('<input type="text" name="nomsite" value="' . @$this -> data['NOM_SITE'] . '" />');
		
		$field = $Form -> addField('url', '', false);
		$field -> setTitle('URL de l\'administration');
		if(isset($this -> error['null:BASE_URL']))
			$field -> setError('Ce champ doit être rempli');
		$field -> setField('<input type="text" name="url" value="' . @$this -> data['BASE_URL'] . '" />');
	
		$field = $Form -> addField('dataroot', '', false);
		$field -> setTitle('Chemin vers le dossier data (relatif au root web)');
		if(isset($this -> error['null:DATA_ROOT']))
			$field -> setError('Ce champ doit être rempli');
		$field -> setField('<input type="text" name="dataroot" value="' . @$this -> data['DATA_ROOT'] . '" />');

		$field = $Form -> addField('ftproot', '', false);
		$field -> setTitle('Chemin vers le dossier FTP (relatif au root web)');
		if(isset($this -> error['null:FTP_ROOT']))
			$field -> setError('Ce champ doit être rempli');
		$field -> setField('<input type="text" name="ftproot" value="' . @$this -> data['FTP_ROOT'] . '" />');

		$field = $Form -> addField('dbhost', '', false);
		$field -> setTitle('Serveur MySQL');
		if(isset($this -> error['sql_connect']))
			$field -> setError('Connexion au serveur SQL erronnée');
		$field -> setField('<input type="text" name="dbhost" value="' . @$this -> data['DB_HOST'] . '" />');
		
		$field = $Form -> addField('dbuser', '', false);
		$field -> setTitle('Utilisateur MySQL');
		if(isset($this -> error['sql_connect']))
			$field -> setError('Connexion au serveur SQL erronnée');
		$field -> setField('<input type="text" name="dbuser" value="' . @$this -> data['DB_USER'] . '" />');
		
		$field = $Form -> addField('dbpassword', '', false);
		$field -> setTitle('Mot de passe MySQL');
		if(isset($this -> error['sql_connect']))
			$field -> setError('Connexion au serveur SQL erronnée');
		$field -> setField('<input type="text" name="dbpassword" value="' . @$this -> data['DB_PASSWORD'] . '" />');
		
		$field = $Form -> addField('dbname', '', false);
		$field -> setTitle('Nom de la base de données');
		if(isset($this -> error['sql_connect']))
			$field -> setError('Base de donnée inconnue');
		$field -> setField('<input type="text" name="dbname" value="' . @$this -> data['DB_NAME'] . '" />');
		
		return Instance::getInstance('Message') -> display() . $Form -> display();
	}
}

?>