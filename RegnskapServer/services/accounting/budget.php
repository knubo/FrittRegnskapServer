<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/helpers/membersformatter.php");
include_once ("../../classes/accounting/accountbudget.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/accounting/accountsemestermembership.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/accounting/accountmemberprice.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "init";
$year = array_key_exists("year", $_REQUEST) ? $_REQUEST["year"] : "0";
$budget = array_key_exists("budget", $_REQUEST) ? $_REQUEST["budget"] : "0";
$memberships = array_key_exists("memberships", $_REQUEST) ? $_REQUEST["memberships"] : "0";

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();
$standard = new AccountStandard($db);

switch ($action) {
    case "saveMemberships":
        $accBudget = new AccountBudget($db);
        $res = $accBudget->saveMemberships(json_decode($memberships));
        $result = array();
        $result["result"] = $res;

        echo json_encode($result);
        break;
    case "init" :

        $result = array ();
        $accBudget = new AccountBudget($db);
        $accPrice = new AccountMemberPrice($db);

        $accYear = new AccountYearMembership($db);
        $accCourse = new AccountSemesterMembership($db, "course");
        $accTrain = new AccountSemesterMembership($db, "train");
        $accYouth = new AccountSemesterMembership($db, "youth");
        $accSemester = new AccountSemester($db);
        $result["budget"] = $accBudget->getBudgetData($year);

        if($year == 0 && count($result["budget"]) > 0) {
            $year = $result["budget"][0]["year"];
        }

        $result["membersbudget"] = $accBudget->getMemberships($year);
        $result["members"] = MembersFormatter :: group($accYear->getOverview(), $accCourse->getOverview(), $accTrain->getOverview(), $accYouth->getOverview());
        $result["price"] = $accPrice->getAll();
        $result["budgetYears"] = $accBudget->getAllBudgetYears();
        $result["result"] = $accBudget->getEarningsAndCostsFromAllYears();
        $result["semesters"] = $accSemester->getAll();
        $result["year_post"] = $standard->getOneValue(AccountStandard::CONST_BUDGET_YEAR_POST);
        $result["course_post"] = $standard->getOneValue(AccountStandard::CONST_BUDGET_COURSE_POST);
        $result["train_post"] = $standard->getOneValue(AccountStandard::CONST_BUDGET_TRAIN_POST);
        $result["youth_post"] = $standard->getOneValue(AccountStandard::CONST_BUDGET_YOUTH_POST);

        echo json_encode($result);
        break;
    case "save":
        $budgetObj = json_decode($budget);
        $accBudget = new AccountBudget($db);
        echo json_encode($accBudget->save($year, $budgetObj));
        break;
    case "simplestatus":
        $accBudget = new AccountBudget($db);
        $result = array();
        $result["budget"] = $accBudget->getBudgetData($year);
        $result["result"] = $accBudget->getEarningsAndCostsFromGivenYear($year);
        echo json_encode($result);
        break;
    case "years":
        $accBudget = new AccountBudget($db);
        echo json_encode($accBudget->getAllBudgetYears());
        break;
         
}
?>
