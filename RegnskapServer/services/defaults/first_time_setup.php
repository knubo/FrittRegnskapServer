<?php


/*
 * Created on Apr 13, 2007
 *
 * Fetches default values for registering a new regn_line.
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "info";
$data = json_decode(array_key_exists("data", $_REQUEST) ? $_REQUEST["data"] : 0);

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

switch ($action) {
    case "info" :
        $standard = new AccountStandard($db);

        $ret = $standard->getValues(array (
        AccountStandard :: CONST_YEAR,
        AccountStandard :: CONST_MONTH,
        ));

        $year = $ret[AccountStandard :: CONST_YEAR];
        $month = $ret[AccountStandard :: CONST_MONTH];
        
        $res = array (
			"year" => $year,
			"month" => $month,
        );

        echo json_encode($res);
        break;
    case "set" :
        $db->begin();
        $standard = new AccountStandard($db);
        $year = $data->year;
        $month = $data->month;
        $spring = $data->spring;
        $fall = $data->fall;

        $semesterIsFall = $data->semester;
        $standard->setValue(AccountStandard :: CONST_YEAR, $year);
        $standard->setValue(AccountStandard :: CONST_MONTH, $month);
        $standard->setValue(AccountStandard :: CONST_FIRST_TIME_SETUP, 1);
        $ib = $data->IB;

        $gotOne = 0;
        foreach($ib as $post => $amount) {
            $gotOne = 1;
        }
        
        if ($gotOne) {
            $acAccountLine = new AccountLine($db);
            $acAccountLine->setNewLatest("IB", 1, $year, $month);
            $acAccountLine->store($month, $year);

            $endTransferPost = $standard->getOneValue(AccountStandard::CONST_IB_POST);
            
            foreach ($ib as $post => $amount) {
                $acAccountLine->addPostSingleAmount($acAccountLine->getId(), -1, $endTransferPost, $amount);
                $acAccountLine->addPostSingleAmount($acAccountLine->getId(), 1, $post, $amount);
            }

        }

        for ($i = 0; $i < 10; $i++) {
            $prefix = $regnSession->getPrefix();
            $prep = $db->prepare("insert into " . $prefix . "semester (description, year, fall) values (?,?,?)");

            $desc = $i % 2 == 0 ? "$spring $year" : "$fall $year";


            if ($i > 0 && $i % 2 == 0) {
                $year++;
            }

            $prep->bind_params("sii", $desc, $year, $i % 2);
            $prep->execute();

            if(!$semesterId && ($i % 2) == $semesterIsFall) {
                $semesterId = $db->insert_id();
            }

        }

        $standard->setValue(AccountStandard :: CONST_SEMESTER, $semesterId);


        $db->commit();
        echo json_encode(array("result" => 1));
        break;
}
?>


