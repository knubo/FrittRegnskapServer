<?php


/*
 * Created on Apr 13, 2007
 *
 * Fetches default values for registering a new regn_line.
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "info";
$data = json_decode(array_key_exists("action", $_REQUEST) ? $_REQUEST["data"] : 0);

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

switch ($action) {
	case "info" :
		$standard = new AccountStandard($db);
		$accSemester = new AccountSemester($db);

		$ret = $standard->getValues(array (
			AccountStandard :: CONST_YEAR,
			AccountStandard :: CONST_MONTH,
			AccountStandard :: CONST_SEMESTER
		));

		$year = $ret[AccountStandard :: CONST_YEAR];
		$month = $ret[AccountStandard :: CONST_MONTH];
		$semester = $ret[AccountStandard :: CONST_SEMESTER];

		$res = array (
			"year" => $year,
			"month" => $month,
			"semester" => $semester,
			"semesters" => $accSemester->getAll()
		);

		echo json_encode($res);
		break;
	case "set" :
		$db->begin();
		$standard = new AccountStandard($db);
		$year = $data["year"];
		$monht = $data["month"];
		$standard->setValue(AccountStandard :: CONST_YEAR, $year);
		$standard->setValue(AccountStandard :: CONST_MONTH, $month);
		$standard->setValue(AccountStandard :: CONST_SEMESTER, $data["semester"]);
		$ib = $data["ib"];

		if (count($ib) > 0) {
			$acAccountLine = new AccountLine($db);
			$acAccountLine->setNewLatest("IB", 1, $year, $month);
			$acAccountLine->store($month, $year);

			$endTransferPost = 8960;
			foreach ($ib as $post => $amount) {
				$acAccountLine->addPostSingleAmount($acAccountLine->getId(), -1, $endTransferPost, $amount);
				$acAccountLine->addPostSingleAmount($acAccountLine->getId(), 1, $post, $amount);
			}

		}

		$db->commit();
		break;
}
?>

