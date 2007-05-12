<?php

/*
 * Created on May 10, 2007
 *
 */

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/accounting/accountstandard.php");

$db = new DB();
$accStandard = new AccountStandard($db);
$accSemester = new AccountSemester($db);
$active_semester = $accStandard->getOneValue("STD_SEMESTER");

$data = array();
$data["semester"] = $accSemester->getSemesterName($active_semester);
$data["month"] = $accStandard->getOnevalue("STD_MONTH"); 
$data["year"] = $accStandard->getOneValue("STD_YEAR");

echo json_encode($data);
?>
