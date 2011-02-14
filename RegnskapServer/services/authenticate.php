<?php


/* Does basic authentication and send back URL where the client should return. */

include_once ("../conf/AppConfig.php");
include_once ("../classes/auth/User.php");
include_once ("../classes/auth/Master.php");
include_once ("../classes/util/DB.php");
include_once ("../classes/util/strings.php");
include_once ("../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "login";

switch ($action) {
    case "hash":
        $host = $_SERVER["SERVER_NAME"];
        $split = explode(".",$host);
        $dbn = DB::dbhash($split[0]);
        echo "Hash is: $dbn for ".$split[0];
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
         
        $_SESSION["prefix"] = $masterRecord["dbprefix"];
        $_SESSION["diskquota"] = $masterRecord["diskquota"];
        $_SESSION["username"] = "fradmin";
        $_SESSION["readonly"] = 0;
        $_SESSION["reducedwrite"] = 0;
        $_SESSION["project_required"] = 0;
        $_SESSION["person_id"] = 0;
        $_SESSION["can_see_secret"] = 0;
        $_SESSION["ip"] = $_SERVER["REMOTE_ADDR"];
       
        if($masterRecord["archive_limit"]) {
            $_SESSION["archive_limit"] = $masterRecord["archive_limit"];
        } else {
            $_SESSION["archive_limit"] = 2;
        }
        if($masterRecord["reduced_mode"]) {
            $_SESSION["reduced_mode"] = $masterRecord["reduced_mode"];
        } else {
            $_SESSION["reduced_mode"] = 0;
        }
        if($masterRecord["parentdbprefix"]) {
            $_SESSION["parentdbprefix"] = $masterRecord["parentdbprefix"];
        } else {
            $_SESSION["parentdbprefix"] = 0;
        }

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
        $dbu = new DB();
        $sess = new RegnSession($dbu, $masterRecord["dbprefix"]);

        $dbp = $masterRecord["parentdbprefix"] ? $masterRecord["parentdbprefix"] : $masterRecord["dbprefix"];

        $auth = new User($dbu);
        if ($auth->authenticate($user, $password, $dbp) == User :: AUTH_OK) {

            if(!session_start()) {
                die("Failed to start session");
            }
             
            $_SESSION["prefix"] = $masterRecord["dbprefix"];
            $_SESSION["diskquota"] = $masterRecord["diskquota"];
            $_SESSION["username"] = $user;
            $_SESSION["readonly"] = $auth->hasOnlyReadAccess();
            $_SESSION["reducedwrite"] = $auth->hasReducedWrite();
            $_SESSION["project_required"] = $auth->hasProjectRequired();
            $_SESSION["person_id"] = $auth->getPersonId();
            $_SESSION["can_see_secret"] = $auth->canSeeSecret();
            $_SESSION["ip"] = $_SERVER["REMOTE_ADDR"];
            
            if($masterRecord["archive_limit"]) {
                $_SESSION["archive_limit"] = $masterRecord["archive_limit"];
            } else {
                $_SESSION["archive_limit"] = 2;
            }
            if($masterRecord["reduced_mode"]) {
                $_SESSION["reduced_mode"] = $masterRecord["reduced_mode"];
            } else {
                $_SESSION["reduced_mode"] = 0;
            }
            if($masterRecord["parentdbprefix"]) {
                $_SESSION["parentdbprefix"] = $masterRecord["parentdbprefix"];
            } else {
                $_SESSION["parentdbprefix"] = 0;
            }

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