<?php


/* Does basic authentication and send back URL where the client should return. */

include_once ("../conf/AppConfig.php");
include_once ("../classes/auth/User.php");
include_once ("../classes/auth/Master.php");
include_once ("../classes/util/DB.php");
include_once ("../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "login";

switch ($action) {
	case "login" :
		$db = new DB();
		$sess = new RegnSession($db);

		$user = $_REQUEST["user"];

		$password = $_REQUEST["password"];

		if (!$user || !$password) {
			die("Must supply user and password.");
		}

		$auth = new User($db);

		if ($auth->authenticate($user, $password) == User :: AUTH_OK) {
			session_start();
			
			$master = new Master($db);
			
			$_SESSION["prefix"] = $master->calculate_prefix();
			$_SESSION["username"] = $user;
            $_SESSION["readonly"] = $auth->hasOnlyReadAccess();
            $_SESSION["reducedwrite"] = $auth->hasReducedWrite();
            $_SESSION["project_required"] = $auth->hasProjectRequired();
            $_SESSION["person_id"] = $auth->getPersonId();
            $arr = array (
				'result' => 'ok', 
			);

		} else {
			$arr = array (
				'error' => 'Ugyldig brukernavn eller passord.'
			);
		}
		echo json_encode($arr);
		break;
	case "logout" :
		$db = new DB();
		$sess = new RegnSession($db);

        $sessionName = session_name();
		$CookieInfo = session_get_cookie_params();
		if ((empty ($CookieInfo['domain'])) && (empty ($CookieInfo['secure']))) {
			setcookie(session_name(), '', time() - 3600, $CookieInfo['path']);
		}
		elseif (empty ($CookieInfo['secure'])) {
			setcookie(session_name(), '', time() - 3600, $CookieInfo['path'], $CookieInfo['domain']);
		} else {
			setcookie(session_name(), '', time() - 3600, $CookieInfo['path'], $CookieInfo['domain'], $CookieInfo['secure']);
		}
		unset ($_COOKIE[$sessionName]);

        $arr = array('result' => 1);
        echo json_encode($arr);
		break;
}
?>