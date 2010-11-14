<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/admin/installer.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/reporting/emailer.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();



switch($action) {
    case "portalinfo":
        $dbPortal = new DB(0, DB::MASTER_DB);
        $master = new Master($dbPortal);

        $info = $master->get_master_record();

        echo json_encode(array("portal_title"=> $info["portal_title"], "portal_status"=> $info["portal_status"] ? $info["portal_status"] : 0));
        break;

    case "all":
        $accPerson = new AccountPerson($db);
        echo json_encode($accPerson->getAllPortal());
        break;

    case "image":
        $prefix = "";
        if(AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix();
        }
        $personId = $_REQUEST["image"];
        $accPerson = new AccountPerson($db);
        $personData = $accPerson->getOnePortal($personId);
        $hiddenPrefx = $personData["show_image"] ? "" : "hidden_";
        $file = "profile_images/".$hiddenPrefx."profile_$personId.jpg";

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


    case "saveinfo":
        $dbPortal = new DB(0, DB::MASTER_DB);
        $master = new Master($dbPortal);

        $info = $master->update_portal_info($_REQUEST["portal_title"]);

        echo json_encode(array("status"=> 1));
        break;
    case "change":
        $dbPortal = new DB(0, DB::MASTER_DB);
        $master = new Master($dbPortal);
        $newStatus = $_REQUEST["status"];
        $info = $master->get_master_record();

        if($newStatus == 4 && !$info["portal_status"]) {
            $subject = urldecode("Portalrequest for : ".$info["hostprefix"].".frittregnskap.no");
            $body = "";

            $email = "admin@frittregnskap.no";
            $emailer = new Emailer();

            $status = $emailer->sendEmail($subject, $email, $body, $email, 0);
        } else if($newStatus == 1 && $info["portal_status"] == 3) {
            /* Okay */
        }  else if($newStatus == 3 && $info["portal_status"] == 1) {
            /* Okay */
        } else {
            die("Not a legal status change");
        }

        $master->update_portal_status($newStatus);
        echo json_encode(array("status"=> 1));
        break;
}

?>