<?php


/*
 * Created on Apr 15, 2007
 *
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "query";
$line = array_key_exists("line", $_REQUEST) ? $_REQUEST["line"] : 1;
$debet = array_key_exists("debet", $_REQUEST) ? $_REQUEST["debet"] : 0;
$post_type = array_key_exists("post_type", $_REQUEST) ? $_REQUEST["post_type"] : 0;
$amount = array_key_exists("amount", $_REQUEST) ? $_REQUEST["amount"] : 0;
$id = array_key_exists("id", $_REQUEST) ? $_REQUEST["id"] : 0;
$project = array_key_exists("project", $_REQUEST) ? $_REQUEST["project"] : 0;
$person = array_key_exists("person", $_REQUEST) ? $_REQUEST["person"] : 0;

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();


$accPost = new AccountPost($db, $line, $debet, $post_type, $amount, $id, $project, $person);

switch ($action) {
	case "delete" :
        $regnSession->checkWriteAccess();

		$res = $accPost->delete($line, $id);

        $arr = array();
	 	if($res) {
            $arr["result"] = $res.":".$accPost->sumForLine($line);
	 	} else {
	 		$arr["result"] = 0;
	 	}
        echo json_encode($arr);
		break;
	case "insert" :
        $regnSession->checkWriteAccess();
		$accPost->store();

        $arr = array();
		if($accPost->getId()) {
		   $arr["result"] = $accPost->getId().":".$accPost->sumForLine($line);
		} else {
            $arr["result"] = 0;
		}
        echo json_encode($arr);
		break;
	default :
		die("Missing action");
}
?>

