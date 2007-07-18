<?php


/* Does basic authentication and send back URL where the client should return. */

include_once ("../conf/AppConfig.php");
include_once ("../classes/auth/User.php");
include_once ("../classes/util/DB.php");
include_once ("../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "login";

switch ($action) {
	case "login" :
		$db = new DB();
		$sess = new RegnSession($db);

		$user = $_GET["user"];

		$password = $_GET["password"];

		if (!$user || !$password) {
			die("Must supply user and password.");
		}

		$auth = new User($db);

		if ($auth->authenticate($user, $password) == User :: AUTH_OK) {
			session_start();
			$_SESSION["username"] = $user;
            $_SESSION["readonly"] = $auth->hasOnlyReadAccess();
			$arr = array (
				'url' => '/RegnskapClient/www/no.knubo.accounting.AccountingGWT/AccountingGWT.html'
			);

		} else {
			$arr = array (
				'error' => 'Ulovlig brukernavn eller passord.'
			);
		}
		echo json_encode($arr);
		break;
	case "logout" :
		$db = new DB();
		$sess = new RegnSession($db);

		$CookieInfo = session_get_cookie_params();
		if ((empty ($CookieInfo['domain'])) && (empty ($CookieInfo['secure']))) {
			setcookie(session_name(), '', time() - 3600, $CookieInfo['path']);
		}
		elseif (empty ($CookieInfo['secure'])) {
			setcookie(session_name(), '', time() - 3600, $CookieInfo['path'], $CookieInfo['domain']);
		} else {
			setcookie(session_name(), '', time() - 3600, $CookieInfo['path'], $CookieInfo['domain'], $CookieInfo['secure']);
		}
		unset ($_COOKIE[session_name()]);
		session_destroy();
		echo "1";
		break;
}
?>