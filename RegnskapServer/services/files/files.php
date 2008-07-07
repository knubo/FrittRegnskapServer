<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/logger.php");
include_once ("../../classes/auth/RegnSession.php");

$db = new DB();
$logger = new Logger($db);
$regnSession = new RegnSession($db);
$regnSession->auth();

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";
$file = array_key_exists("file", $_REQUEST) ? $_REQUEST["file"] : "";

switch($action) {
    case "list":
        $directory = dir("../../storage/");

        $res = array();
        while(false !== ($data = $directory->read())) {
        	if($data[0] != '.') {
                $res[] = $data;
        	}
        }

        echo json_encode($res);

        break;
    case "delete":
        $res = array();

        if($file[0] == '.') {
            $result["status"] = 0;
        } else {
            $res["result"] = unlink("../../storage/$file") ? 1 : 0;
            $logger->log("info","files", "Deleted: $fileName");
        }

        echo json_encode($res);

        break;
	case "upload":
        $regnSession->checkWriteAccess();

        //TODO Check that file does not exist.

        $fileName = $_FILES['uploadFormElement']['name'];

        $fileName = Strings::whitelist($fileName);

        $result = array();

        if($fileName[0] == '.') {
            $result["status"] = 0;
        } else {
            $result["status"] = copy($_FILES['uploadFormElement']['tmp_name'], "../../storage/$fileName") ? 1 : 0;
        }

        $logger->log("info","files", "Uploaded: $fileName");

        echo json_encode($result);
        break;
}

?>