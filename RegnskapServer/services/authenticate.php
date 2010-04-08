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
        $user = $_REQUEST["user"];
        $password = $_REQUEST["password"];

        if (!$user || !$password) {
            die("Must supply user and password.");
        }

        $db = new DB();
        $master = new Master($db);
        $masterRecord = $master->get_master_record();
        
        if(!$masterRecord) {
             $arr = array (
				'error' => 'Ikke identifisert database.'
				);
				echo json_encode($arr);
            break;
        }
        $sess = new RegnSession($db, $masterRecord["dbprefix"]);
        
        $auth = new User($db);

        if ($auth->authenticate($user, $password, $masterRecord["dbprefix"]) == User :: AUTH_OK) {
            session_start();
            	
            $_SESSION["prefix"] = $masterRecord["dbprefix"];
            $_SESSION["diskquota"] = $masterRecord["diskquota"];
            $_SESSION["username"] = $user;
            $_SESSION["readonly"] = $auth->hasOnlyReadAccess();
            $_SESSION["reducedwrite"] = $auth->hasReducedWrite();
            $_SESSION["project_required"] = $auth->hasProjectRequired();
            $_SESSION["person_id"] = $auth->getPersonId();
            $arr = array (
				'result' => 'ok', 
            );
            
            session_write_close(); 

        } else {
            $arr = array (
				'error' => 'Ugyldig brukernavn eller passord.',
                'dbprefix' => $masterRecord["dbprefix"]
				);
        }
        echo json_encode($arr);
        break;
    case "installations":
        $db = new DB();
        $master = new Master($db);
        echo json_encode($master->getAllInstallations());
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