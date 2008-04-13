<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountplan.php");
include_once ("../../classes/auth/RegnSession.php");

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "list";


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
	case "upload":
        $regnSession->checkWriteAccess();

        //TODO Check that file does not exist.

        $fileName = $_FILES['uploadFormElement']['name'];

        $result = array();

        if($fileName[0] == '.') {
            $result["status"] = 0;
        } else {
            $result["status"] = copy($_FILES['uploadFormElement']['tmp_name'], "../../storage/$fileName");
        }


        echo json_encode($result);
        break;
}

?>