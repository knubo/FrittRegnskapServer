<?php


/* Does basic authentication and send back URL where the client should return. */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/auth/PortalUser.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/auth/User.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/reporting/emailer.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/validators/emailvalidator.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "login";

switch ($action) {

    case "status":
        $host = $_SERVER["SERVER_NAME"];
        $split = explode(".",$host);
        $db = new DB(0, DB::MASTER_DB);

        $prep = $db->prepare("select portal_status, portal_title from installations where hostprefix = ?");
        $prep->bind_params("s", $split[0]);

        $result = $prep->execute();
        $data = array_shift($result);

        if(!$data["portal_status"]) {
            $data["portal_status"] = 0;
        }

        $data["eventEnabled"] = AppConfig::EVENT_ENABLED;

        echo json_encode($data);
        break;

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
    case "connect":
        $email = $_REQUEST["email"];
        if(!array_key_exists("test",$_REQUEST)) {
            header("Content-Type: application/json");
        }

        if(!$email) {
            die(json_encode(array("error"=>"Inngi epostadresse.")));
        }
        if(!EmailValidator::check_email_address($email)) {
            die(json_encode(array("error"=>"Ugyldig epostadresse.")));
        }


        $db = new DB(0, DB::MASTER_DB);
        $master = new Master($db);
        $masterRecord = $master->get_master_record();

        if(!$masterRecord) {
            die('Ikke identifisert database.');
        }
        $dbu = new DB();

        $accPerson = new AccountPerson($dbu);

        $res = $accPerson->searchByEmailInDb($email, $masterRecord["dbprefix"]);

        if(count($res) == 0) {
            echo json_encode(array("error"=>"Du er ikke registrert i regnskapsdatabasen."));
        } else if(count($res) > 1) {
            echo json_encode(array("error"=>"Du er registrert ".count($res)." ganger i regnskapsdatabasen. Ta kontakt med styret i din klubb slik at de kan fikse dette."));
            break;
        } else {
            $person = $res[0];
            $secret = $accPerson->setSecret($person["id"],$masterRecord["dbprefix"]);

            $standard = new AccountStandard($dbu, $masterRecord["dbprefix"]);


            $emailer = new Emailer();

            $body = "Linken under gir direkte innlogging til medlemsportalen til Fritt Regnskap. Etter innlogging kan du endre til et nytt passord.\n\n".
             "http://".$_SERVER["SERVER_NAME"]."/RegnskapServer/services/portal/portal_authenticate.php?action=secret&email=$email&id=".$person["id"]."&secret=".$secret.
            "\n\nVenligst hilsen Fritt Regnskap.\n\n".
            "Om du mottar denne eposten og ikke har bedt om engangslink fra medlemsportalen kan du ignorere denne eposten og ditt passord forblir uendret.";
            $sender = $standard->getOneValue(AccountStandard :: CONST_EMAIL_SENDER);
            $emailer->sendEmail("Nytt engangspassord til medlemsportal for frittregnskap.no",$email, $body, $sender,0);

            echo json_encode(array("status"=> "ok"));
        }

        break;

    case "password":

        $db = new DB();
        $regnSession = new RegnSession($db,0, "portal");
        $regnSession->auth();
        $personId = $regnSession->getPersonId();
        $password = $_REQUEST["password"];

        if(!$password) {
            die("Password required");
        }

        $accPerson = new AccountPerson($db);

        $accPerson->updatePortalPassword($personId, $password);

        echo json_encode(array("status"=> "ok"));
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
        $accPerson = new AccountPerson($dbu);

        if(!$accPerson->requirePortaluserSecretMatchAndUpdateSecret($secret, $id, $masterRecord["dbprefix"])) {
            die("Ditt engangspassord er ugyldig.");
        }

        $sess = new RegnSession($dbu, $masterRecord["dbprefix"], "portal");

        if(!session_start()) {
            die("Failed to start session");
        }

        $_SESSION["prefix"] = $masterRecord["dbprefix"];
        $_SESSION["username"] = "secret_id";
        $_SESSION["person_id"] = $id;
        $_SESSION["diskquota"] = $masterRecord["diskquota"];

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

        $status = $auth->authenticate($user, $password, $masterRecord["dbprefix"]);

        if ($status == PortalUser :: AUTH_OK) {
            if(!session_start()) {
                die("Failed to start session");
            }

            $_SESSION["prefix"] = $masterRecord["dbprefix"];
            $_SESSION["username"] = $user;
            $_SESSION["person_id"] = $auth->getPersonId();
            $_SESSION["diskquota"] = $masterRecord["diskquota"];

            $arr = array (
				'result' => 'ok', 'person_id' => $auth->getPersonId()
            );

            session_write_close();

        } else if($status == PortalUser::AUTH_BLOCKED) {
            $arr = array (
				'error' => 'Din bruker er sperret.',
                'dbprefix' => $masterRecord["dbprefix"]
            );

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