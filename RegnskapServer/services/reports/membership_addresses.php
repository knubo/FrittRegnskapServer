<?php
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");

$year = array_key_exists("year", $_REQUEST) ? $_GET["year"] : 2007;
$db = new DB();
 
if (!$year) {
    $standard = new AccountStandard($db);
    $year = $standard->getOneValue("STD_YEAR");
}

?>
