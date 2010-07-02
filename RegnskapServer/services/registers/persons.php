<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/accounting/accountsemestermembership.php");
include_once ("../../classes/accounting/accountyearmembership.php");
include_once ("../../classes/validators/emailvalidator.php");
include_once ("../../classes/validators/validatorstatus.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");


$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "get";
$firstname = array_key_exists("firstname", $_REQUEST) ? trim($_REQUEST["firstname"]) : "";
$birthdate = array_key_exists("birthdate", $_REQUEST) ? trim($_REQUEST["birthdate"]) : "";
$lastname = array_key_exists("lastname", $_REQUEST) ? trim($_REQUEST["lastname"]) : "";
$email = array_key_exists("email", $_REQUEST) ? trim($_REQUEST["email"]) : "";
$address = array_key_exists("address", $_REQUEST) ? trim($_REQUEST["address"]) : "";
$postnmb = array_key_exists("postnmb", $_REQUEST) ? trim($_REQUEST["postnmb"]) : "";
$city = array_key_exists("city", $_REQUEST) ? trim($_REQUEST["city"]) : "";
$country = array_key_exists("country", $_REQUEST) ? trim($_REQUEST["country"]) : "";
$phone = array_key_exists("phone", $_REQUEST) ? trim($_REQUEST["phone"]) : "";
$cellphone = array_key_exists("cellphone", $_REQUEST) ? trim($_REQUEST["cellphone"]) : "";
$employee = array_key_exists("employee", $_REQUEST) ? trim($_REQUEST["employee"]) : "";
$id = array_key_exists("id", $_REQUEST) ? $_REQUEST["id"]: 0;
$onlyEmp = array_key_exists("onlyemp", $_REQUEST) ? trim($_REQUEST["onlyemp"]) : "";
$queryMembership = array_key_exists("getmemb", $_REQUEST) ? trim($_REQUEST["getmemb"]) :1;
$newsletter = array_key_exists("newsletter", $_REQUEST) ? trim($_REQUEST["newsletter"]) : 0;
$hidden = array_key_exists("hidden", $_REQUEST) ? trim($_REQUEST["hidden"]) : 0;
$gender = array_key_exists("gender", $_REQUEST) ? trim($_REQUEST["gender"]) : '';
$secretaddress = array_key_exists("secretaddress", $_REQUEST) ? trim($_REQUEST["secretaddress"]) : '';
$comment = array_key_exists("comment", $_REQUEST) ? trim($_REQUEST["comment"]) : '';


$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();


switch ($action) {
	case "all" :
		$accPers = new AccountPerson($db);
		$columnList = $accPers->getAll($onlyEmp);
		echo json_encode($columnList);
		break;
	case "get" :
	    $accStd = new AccountStandard($db);
	    
		$accPers = new AccountPerson($db);
		$accPers->load($id);
		
		
        $accSemesterMembership = new AccountSemesterMembership($db);
        $accYearMembership = new AccountyearMembership($db);
        $accYouthMembership = new AccountSemesterMembership($db);
        $memberships = array();
        $memberships["course"] = $accSemesterMembership->getUserMemberships($id, "course");
        $memberships["train"] = $accSemesterMembership->getUserMemberships($id, "train");
        $memberships["year"] = $accYearMembership->getUserMemberships($id, "train");
        $memberships["youth"] = $accYouthMembership->getUserMemberships($id, "youth");
        $accPers->Memberships = $memberships;
        
        $accPers->BirthdateRequired = $accStd->getOneValue(AccountStandard::CONST_BIRTHDATE_REQUIRED);
        
		echo json_encode($accPers);
		break;
	case "search" :
		$accPers = new AccountPerson($db);
		$accPers->setId($id);
		$accPers->setFirstname($firstname);
		$accPers->setLastname($lastname);
		$accPers->setIsEmployee($employee);
		$accPers->setAddress($address);
		$accPers->setPostnmb($postnmb);
		$accPers->setCity($city);
		$accPers->setCountry($country);
		$accPers->setPhone($phone);
		$accPers->setEmail($email);
		$accPers->setCellphone($cellphone);
        $accPers->setHidden($hidden);
        $accPers->setGender($gender);
		echo json_encode($accPers->search($queryMembership));
		break;
	case "save" :
        $regnSession->checkReducedWriteAccess();

        $validator = new ValidatorStatus();
        if($email && !EmailValidator::check_email_address($email)) {
        	$validator->addInvalidField("email");
        }
        $validator->dieIfNotValidated();
		$accPers = new AccountPerson($db);
		$accPers->setId($id);
		$accPers->setFirstname($firstname);
		$accPers->setLastname($lastname);
		$accPers->setIsEmployee($employee);
		$accPers->setAddress($address);
		$accPers->setPostnmb($postnmb);
		$accPers->setCity($city);
		$accPers->setCountry($country);
		$accPers->setPhone($phone);
		$accPers->setEmail($email);
		$accPers->setCellphone($cellphone);
        $accPers->setBirthdate($birthdate);
        $accPers->setNewsletter($newsletter);
        $accPers->setHidden($hidden);
        $accPers->setGender($gender);
        $accPers->setSecretaddress($secretaddress);
        $accPers->setComment($comment);

        $res = array();
        $res["result"] = $accPers->save();

        echo json_encode($res);
		break;
}
?>
