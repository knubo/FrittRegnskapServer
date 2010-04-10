<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$file = array_key_exists("file", $_REQUEST) ? $_REQUEST["file"] : "";

switch($action) {
    case "list":
        $prefix = "";
        if(AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix()."/";
        }

        if(!is_dir("../../storage/$prefix")) {
            mkdir("../../storage/$prefix");
        }

        $directory = dir("../../storage/$prefix");

        $res = array();
        while(false !== ($data = $directory->read())) {
            if($data[0] != '.') {
                $res[] = array("name"=> $data, "size" => Strings::formatBytes(filesize("../../storage/$prefix/$data")));
            }
        }

        echo json_encode($res);

        break;
    case "get":
        $prefix = "";
        if(AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix()."/";
        }

        if($file[0] == '.') {
            echo "0";
        } else {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile("../../storage/".$prefix.$file);
        }

        break;

    case "delete":
        $res = array();

        $prefix = "";
        if(AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix()."/";
        }


        if($file[0] == '.') {
            $result["status"] = 0;
        } else {
            $res["result"] = unlink("../../storage/".$prefix.$file) ? 1 : 0;
            $logger->log("info","files", "Deleted: $fileName");
        }

        echo json_encode($res);

        break;
    case "upload":
        $regnSession->checkWriteAccess();

        $fileName = $_FILES['uploadFormElement']['name'];

        $fileName = Strings::whitelist($fileName);

        $result = array();

        $prefix = "";
        if(AppConfig::USE_QUOTA) {
            $prefix = $regnSession->getPrefix()."/";
        }


        if($fileName[0] == '.') {
            $result["status"] = 0;
        } else {
            $result["status"] = copy($_FILES['uploadFormElement']['tmp_name'], "../../storage/".$prefix.$fileName) ? 1 : 0;
        }

        $logger->log("info","files", "Uploaded: $fileName");

        echo json_encode($result);
        break;
}

?>