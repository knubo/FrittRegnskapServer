<?php
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/reporting/reportuserbirthdate.php");
include_once ("../../classes/auth/RegnSession.php");

$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : 0;
$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

if (!$year) {
    $standard = new AccountStandard($db);
    $year = $standard->getOneValue(AccountStandard::CONST_YEAR);
}

$accYearMem = new AccountYearMembership($db);

$users = $accYearMem->getReportUsersBirthdate($year);

$now = getdate();
$thisYear = $now["year"];

$year_unset = array();
$year_19 = array();
$year_25 = array();
$year_30 = array();
$year_40 = array();
$year_above = array();
$year_wrong = array();

foreach($users as $one) {
  $birth = $one->getBirthdate();

  $age = $thisYear - substr($birth, -4) - 1;

  if($age < 150) {
    $one->setAge($age);
  } else {
  	$one->setAge("");
  }
  if(!$birth) {
    $year_unset[] = $one;
  } else if(strlen($birth) != 10 || $age > 150) {
    $year_wrong[] = $one;
  } else if($age <= 19) {
    $year_19[] = $one;

  } else if($age <= 25) {
    $year_25[] = $one;
  } else if($age <= 30) {
    $year_30[] = $one;
  } else if($age <= 40) {
    $year_40[] = $one;
  } else {
    $year_above[] = $one;
  }
}

$result = array();
$result["year_unset"] = $year_unset;
$result["year_wrong"] = $year_wrong;
$result["year_19"] = $year_19;
$result["year_25"] = $year_25;
$result["year_30"] = $year_30;
$result["year_40"] = $year_40;
$result["year_above"] = $year_above;

echo json_encode($result);

?>