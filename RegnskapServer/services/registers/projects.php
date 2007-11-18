<?php


/*
 * Created on Apr 13, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>
<?php


/*
 * Created on Apr 12, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountproject.php");
include_once ("../../classes/auth/RegnSession.php");

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$accProj = new AccountProject($db);

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";
$project = array_key_exists("project", $_REQUEST) ? $_REQUEST["project"] : "";
$description = array_key_exists("description", $_REQUEST) ? $_REQUEST["description"] : "all";

switch ($action) {
	case "all" :
		$all = $accProj->getAll();
		echo json_encode($all);
		break;
	case "save" :
        $regnSession->checkWriteAccess();

		$accProj->setProject($project);
		$accProj->setDescription($description);
		$accProj->save();

		if (!$project) {
			echo json_encode($accProj);
		} else {
            $result = array();
            $result["result"] = $db->affected_rows();
            echo json_encode($result);
		}
		break;
}
?>
