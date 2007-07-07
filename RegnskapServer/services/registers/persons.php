<?php


/*
 * Created on Apr 12, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include_once ("../../conf/AppConfig.php"); 
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountsemester.php");
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
 
$db = new DB();

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
		echo $accPers->save();
		break;
}
?>
