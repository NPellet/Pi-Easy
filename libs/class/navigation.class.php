<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Navigation extends Security {
	
	private $mode;
	private $admin;
	protected $User;
	public $nav = array();
	private $base_path = './';
	private $Page;
	
	/* Constructeur de classe */
	public function __construct() {
		global $toInstall;
		
		$this -> setInstance($this, 'Navigation');
		$this -> Page = $this -> setInstance(new Page(), 'Page');
		$this -> setInstance(new Message(explode('_', $this -> message)), 'Message');

		if($toInstall)
			return;
			
		$Sql = new Sql();
		$Sql -> connect();

		$this -> setInstance($Sql, 'Sql');
		$this -> setInstance(new Config(), 'Config');
		
		if(!defined('IN_NAV')) define('IN_NAV', true);
		
		$this -> parseTheGet();
	}
	
	
	public function parseTheGet() {

		$toParse = !empty($_GET['nav']) ? explode('/', $_GET['nav']) : array();	

		foreach($toParse as $parse) {
			$parse = explode('-', $parse);
			$this -> nav[$parse[0]] = $parse[1];
		}
		
		if(!empty($this -> nav['mess'])) {
			$messages = explode(',', $this -> nav['mess']);
			foreach($messages as $message) {
				$this -> getInstance('Message') -> log($message, false);	
			}
		}
	}
	
	public function nav($mode) {
		return !empty($this -> nav[$mode]) ? $this -> nav[$mode] : NULL;
	}
	
	public function login() {
		
		if(User::buildFromSession()) {
			if($this -> logout) {
				session_start();
				User::logout();
				$this -> redirect();	
			}
		} else {
			if($this -> login)
				User::tryLogin(@$_POST['username'], @$_POST['password']);
		}
	}
	
	public function run() {
		global $_baseUrl, $toInstall;
		$this -> setJs();
		
		if($toInstall && file_exists($_baseUrl . 'install/install.php')) {

			include($_baseUrl . 'install/install.php');
			$install = new Install();
			$installHtml = $install -> installPieasy();
			$this -> Page -> addNavigation('Installation du Pi-Easy');
			$this -> Page -> setContent($installHtml);
			$this -> Page -> display();
			return;
		}
		
		$this -> login();
		$User = $this -> getInstance('currentUser');
	
		if(!$User) 
			return $this -> displayLogin();

		$this -> Page -> addCss($_baseUrl . FOLDER_DESIGN . 'pi-easy.css');
	//	$strFile = $this -> getActivity();
		
		$this -> Page -> addToolbar('Config', $this -> url(array('mode' => 'config'), true), 'Configuration');
		$this -> Page -> addToolbar('Users', $this -> url(array('mode' => 'user', 'action' => 'show'), true), 'Gestion des utilisateurs');
		$this -> Page -> addToolbar('Help', $this -> url(), 'Aide');
		
		$this -> Page -> setContent($this -> content());
		$this -> handleMenu();
		
		$this -> Page -> display();
	}
	
	public function url($nav = NULL, $reset = NULL) {
		global $_baseUrl;
		
		if($reset == false)
			$theBuild = $this -> nav;
		else 
			$theBuild = array();

		if(empty($nav['sent'])) unset($theBuild['sent']);
		if(empty($nav['message'])) unset($theBuild['message']);
		if(empty($nav['login'])) unset($theBuild['login']);
		if(empty($nav['logout'])) unset($theBuild['logout']);

		if(count($nav) > 0 && is_array($nav))
			foreach($nav as $key => $value) {
				if($value == NULL) {
					unset($theBuild[$key]);
					continue;
				}
				$theBuild[$key] = $value;
			}
		
		unset($theBuild['mess']);
		$urlMessages = $this -> getInstance('Message') -> export();
		if($urlMessages != NULL)
			$theBuild['mess'] = $urlMessages;
		
		$strUrl = NULL;
		if(count($theBuild) > 0)
			foreach($theBuild as $key => $value)
				$strUrl .= '/' . $key . '-' . $value;

		return $_baseUrl . substr($strUrl, 1) . '.html';
	}
	
	public function redirect($url = NULL) {
	
		if($url == NULL)
		header('Location: ' . BASE_URL);
			
		if(is_array($url))
			$url = $this -> url($url);
		header('Location: ' . BASE_URL . $url);
	}

	public function __get($var) { return !empty($this -> nav[$var]) ? $this -> nav[$var] : false; }
	public function __set($varName, $varValue) { if($varValue == NULL) unset($this -> nav[$varName]); else $this -> nav[$varName] = $varValue; }
	

	private function displayLogin() {
		global $_baseUrl;

		$this -> Page -> setFile($_baseUrl . FOLDER_PAGES . 'login.php');
		$this -> Page -> simple = true;
		$this -> Page -> display();
		return;
	}
	
	
	private function content() {
	
		global $toInstall;
		$User = $this -> getInstance('User');
		
		if($this -> nav('plugin') != NULL) {
			return PluginManager::run();
		}
	
		if(@$this -> nav['action'] == 'edit' || @$this -> nav['action'] == 'add' && @$this -> nav['mode'] !== 'media') {
			$Back = new Button('Back', 'Retour');
			$Back -> setUrl(array('action' => 'show'));
			$this -> Page -> addButton($Back);
		} else {
			$Update = new Button('Reload', 'Actualiser', false);
			$Update -> setUrl(array());
			$this -> Page -> addButton($Update);			
		}
		
		switch(@$this -> nav['mode']) {
			
			case 'entry':
				
				if(!$this -> module)
					return $this -> log();
			
				$Module = Module::buildFromId($this -> module);
				
				$this -> Page -> addNavigation($Module -> getCategorie() -> getLabel());
				$this -> Page -> addNavigation($Module -> getLabel());

				if($this -> action == 'edit' || $this -> action == 'add') {
					
					if(Security::userAdmin()) {
						$Mod = new Button('Module', 'Module');
						$Mod -> setUrl(array('mode' => 'module', 'action' => 'edit', 'module' => $Module -> getId(), 'entry' => false));
						$this -> Page -> addButton($Mod);
						
						$Fields = new Button('Fields', 'Champs');
						$Fields -> setUrl(array('mode' => 'field', 'action' => 'show', 'module' => $Module -> getId(), 'entry' => false));
						$this -> Page -> addButton($Fields);
					}
					
					$Data = new GetData();
					$Data -> setModule($Module);
					$Data -> setWhere('id', $this -> entry, '=');
					$Data -> get();
					$data = $Data -> getData();
				
					$this -> Page -> addNavigation(($this -> action == 'add' ? 'Ajouter' : 'Editer') . ' l\'entrée');
					$Entry = new Entry($Module, $this -> entry, (!empty($data[$this -> entry]) ? $data[$this -> entry] : array()));
					return $Entry -> edit();
						
				} else {
					
					$Add = new Button('Add', 'Ajouter', false);
					$Add -> setUrl(array('action' => 'add', 'entry' => false));
					$this -> Page -> addButton($Add);

					$Rub = new Button('Remove', 'Supprimer', false);
					$Rub -> setOptions(array(
							'Class' => 'RemoveEntries',
							'Validable' => true,
							'Selectable' => true,
							'Disablable' => true,
							'Rel' => 'entry'
						));
					$this -> Page -> addButton($Rub);


					if($Module -> isSortable()) {
						$Sort = new Button('Sort', 'Trier', false);
						$Sort -> setOptions(array(
									'Class' => 'SortEntries',
									'Selectable' => true,
									'Disablable' => true,
									'Validable' => true
								));
						$this -> Page -> addButton($Sort);
					}
							
					if($Module -> hasRubriques()) {
						$Rub = new Button('Rubrique', 'Rubriques', false);
						$Rub -> setOptions(array(
									'Id' => 'EditRubriques',
									'Disablable' => true
								));
						$Rub -> setUrl(array('mode' => 'rubrique', 'module' => $Module -> getId()));
						$this -> Page -> addButton($Rub);
					}
					
					if(Security::userAdmin()) {
						$Mod = new Button('Module', 'Module', false);
						$Mod -> setUrl(array('mode' => 'module', 'action' => 'edit', 'module' => $Module -> getId()));
						$this -> Page -> addButton($Mod);
						
						$Fields = new Button('Fields', 'Champs', false);
						$Fields -> setUrl(array('mode' => 'field', 'action' => 'show', 'module' => $Module -> getId()));
						$this -> Page -> addButton($Fields);
					}
					
					return Entry::showAll($this -> module);
				}
				
			break;
			

			
			case 'module': 
				if(!$this -> userAdmin())
					return $this -> log();
				$this -> Page -> addNavigation('Administration des modules');					
				if($this -> action == 'edit' || $this -> action == 'add') {
					
					if($this -> action == 'edit') {
						$Add = new Button('Add', 'Ajouter');
						$Add -> setUrl(array('action' => 'add', 'module' => false));
						$this -> Page -> addButton($Add);
					}

					$Module = Module::buildFromId($this -> module);
					$this -> Page -> addNavigation(($this -> action == 'add' ? 'Ajouter' : 'Editer') . ' un module');
					return $Module -> edit();	
				} else {
					
					$Add = new Button('Add', 'Ajouter');
					$Add -> setUrl(array('action' => 'add'));
					$this -> Page -> addButton($Add);
					/*
					$Mod = new Button('Edit', 'Modifier');
					$Mod -> setOptions(array(
							'Id' => 'Edit',
							'Rel' => 'module',
							'Disablable' => true,
							'Selectable' => true
						));
					$this -> Page -> addButton($Mod);
					*/
					
					$Remove = new Button('Remove', 'Supprimer');
					$Remove -> setOptions(array(
							'Id' => 'Remove',
							'Rel' => 'module',
							'Selectable' => true,
							'Validable' => true,
							'Disablable' => true
						));
					$this -> Page -> addButton($Remove);
					
					$Fields = new Button('Fields', 'Champs');
					$Fields -> setOptions(array(
							'Id' => 'Field',
							'Rel' => 'module',
							'Selectable' => true,
							'Disablable' => true
						));
					$this -> Page -> addButton($Fields);
					
					return Module::showAll();
				}
			break;
		
			case 'field': 
				if(!$this -> userAdmin())
					return $this -> log();

				if(!$this -> module)
					return $this -> log();
				
				$Module = Module::buildFromId($this -> module);
				$this -> Page -> addNavigation($Module -> getLabel());
				$this -> Page -> addNavigation('Administrer les champs');
				
				if($this -> action == 'edit' || $this -> action == 'add') {
					$Field = Field::buildFromId($this -> field);

					if($this -> action == 'add') {
						$this -> Page -> addNavigation('Ajouter un champ');
						$Field -> setIdxModule($this -> module);
					} else {
						$this -> Page -> addNavigation('Modifier un champ');
						$Add = new Button('Add', 'Ajouter');
						$Add -> setUrl(array('action' => 'add'));
						$this -> Page -> addButton($Add);
					}
		
					return $Field -> edit();
				} else {
					
					$Add = new Button('Add', 'Ajouter');
					$Add -> setUrl(array('action' => 'add', 'field' => false));
					$this -> Page -> addButton($Add);

					$Remove = new Button('Remove', 'Supprimer');
					$Remove -> setOptions(array(
								'Id' => 'Remove',
								'Rel' => 'field',
								'Selectable' => true,
								'Disablable' => true,
								'Validable' => true
							));
					
					$this -> Page -> addButton($Remove);
					
					
					$Groups = new Button('Defaut', 'Groupes');
					$Groups -> setUrl(array('mode' => 'groupfields', 'action' => 'show'));
					$this -> Page -> addButton($Groups);
					
					return Field::showAll($this -> module);
				}
			break;
		
			case 'rubrique':
				
				if(!$this -> module)
					return $this -> log();
				
				$Add = new Button('Add', 'Ajouter');
				$Add -> setOptions(array('Id' => 'AddRubrique'));
				$this -> Page -> addButton($Add);
				
				$Edit = new Button('Edit', 'Editer');
				$Edit -> setOptions(array('Id' => 'EditRubrique', 'Validable' => true, 'Selectable' => true));
				$this -> Page -> addButton($Edit);
					
				$Remove = new Button('Remove', 'Supprimer');
				$Remove -> setOptions(array('Id' => 'RemoveRubrique', 'Validable' => true, 'Selectable' => true));
				$this -> Page -> addButton($Remove);
				
				$Save = new Button('Defaut', 'Sauver');
				$Save -> setOptions(array('Id' => 'SaveRubriques'));
				$this -> Page -> addButton($Save);
				
				
				$Module = Module::buildFromId($this -> module);
				$this -> Page -> addNavigation($Module -> getLabel());
				$this -> Page -> addNavigation('Administrer les rubriques');
				
				return  $Module -> handleRubriques();
				
			break;
		
			case 'groupfields':
				
				if(!$this -> userAdmin())
					return $this -> log();
			
					
				if($this -> module) {
					$Module = Module::buildFromId($this -> module);
					$this -> Page -> addNavigation($Module -> getLabel());
				} else
					$this -> Page -> addNavigation('Editer les groupes de configuration');
					
				if($this -> action == 'edit' || $this -> action == 'add') {
					
					$Group = GroupFields::buildFromId($this -> groupfields);
	
					if($this -> action == 'add') {
						$this -> Page -> addNavigation('Ajouter un groupe de champs');
						$Group -> setIdxModule($this -> module);
					} else {
						$this -> Page -> addNavigation('Modifier un groupe de champs');
						$Add = new Button('Add', 'Ajouter');
						$Add -> setUrl(array('action' => 'add'));
						$this -> Page -> addButton($Add);
					}
		
					return $Group -> edit();
				} else {
					
					
					$this -> Page -> addNavigation('Liste des groupes de champs');
						
					$Add = new Button('Add', 'Ajouter');
					$Add -> setUrl(array('action' => 'add'));
					$this -> Page -> addButton($Add);

					$Edit = new Button('Edit', 'Editer');
					$Edit -> setOptions(array('Id' => 'GrpFieldsEdit', 'Validable' => true, 'Selectable' => true));
					$this -> Page -> addButton($Edit);

					$Remove = new Button('Remove', 'Supprimer');
					$Remove -> setOptions(array(
								'Id' => 'Remove',
								'Rel' => 'groupfields',
								'Selectable' => true,
								'Disablable' => true,
								'Validable' => true
							));
					
					$this -> Page -> addButton($Remove);
					
					return GroupFields::showAll($this -> module);
				}
				
				
			break;
			
			case 'category': 
				if(!$this -> userAdmin())
					return $this -> log();
					
				$this -> Page -> addNavigation('Administrer les catégories');					
				if($this -> action == 'edit' || $this -> action == 'add') {
					$this -> Page -> addNavigation(($this -> action == 'add' ? 'Ajouter' : 'Editer') . ' une catégorie');
					$Category = Category::buildFromId($this -> category);
					return $Category -> edit();	
				} else {
					$Add = new Button('Add', 'Ajouter');
					$Add -> setUrl(array('action' => 'add', 'category' => false));
					$this -> Page -> addButton($Add);
					return Category::showAll();
				}
				
			break;
		
			case 'user': 
				if(!$this -> userAdmin() && $this -> user != $this -> getInstance('currentUser') -> getId())
					return $this -> log();
					
				$this -> Page -> addNavigation('Administrer les utilisateurs');
				
				if($this -> action == 'edit' || $this -> action == 'add') {
					$this -> Page -> addNavigation(($this -> action == 'add' ? 'Ajouter' : 'Editer') . ' un utilisateur');
					$User = User::buildFromId($this -> user);
					return $User -> edit();
				} else {
					$Add = new Button('Add', 'Ajouter');
					$Add -> setUrl(array('action' => 'add'));
					$this -> Page -> addButton($Add);
					return User::showAll();
				}
			break;			
		
			case 'config':
							
				$Config = Instance::getInstance('Config');
				
			
				if(Security::userAdmin()) {
					
					$Add = new Button('Add', 'Ajouter');
					$Add -> setUrl(array('action' => 'cfg'));
					$this -> Page -> addButton($Add);
					
					$Edit = new Button('Edit', 'Editer');
					$Edit -> setOptions(array('Id' => 'CfgEdit', 'Selectable' => true, 'Validable' => true));
					$this -> Page -> addButton($Edit);
					
					$Remove = new Button('Remove', 'Supprimer');
					$Remove -> setOptions(array('Id' => 'CfgRemove', 'Selectable' => true, 'Validable' => true));
					$this -> Page -> addButton($Remove);
					
					$Groups = new Button('Defaut', 'Groupes');
					$Groups -> setUrl(array('mode' => 'groupfields', 'action' => 'show', 'module' => 0));
					$this -> Page -> addButton($Groups);
				}
						
				if($this -> nav['action'] == 'cfg' && Security::userAdmin()) {				
					return $Config -> editKey();
				} else {
				
					$this -> Page -> addNavigation('Editer la configuration');
					return $Config -> edit();	
				}
					
			break;
			
			case 'activity':
				if($User -> isAdmin()) $strFile = './pages/activity.php';
				else return $this -> log();
			break;

			case 'plugin':
				if(Security::userAdmin()) 
					return PluginManager::run();
				else 
					return $this -> log();
			break;
			
			case 'media':
				$media = new Mediatheque();
				if($this -> nav['action'] == 'show')
					return $media -> showList($this -> nav['type']);
				else
					return $media -> form();
					
			break;
			
			default:
				$this -> Page -> addNavigation('Accueil du Pi-Easy');
				ob_start();
					include('./pages/main.php');
				$content = ob_get_contents();
				ob_end_clean();
				
				return $content;
			//	return include('./pages/main.php');
			break;
		}

		return $strFile;
	}
	
	private function handleMenu() {

		$Categories = Category::getAll();
		$Categories[] = new Category(array('id' => 0, 'label' => 'Hors catégorie', 'order' => 0));
		
		$Modules = Module::getAll();
		foreach($Categories as $Category) {
			
			$User = $this -> getInstance('currentUser');
			$tMenu = array();
			
			$strModulesMenu = NULL;
			foreach($Modules as $Module) {
				
				if($Module -> getIdxCategory() == $Category -> getId() && $User -> hasRight($Module -> getId(), 'view')) {
					$url = $this -> url(array('mode' => 'entry', 'action' => 'show', 'module' => $Module -> getId()), true);
					$tMenu[] = array(
								'url' => $url, 
								'label' => $Module -> getLabel(), 
								'selected' => $this -> nav('module') == $Module -> getId() ? true : false
								);
				}
			}
			
			if(count($tMenu) > 0) {
				$Menu = $this -> Page -> addMenu($Category -> getLabel());	
				foreach($tMenu as $menu) 
					$Menu -> addEntry($menu);			
			}
		}
		
		
		/*
		$Menu = $this -> Page -> addMenu('Médiathèque');
		
		$Menu -> addEntry(array(
			'url' => $this -> url(array('mode' => 'media', 'action' => 'show', 'type' => 'image'), true), 
			'label' => 'Visionner'
		));

		$Menu -> addEntry(array(
			'url' => $this -> url(array('mode' => 'media', 'action' => 'add'), true), 
			'label' => 'Ajouter des fichiers', 
			'selected' => ($this -> nav('mode') == 'media' && $this -> nav('action') == 'add' ? true : false)
		));
*/
		$tPlugins = PluginController::getPlugins(true);
		foreach($tPlugins as $plugin) {
			$id = $plugin['id'];
			include(FOLDER_PLUGINS . $plugin['name'] . '/menu.php');
		}
		/*
		$Menu = $this -> Page -> addMenu('Configuration');
		$Menu -> addEntry(array(
							'url' => $this -> url(array('mode' => 'config'), true), 
							'label' => 'Editer la configuration', 
						  	'selected' => ($this -> nav('mode') == 'config' ? true : false)
						));
		*/
		if($this -> userAdmin()) {	
		
			$Menu = $this -> Page -> addMenu('Administration');
			$Menu -> addEntry(array(
								'url' => $this -> url(array('mode' => 'module', 'action' => 'show'), true), 
								'label' => 'Editer les modules',
								'selected' => ($this -> nav('mode') == 'config' ? true : false)
								));
			
			$Menu -> addEntry(array(
								'url' => $this -> url(array('mode' => 'category', 'action' => 'show'), true), 
								'label' => 'Editer les catégories',
								'selected' => ($this -> nav('mode') == 'category' ? true : false)
								));
			$Menu -> addEntry(array('url' => $this -> url(array('mode' => 'user', 'action' => 'show'), true), 'label' => 'Editer les utilisateurs'));
			$Menu -> addEntry(array('url' => $this -> url(array('mode' => 'plugin', 'action' => 'show'), true), 'label' => 'Gérer les plugins'));
		} else {
			$Menu = $this -> Page -> addMenu('Informations Personnelles');
			$Menu -> addEntry(array('url' => $this -> url(array('mode' => 'user', 'action' => 'edit', 'user' => $this -> getInstance('currentUser') -> getId()), true), 'label' => 'Editer mon compte'));
			
		}
	}
	
	function setJs() {
		global $_baseUrl;
		
		$this -> Page -> addJs(array(
			$_baseUrl . FOLDER_LIBS_JQUERY . 'jquery-1.6.1.min.js',
			$_baseUrl . FOLDER_LIBS_JAVASCRIPT . 'plugins.js',
			$_baseUrl . FOLDER_LIBS_JAVASCRIPT . 'overlay.js',
			$_baseUrl . FOLDER_LIBS_JAVASCRIPT . 'remove.js',
			$_baseUrl . FOLDER_LIBS_JAVASCRIPT . 'field.js',
			$_baseUrl . FOLDER_LIBS_JAVASCRIPT . 'uploader.js',
			$_baseUrl . FOLDER_LIBS_JQUERY . 'tinymce/jquery.tinymce.js',
			$_baseUrl . FOLDER_LIBS_JQUERY . 'asmselect/jquery.asmselect.js',
			$_baseUrl . FOLDER_LIBS_JAVASCRIPT . 'table.js',
			$_baseUrl . FOLDER_LIBS_JAVASCRIPT . 'rubriques.js',
			$_baseUrl . FOLDER_LIBS_JQUERY . 'colorbox/jquery.colorbox.js',
			$_baseUrl . FOLDER_LIBS_JQUERY . 'filetree/jquery.filetree.js',
			$_baseUrl . FOLDER_LIBS_JQUERY . 'jqueryui/jquery-ui.min.js',
			$_baseUrl . FOLDER_LIBS_JQUERY . 'swfobject/jquery.swfobject.1-1-1.min.js',
			$_baseUrl . FOLDER_LIBS_JQUERY . 'growfield/jquery.growfield.js',
			'http://maps.google.com/maps/api/js?sensor=false'
		));
			
		$this -> Page -> addCss(array(
			$_baseUrl . FOLDER_LIBS_JQUERY . 'asmselect/jquery.asmselect.css',
			$_baseUrl . FOLDER_LIBS_JQUERY . 'colorbox/colorbox.css',
			$_baseUrl . FOLDER_LIBS_JQUERY . 'filetree/jquery.filetree.css',
			$_baseUrl . FOLDER_LIBS_JQUERY . 'jqueryui/css/smoothness/jquery-ui.css',
	
		));	
	}
}

?>