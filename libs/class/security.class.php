<?php

if(!defined('IN_PIEASY'))
	die('Error 403 : You do not have granted access to this file');

class Security extends Instance {
	
	public static function userAdmin() {
		$CurrentUser = self::getInstance('currentUser');
		if($CurrentUser instanceof User)
			return $CurrentUser -> isAdmin();
		return false;
	}

	public static function userModerator() {
		$CurrentUser = self::getInstance('currentUser');
		if($CurrentUser instanceof User)
			return $CurrentUser -> isModerator();
		return false;
	}

	public static function userAccess($moduleId, $mode) {
		$CurrentUser = self::getInstance('currentUser');
		if($CurrentUser instanceof User)
			return $CurrentUser -> hasRight($moduleId, $mode);	
		
		return false;
	}
	
	private function secureForm() {
		$_SESSION['formToken'] = sha1(rand(0, 1000000000));
		return '<input type="hidden" name="form_token" value="' . $_SESSION['formToken'] . '" />';
	}
	
	private function isSecuredForm() {
		if(empty($_SESSION['formToken']))
			return false;
		
		if($_SESSION['formToken'] == @$_REQUEST['form_token'])
			return true;
			
		return false;
	}
}

?>