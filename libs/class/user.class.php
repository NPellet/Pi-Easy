<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class User extends Object {
	
	private $id, $username, $email, $admin, $moderator, $tRights = array();
	
	public static function buildFromId($id) {
		
		if(empty($id))
			return new User();
			
		$strSql = '
		SELECT * FROM `' . Sql::buildTable(T_CFG_USER) . '` `user`
		LEFT JOIN `' . Sql::buildTable(T_CFG_RIGHTS) . '` `right` ON `user`.`id` = `right`.`idx_user`
		WHERE `id` = ' . intval($id) . ' LIMIT 1';
		
		$f = true;
		if($resSql = Sql::query($strSql))
			while($dataSql = mysql_fetch_assoc($resSql)) {
				if(!$obj) 
					$obj = new User($dataSql);
				$obj -> setRight($dataSql['idx_module'], $dataSql['mode'], $dataSql['right']);
			}
		else
			$obj = new User();
			
		return $obj;
	}
	
	public static function buildFromSession() {
		
		self::clearSessions();
		
		if(!empty($_COOKIE['sessionId'])) {
			$strSql = '
			SELECT * FROM `' . Sql::buildTable(T_CFG_SESSIONS) . '` `sessions` 
			LEFT JOIN `' . Sql::buildTable(T_CFG_USER) . '` `user`
			ON `sessions`.`idx_user` = `user`.`id`
			WHERE `sessid` = "' . Sql::secure($_COOKIE['sessionId']) . '" LIMIT 1';
			
			if($resSql = self::query($strSql)) {
				if($dataSql = mysql_fetch_assoc($resSql)) {
					$userId = $dataSql['idx_user'];
					if($dataSql['id'] != NULL) {
						return User::login($dataSql, $dataSql['sessid']);
					}
				}
				return false;
			} 
			return self::log('sql:query');
		}
	}
	
	public static function login($data, $sessionId = NULL) {

		if($User = new User($data)) {
			self::setInstance($User, 'currentUser');
			$User -> setSession($sessionId);
			return $User;
		}
		return false;
	}
	
	public static function logout() {
		$strSql = 'DELETE FROM `' . Sql::buildTable(T_CFG_SESSIONS) . '` WHERE `sessid` = "' . $_COOKIE['sessionId'] . '"';
		if(Sql::query($strSql)) {
			setcookie('sessionId', '', 0, '/');
			session_destroy();
		}
	}
	
	public static function clearSessions() {
		
		$strSql = 'DELETE FROM `' . Sql::buildTable(T_CFG_SESSIONS) . '` WHERE `timeout` < ' . time();
		
		if(!self::query($strSql))
			return self::log('sql:query');
	}
	
	public static function clearBruteforce() {
		$strSql = 'DELETE FROM `' . Sql::buildTable(T_CFG_BRUTEFORCE) . '` WHERE `last` < ' . (time() - BRUTEFORCE_EXPIRACY);
		if(!self::query($strSql)) 
			return self::log('sql:query');
	}
	
	public static function checkBruteforce() {
		
		$ip = $_SERVER['REMOTE_ADDR'];
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_BRUTEFORCE) . '` WHERE `ip` = "' . Sql::secure($ip) . '"';
		if($resSql = self::query($strSql)) {
			if($dataSql = mysql_fetch_assoc($resSql)) {
				$tried = $dataSql['tries'];
				if($tried > SESSION_TRIES) {
					self::log('login:0:3');
					return true;
				} else
					return false;
			} else
				return false;
		} else 
			return false;
	}
	
	public static function addBruteforce() {
		$ip = $_SERVER['REMOTE_ADDR'];
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_BRUTEFORCE) . '` WHERE `ip` = "' . Sql::secure($ip) . '"';
		if($resSql = self::query($strSql)) {
			if(mysql_num_rows($resSql) == 0) {
				$strSql = 'INSERT INTO `' . Sql::buildTable(T_CFG_BRUTEFORCE) . '` (`ip`, `tries`, `last`) 
				VALUES ("' . Sql::secure($ip) . '", 1, ' . time() . ')';
				if(self::query($strSql))
					return true;
				else
					die();
			} else {
				$dataSql = mysql_fetch_assoc($resSql);
				$tries = $dataSql['tries'] + 1;
				$strSql = 'UPDATE `' . Sql::buildTable(T_CFG_BRUTEFORCE) . '` 
				SET `tries` = "' . $tries . '", `last` = ' . time() . ' WHERE `ip` = "' . Sql::secure($ip) . '"';
				if(self::query($strSql))
					return true;
				else
					die();
			}
		} else 
			die();
	}
	
	public function tryLogin($username,  $userpassword) {
		
		self::clearBruteforce();
		
		if(self::checkBruteforce())
			return false;

		$username = Sql::secure($username);
		$userpassword = sha1($userpassword . SECURITY_SALT);
		
		$strSql = '
		SELECT * FROM `' . Sql::buildTable(T_CFG_USER) . '` 
		WHERE `username` =  "' . $username . '" AND `password` = "' . $userpassword . '" LIMIT 1';

		if($resSql = self::query($strSql)) {
			if(mysql_num_rows($resSql) == 0) {
				self::addBruteforce();	
				return self::log('login:0:1');
			}
			if($dataSql = mysql_fetch_assoc($resSql)) {
				User::login($dataSql); 	
				return true;
			} else
				return self::log('login:0:1');
		} else
			self::log('sql:query');	
	}
	
	public function setSession($sessionId) {
		
		if($this -> id == NULL)
			return $this -> log();
		
		if($sessionId == NULL)
			$sessionId = sha1(uniqid());
		
		$strSql = 'INSERT INTO `' . Sql::buildTable(T_CFG_SESSIONS) . '` 
		(`sessid`, `idx_user`, `timeout`, `ip`) VALUES ("' . $sessionId . '", ' . $this -> id . ', ' . (time() + SESSION_EXPIRACY) . ', "' . $_SERVER['REMOTE_ADDR'] . '")		
		ON DUPLICATE KEY UPDATE `timeout` = ' . (time() + SESSION_EXPIRACY) . ', `sessid` = "' . $sessionId . '"';
		
		if(!$this -> query($strSql))
			return $this -> log('sql:query');

		setcookie('sessionId', $sessionId, time() + SESSION_EXPIRACY, '/');
	}
	
	public function __construct($dataSql = NULL) {
		
		if($dataSql == NULL)
			return;
			
		extract($dataSql);
		$this -> id = $id;	
		$this -> username = $username;
		$this -> admin = (bool) $admin;
		$this -> moderator = (bool) $moderator;
		$this -> email = $email;
		$this -> getRights();

	}
	
	public function save($password = NULL) {
		
	//	if($this -> userAdmin()) {
			if($password != NULL)
				$sql['password'] = sha1($password . SECURITY_SALT);
	//	}
		
		$sql['username'] = $this -> username;
		$sql['email'] = $this -> email;
		if($this -> userAdmin())
			$sql['moderator'] = $this -> moderator;
		
		$strSql = Sql::buildSave(T_CFG_USER, $this -> id, $sql);
		if($this -> query($strSql)) {
			
			if($this -> id == NULL)
				$this -> id = mysql_insert_id();
				
		} else
			return $this -> log('sql:query');
	
		$sql = array();
		if($this -> userAdmin()) {
			foreach($this -> tRights as $module => $mode) {
				
				foreach($mode as $right => $hasRight) {
					$sql['idx_user'] = $this -> id;
					$sql['idx_module'] = $module;
					$sql['mode'] = $right;
					$sql['right'] = $hasRight;
					Sql::query(Sql::buildSave(T_CFG_RIGHTS, NULL, $sql));
				}
			}
		}
	}
	
	
	public function getRights() {
		if(count($this -> tRights) == 0)
			$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_RIGHTS) . '` WHERE `idx_user` = ' . (int) $this -> id;
			if($resSql = $this -> query($strSql)) {
				while($dataSql = mysql_fetch_assoc($resSql)) {
					$this -> setRight($dataSql['idx_module'], $dataSql['mode'], $dataSql['right']);
				}
			}
	}
	
	
	public function setRight($module, $right, $hasRight = true) {	
	//	if($this -> userAccess($module, $right) && ($this -> userModerator() || $this -> userAdmin()))
			$this -> tRights[$module][$right] = $hasRight;
		return true;
	}
	
	public function hasRight($module, $right) {
		return $this -> isAdmin() ? true : (!empty($this -> tRights[$module][$right]) ? $this -> tRights[$module][$right] : false);
	}
	
	public function getUsername() { return $this -> username; }
	public function getEmail() { return $this -> email; }
	public function getId() { return $this -> id; }
	public function isModerator() { return $this -> moderator; }
	public function isAdmin() { return $this -> admin; }
	
	public function edit() {
		
		if(empty($_POST['formEntry']))
			return $this -> formEdit();
		
		$errors = array();
		
		extract($_POST);
		$this -> username = $username;
		$this -> email = $userEmail;
		
		if($this -> userAdmin())
			$this -> moderator = !empty($userModerator) ? true : false;
		
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_USER) . '` WHERE LOWER(`username`) = "' . Sql::secure(strtolower($username)) . '"';
		if($resSql = $this -> query($strSql)) {
			$num = mysql_num_rows($resSql);
			if($num == 1) {
				$dataSql = mysql_fetch_assoc($resSql);
				$id = $dataSql['id'];
				if($id != $this -> id)
					$errors['username'] = 'Ce nom d\'utilisateur est déjà utilisé. Veuillez en choisir un autre';	
			}
		} else
			return $this -> log('sql:query');
			
		if($userPassword1 != $userPassword2)
			$errors['password'] = 'Les mots de passe sont différents';	
		
		global $_tRights;

		$Modules = Module::getAll();
		
		if($this -> userAdmin())
			foreach($Modules as $Module)
				foreach($_tRights as $mode) {
					$this -> setRight($Module -> getId(), $mode, (!empty($right[$Module -> getId()][$mode]) ? true : false));
				}
			
		if(count($errors) > 0)
			return $this -> formEdit($errors);
		
		$this -> save($userPassword1);
		$this -> redirect(array('user' => false, 'action' => 'show'));
	}
	
	private function formEdit($errors = NULL) {
		
		$Form = new Form();
		$Form -> addLang('', 'Configuration de l\'utilisateur');
		$Form -> setUrl('');
		$Form -> setClass('DataForm');
		
		$Username = $Form -> addField('user');
		$Username -> setTitle('Nom d\'utilisateur');
		if(!empty($errors['username']))
			$Username -> setError($errors['username']);
		$Username -> setField('<input type="text" name="username" value="' . $this -> username . '" />');
		
		$Password = $Form -> addField('password');
		$Password -> setTitle('Mot de passe');
		if(!empty($errors['password']))
			$Password -> setError($errors['password']);
		$Password -> setField('<input type="password" name="userPassword1" value="" />');
		
		$Password2 = $Form -> addField('conf');
		$Password2 -> setTitle('Confirmer le mot de passe');
		$Password2 -> setField('<input type="password" name="userPassword2" value="" />');
		
		$Email = $Form -> addField('email');
		$Email -> setTitle('E-mail');
		$Email -> setField('<input type="text" name="userEmail" value="' . $this -> email . '" />');
		
		if($this -> userAdmin()) {
			$Moderator = $Form -> addField('moderator');
			$Moderator -> setTitle('Modérateur');
			$Moderator -> setField('<input type="checkbox" name="userModerator" ' . ($this -> isModerator() ? 'checked="checked" ' : NULL) . '/>');
		}
				
		if(Security::userAdmin()) {
		
			$formRights = '
			<table cellpadding="0" cellspacing="0" class="DataForm Rights">
				<tr>
					<th>Module</th>
					<th>Voir</th>
					<th>Ajouter</th>
					<th>Editer</th>
					<th>Supprimer</th>
					<th>Editer les rubriques</th>
				</tr>';
			
			$tModules = Module::getAll();
			global $_tRights;
			
			foreach($tModules as $Module) {
				
				$formRights .= '
				<tr>
					<td class="FirstCol">' . $Module -> getLabel() . '<input type="hidden" name="right[' . $Module -> getId() . '][is]" value="true" /></td>	';
					
					foreach($_tRights as $right)
						$formRights .= '
						<td>
							<input type="checkbox" name="right[' . $Module -> getId() . '][' . $right . ']" ' . ($this -> hasRight($Module -> getId(), $right) ? 'checked="checked"' : NULL) . ' />
						</td>';
			
				$formRights .= '</tr>';
			}
			
			$formRights .= '</table>';
		}
		
		$Form -> append($formRights);
		return $Form -> display();
	}
	
	public static function getAll() {
		
		$tUsers = array();
		$strSql = 'SELECT * FROM `' . Sql::buildTable(T_CFG_USER) . '` ORDER BY `admin` DESC, `moderator` ASC, `username` ASC';
		if($resSql = Sql::query($strSql)) {
			while($dataSql = mysql_fetch_assoc($resSql)) {
				$tUsers[] = new User($dataSql);
			}
		}
		
		return $tUsers;
	}
	
	
	public static function showAll() {
		
		$strModel = '
			<tr rel="%s" class="%s">
				<td class="FirstCol">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
			</tr>
		';
		
		$strHtml = '
		<table class="Data" cellpadding="0" cellspacing="0">
			<tr>
				<th>Nom d\'utilisateur</th>
				<th>E-mail</th>
				<th>Administrateur</th>
				<th>Modérateur</th>
			</tr>';
			
		$tUsers = User::getAll();	

		$i = 0;
		foreach($tUsers as $User) {

			$strHtml .= sprintf($strModel, 
			/*	self::url(array('action' => 'edit', 'user' => $User -> getId())),*/
				$User -> getId(),
				$i % 2 == 0 ? 'Even' : 'Odd',
				$User -> getUsername(),
				$User -> getEmail(),
				$User -> isAdmin() ? 'Oui' : 'Non',
				$User -> isModerator() ? 'Oui' : 'Non'
			);
			$i++;
		}
		$strHtml .= '</table>';
		
		return $strHtml;
	}
	
	public function remove() {
		
		$strSql = 'DELETE FROM `' . Sql::buildTable(T_CFG_USER) . '` WHERE `id` = ' . intval($this -> id) . ' LIMIT 1';
		if($this -> query($strSql))
			return true;
			
		return $this -> log('sql:query');
	}
}


?>