<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/reporting/emailer.php");
include_once ("../../classes/util/fileutil.php");

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
        $hiddenPrefx = $personData["show_image"] ? "" : "hidden_";

        $file = "profile_images/".$hiddenPrefx."profile_$personId.jpg";
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

    case "image":
        $prefix = "";
        if(AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix();
        }
        $personId = Strings::whitelist($_REQUEST["personId"]);

        $file = "profile_images/profile_$personId.jpg";

        if(!file_exists("../../storage/".$prefix."/".$file)) {
            die("No image");
        }

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
        $accPerson = new AccountPerson($db);
        $personData = $accPerson->getOnePortal($personId);

        $hiddenPrefx = $personData["show_image"] ? "" : "hidden_";

        $file = "profile_images/".$hiddenPrefx."profile_$personId.jpg";

        if(!is_dir("../../storage/".$prefix."/profile_images/")) {
            mkdir("../../storage/".$prefix."/profile_images/",0700, true);
        }

        if(AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix()."/";

            if(!is_dir("../../storage/$prefix")) {
                mkdir("../../storage/$prefix");
            }

            $directory = dir("../../storage/$prefix");

            $total = 0;
            while(false !== ($data = $directory->read())) {
                if($data[0] != '.') {
                    if(is_dir("../../storage/$prefix/$data")) {
                        $size = dirSize("../../storage/$prefix/$data");
                    } else {
                        $size = filesize("../../storage/$prefix/$data");
                    }
                    $total += $size;
                }
            }

            $total += filesize($_FILES['uploadFormElement']['tmp_name']);

            $percentUsed = sprintf("%01.2f",(($total / ($regnSession->getQuota() * 1024 * 1024)) * 100));

        }

        if($percentUsed > 100) {
            header("HTTP/1.0 513 Quota exceeded");
            die("Kan ikke laste opp bilde - diskkvoten er oversteget for portalen.");
        }

        $cmd = AppConfig::CONVERT." -adaptive-resize 200x260 ".$_FILES['uploadfile']['tmp_name']." ../../storage/".$prefix."/".$file;
        system($cmd);

        unlink($_FILES['uploadfile']['tmp_name']);

        echo "Profilbilde opplastet";
        
        break;

    case "save":

        if(strpos($data, "<") !== FALSE || strpos($data, "<") !== FALSE) {
            die(json_encode(array("error" =>"Ikke bruk ulovlige tegn som: < eller >")));
        }

        $personId = $regnSession->getPersonId();
        $accPerson = new AccountPerson($db);

        $saveData =  json_decode($data);
        
        if(substr($saveData->homepage, 0, 7) == "http://") {
            $saveData->homepage = substr($saveData->homepage, 7);
        }
        if(substr($saveData->twitter, 0, 7) == "http://") {
            $saveData->twitter = substr($saveData->twitter, 7);
        }
        if(substr($saveData->facebook, 0, 7) == "http://") {
            $saveData->facebook = substr($saveData->facebook, 7);
        }
        if(substr($saveData->linkedin, 0, 7) == "http://") {
            $saveData->linkedin = substr($saveData->linkedin, 7);
        }
        
        
        
        $accPerson->savePortalUser($personId, $saveData);

        $prefix = "";
        if(AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix();
        }

        $filehidden = "profile_images/hidden_profile_$personId.jpg";
        $file = "profile_images/profile_$personId.jpg";

        if($saveData->show_image) {
            if(file_exists("../../storage/".$prefix."/".$filehidden)) {
                rename("../../storage/".$prefix."/".$filehidden, "../../storage/".$prefix."/".$file);
            }
        } else {
            if(file_exists("../../storage/".$prefix."/".$file)) {
                rename("../../storage/".$prefix."/".$file, "../../storage/".$prefix."/".$filehidden);
            }
        }


        echo json_encode(array("result" => "ok"));
        break;

    case "share":
        $prefix = "";
        if(AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix();
        }

        $file = "profile_images/portal_cache.json";
        $cacheFile = "../../storage/".$prefix."/".$file;

        $statInfo = 0;
        if(file_exists($cacheFile)) {
            $statInfo = stat($cacheFile);
        }

        if($statInfo && $statInfo[9] > (time() - (60 * 60 * 24) )) {
            readfile($cacheFile);
        } else {
            $accPerson = new AccountPerson($db);
             
            $data = json_encode($accPerson->getSharedCompactPortalData());
            file_put_contents($cacheFile, $data);
            echo $data;
        }
        break;
}