<?php
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/reporting/reportuserbirthdate.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

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

date_default_timezone_set(AppConfig::TIMEZONE);
$now = getdate();
$thisYear = $now["year"];

$result = array ();

foreach ($users as $one) {
	$birth = $one->getBirthdate();

	if ($birth) {
        $parts = explode('.', $birth);

        if(count($parts) == 3) {
            $age = $thisYear - $parts[2] -1;
        } else {
            $age = null;
        }
	} else {
		$age = null;
	}
    $gender = $one->gender;

    if(!$gender) {
    	$gender = "?";
    }

	$one = array ();

    if(array_key_exists("$gender$age", $result)) {
        $result["$gender$age"] = $result["$gender$age"] + 1;
    } else {
    	$result["$gender$age"] = 1;
    }

}

echo json_encode($result);
?>
