<?php
/*
 * Created on May 12, 2007
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/accounting/accountsemestermembership.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/accounting/accountmemberprice.php");
include_once ("../../classes/accounting/helpers/memberships.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();
$regnSession->checkWriteAccess();

$actions = Memberships::parseParams($_REQUEST);

$db->begin();
Memberships::store($db, $actions);
$db->commit();

$arr = array();
$arr["result"] = 1;

echo json_encode($arr);

?>
