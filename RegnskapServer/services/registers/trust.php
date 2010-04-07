<?php
/*
 * Created on Aug 22, 2007
 *
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accounttrust.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

 
$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : null;
$fond = array_key_exists("fond", $_REQUEST) ? $_REQUEST["fond"] : null;
$description = array_key_exists("description", $_REQUEST) ? $_REQUEST["description"] : null;
 
switch($action) {
	case "save":
        $db = new DB();
        $accTrust = new AccountTrust($db);
        $res = $accTrust->saveFondType($fond, $description);
        $retval = array();
        $retval["result"] = $res > 0 ? 1 : 0;
        echo json_encode($retval);
        break;
    default:
        die("Unknown action: $action");
}
 
?>
