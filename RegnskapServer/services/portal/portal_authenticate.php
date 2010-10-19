<?php


/* Does basic authentication and send back URL where the client should return. */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/auth/PortalUser.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/accounting/accountperson.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "login";

switch ($action) {
    case "hash":
        $host = $_SERVER["SERVER_NAME"];
        $split = explode(".",$host);
        $dbn = DB::dbhash($split[0]);
        echo "Hash is: $dbn for ".$split[0];
        break;

    case "sessionvalid":
        $db = new DB();
        $regnSession = new RegnSession($db,0, "portal");
        $regnSession->auth();
        echo json_encode(array());
        break;

    case "forward":
        header("Location: http://".$_SERVER["SERVER_NAME"]."/portal");
        break;
    case "secret":
        $secret = $_REQUEST["secret"];
        $id = $_REQUEST["id"];

        if(!$secret || !$id) {
            die("");
        }
        $db = new DB(0, DB::MASTER_DB);
        $master = new Master($db);
        $masterRecord = $master->get_master_record();

        if(!$masterRecord) {
            die('Ikke identifisert database.');
        }
        $dbu = new DB();
        $sess = new RegnSession($dbu, $masterRecord["dbprefix"], "portal");

        $accPerson = new AccountPerson($dbu);

        if(!$accPerson->requirePortaluserSecretMatchAndUpdateSecret($secret, $id)) {
            die("Ditt engangspassord er ugyldig.");
        }

        if(!session_start()) {
            die("Failed to start session");
        }
         
        $_SESSION["prefix"] = $masterRecord["dbprefix"];
        $_SESSION["username"] = "secret_id";
        $_SESSION["person_id"] = $id;

        session_write_close();

        header("Location: http://".$_SERVER["SERVER_NAME"]."/portal");
        break;

    case "login" :
        $user = $_REQUEST["user"];
        $password = $_REQUEST["password"];

        if (!$user || !$password) {
            die("Must supply user and password.");
        }

        if(!array_key_exists("test",$_REQUEST)) {
            header("Content-Type: application/json");
        }

        $db = new DB(0, DB::MASTER_DB);
        $master = new Master($db);
        $masterRecord = $master->get_master_record();

        if(!$masterRecord) {
            $arr = array (
				'error' => 'Ikke identifisert database.'
				);
				echo json_encode($arr);
				break;
        }
        $dbu = new DB();
        $sess = new RegnSession($dbu, $masterRecord["dbprefix"], "portal");

        $auth = new PortalUser($dbu);

        if ($auth->authenticate($user, $password, $masterRecord["dbprefix"]) == PortalUser :: AUTH_OK) {
            if(!session_start()) {
                die("Failed to start session");
            }
             
            $_SESSION["prefix"] = $masterRecord["dbprefix"];
            $_SESSION["username"] = $user;
            $_SESSION["person_id"] = $auth->getPersonId();
            $arr = array (
				'result' => 'ok', 'person_id' => $auth->getPersonId()
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


    case "logout" :
        $db = new DB();
        $sess = new RegnSession($db,0, "portal");

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