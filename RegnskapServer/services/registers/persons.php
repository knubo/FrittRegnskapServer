<?php
/*
 * Created on Apr 12, 2007
 *
 */
include_once ("../../conf/AppConfig.php"); 
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/validators/emailvalidator.php");
include_once ("../../classes/validators/validatorstatus.php");
include_once ("../../classes/auth/RegnSession.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "all";
$firstname = array_key_exists("firstname", $_REQUEST) ? $_REQUEST["firstname"] : "";
$birthdate = array_key_exists("birthdate", $_REQUEST) ? $_REQUEST["birthdate"] : "";
$lastname = array_key_exists("lastname", $_REQUEST) ? $_REQUEST["lastname"] : "";
$email = array_key_exists("email", $_REQUEST) ? $_REQUEST["email"] : "";
$address = array_key_exists("address", $_REQUEST) ? $_REQUEST["address"] : "";
$postnmb = array_key_exists("postnmb", $_REQUEST) ? $_REQUEST["postnmb"] : "";
$city = array_key_exists("city", $_REQUEST) ? $_REQUEST["city"] : "";
$country = array_key_exists("country", $_REQUEST) ? $_REQUEST["country"] : "";
$phone = array_key_exists("phone", $_REQUEST) ? $_REQUEST["phone"] : "";
$cellphone = array_key_exists("cellphone", $_REQUEST) ? $_REQUEST["cellphone"] : "";
$employee = array_key_exists("employee", $_REQUEST) ? $_REQUEST["employee"] : "";
$id = array_key_exists("id", $_REQUEST) ? $_REQUEST["id"] : "";
$onlyEmp = array_key_exists("onlyemp", $_REQUEST) ? $_REQUEST["onlyemp"] : "";
$queryMembership = array_key_exists("getmemb", $_REQUEST) ? $_REQUEST["getmemb"] :1;
$newsletter = array_key_exists("newsletter", $_REQUEST) ? $_REQUEST["newsletter"] : 0;
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
		$accPers = new AccountPerson($db);
		$accPers->load($id);
		echo json_encode($accPers) ;
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
		echo json_encode($accPers->search($queryMembership));
		break;
	case "save" :
        $regnSession->checkWriteAccess();
    
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
		echo $accPers->save();
		break;
}
?>
