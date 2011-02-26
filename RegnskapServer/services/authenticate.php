<?php


/* Does basic authentication and send back URL where the client should return. */

include_once ("../conf/AppConfig.php");
include_once ("../classes/auth/User.php");
include_once ("../classes/auth/Master.php");
include_once ("../classes/util/DB.php");
include_once ("../classes/util/strings.php");
include_once ("../classes/auth/RegnSession.php");
include_once ("../classes/accounting/accountperson.php");
include_once ("../classes/reporting/emailer.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "login";

switch ($action) {
    case "hash":
        $host = $_SERVER["SERVER_NAME"];
        $split = explode(".",$host);
        $dbn = DB::dbhash($split[0]);
        echo "Hash is: $dbn for ".$split[0];
        break;

    case "secret":
        $db = new DB(0, DB::MASTER_DB);
        $master = new Master($db);
        $masterRecord = $master->get_master_record();

        if(!$masterRecord) {
            $arr = array (
				'error' => 'Ikke identifisert database.'
				);
				die(json_encode($arr));
        }

        $secret =  array_key_exists("secret", $_REQUEST) ? $_REQUEST["secret"] : 0;
        $username =  array_key_exists("username", $_REQUEST) ? $_REQUEST["username"] : 0;

        if(!$secret || !$username) {
            die("Lenken du har fulgt er ikke gyldig.");
        }

        $timepos = substr($secret, 0, strpos($secret, "-"));

        if(time() > $timepos) {
            die("Glemt passord lenken er ikke lengre gyldig.");
        }

        $dbp = $masterRecord["parentdbprefix"] ? $masterRecord["parentdbprefix"] : $masterRecord["dbprefix"];
        $dbu = new DB(0, $masterRecord["parenthostprefix"] ? DB::dbhash($masterRecord["parenthostprefix"]) : 0);
         
        $accUser = new User($dbu);
        $ok =  $accUser->authenticateBySecret($secret, $username, $dbp);

        if(!$ok) {
            $emailer->sendEmail("Mulig hackefors¿k", "admin@frittregnskap.no", "Detaljer er: $username $secret ".json_encode($_SERVER),"admin@frittregnskap.no",0);

            die("Lenken du har fulgt er en engangslenke og er ikke lengre gyldig.");
        }

        $emailer->sendEmail("Glemt passord innlogging", "admin@frittregnskap.no", "Detaljer er: $username $secret ".json_encode($_SERVER),"admin@frittregnskap.no",0);

        $sess = new RegnSession(new DB(), $masterRecord["dbprefix"]);

        if(!session_start()) {
            die("Failed to start session");
        }

        $sess->setSessionVarialbesForUser($username, $accUser, $masterRecord);

        $accPerson = new AccountPerson($dbu, $dbp);
        $accPerson->setSecret($accUser->getPersonId());

        session_write_close();

        header("Location: http://".$_SERVER["SERVER_NAME"]."/prg/AccountingGWT.html");

        break;

    case "adminlogin":
        $db = new DB(0, DB::MASTER_DB);
        $master = new Master($db);
        $masterRecord = $master->get_master_record();

        if(!$masterRecord) {
            $arr = array (
				'error' => 'Ikke identifisert database.'
				);
				die(json_encode($arr));
        }

        if($masterRecord["secret"] != $_REQUEST["secret"]) {
            die("Bad bad bad!");
        }

        /* Update so it only works once */
        $master->updateSecret($masterRecord["id"]);

        $dbu = new DB();
        $sess = new RegnSession($dbu, $masterRecord["dbprefix"]);

        if(!session_start()) {
            die("Failed to start session");
        }
         
        $sess->setSessionVariablesForAdmin($masterRecord);

        session_write_close();

        header("Location: http://".$_SERVER["SERVER_NAME"]."/prg/AccountingGWT.html");

        break;

    case "forward":
        $forward = 1;
    case "login" :
        $user = $_REQUEST["user"];
        $password = $_REQUEST["password"];

        if (!$user || !$password) {
            die("Must supply user and password.");
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

        $sess = new RegnSession(new DB());

        $dbp = $masterRecord["parentdbprefix"] ? $masterRecord["parentdbprefix"] : $masterRecord["dbprefix"];
        $dbu = new DB(0, $masterRecord["parenthostprefix"] ? DB::dbhash($masterRecord["parenthostprefix"]) : 0);

        $auth = new User($dbu);
        if ($auth->authenticate($user, $password, $dbp) == User :: AUTH_OK) {

            if(!session_start()) {
                die("Failed to start session");
            }
             
            $sess->setSessionVarialbesForUser($user, $auth, $masterRecord);

            $arr = array (
				'result' => 'ok', 
            );

            session_write_close();

        } else {
            $arr = array (
            	'result ' => 'failed',
				'error' => 'Ugyldig brukernavn eller passord.',
                'dbprefix' => $masterRecord["dbprefix"]
            );
        }

        if($forward) {
            header("Location: http://".$_SERVER["SERVER_NAME"]."/prg/AccountingGWT.html");
        }

        echo json_encode($arr);
        break;
    case "installations":
        $db = new DB(0, DB::MASTER_DB);
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