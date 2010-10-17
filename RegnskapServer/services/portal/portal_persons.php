<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

//header("Content-Type: application/json");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "get";
$data = array_key_exists("data", $_REQUEST) ? $_REQUEST["data"] : 0;

$db = new DB();
$regnSession = new RegnSession($db,0, "portal");
$username = $regnSession->auth();


switch($action) {
    case "me":
        $personId = $regnSession->getPersonId();
        $accPerson = new AccountPerson($db);

        $personData = $accPerson->getOnePortal($personId);

        $file = "profile_images/profile_$personId.jpg";
        $prefix = "";
        if(AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix();
        }

        $personData["has_profile_image"] = file_exists("../../storage/".$prefix."/".$file) ? 1 : 0;



        echo json_encode($personData);
        break;

    case "myimage":
        $prefix = "";
        if(AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix();
        }
        $personId = $regnSession->getPersonId();

        $file = "profile_images/profile_$personId.jpg";

        header('Content-Description: File Transfer');
        header('Content-Type: image');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize("../../storage/".$prefix."/".$file));
        ob_clean();
        flush();
        readfile("../../storage/".$prefix."/".$file);
        break;

    case "imageupload":

        $prefix = "";
        if(AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix();
        }
        $personId = $regnSession->getPersonId();

        $file = "profile_images/profile_$personId.jpg";

        echo copy($_FILES['uploadfile']['tmp_name'], "../../storage/".$prefix."/".$file);
        echo json_encode($_REQUEST);
        echo json_encode($_FILES);

        break;

    case "save":
        $personId = $regnSession->getPersonId();
        $accPerson = new AccountPerson($db);
        $accPerson->savePortalUser($personId, json_decode($data));
        echo json_encode(array("result" => "ok"));
        break;

}