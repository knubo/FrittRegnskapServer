<?php


/*
 * Created on Apr 12, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountperson.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "query";
$firstname = array_key_exists("firstname", $_REQUEST) ? $_REQUEST["firstname"] : "";
$lastname = array_key_exists("lastname", $_REQUEST) ? $_REQUEST["lastname"] : "";
$email = array_key_exists("email", $_REQUEST) ? $_REQUEST["email"] : "";
$postnmb = array_key_exists("postnmb", $_REQUEST) ? $_REQUEST["postnmb"] : "";
$city = array_key_exists("city", $_REQUEST) ? $_REQUEST["city"] : "";
$country = array_key_exists("country", $_REQUEST) ? $_REQUEST["country"] : "";
$phone = array_key_exists("phone", $_REQUEST) ? $_REQUEST["phone"] : "";
$cellphone = array_key_exists("cellphone", $_REQUEST) ? $_REQUEST["cellphone"] : "";
$employee = array_key_exists("employee", $_REQUEST) ? $_REQUEST["employee"] : "";
$id = array_key_exists("id", $_REQUEST) ? $_REQUEST["id"] : "";
$onlyEmp = array_key_exists("onlyemp", $_REQUEST) ? $_REQUEST["onlyemp"] : "";

$db = new DB();

switch ($action) {
	case "all" :
		$accPers = new AccountPerson($db);
		$columnList = $accPers->getAll($onlyEmp);
		echo json_encode($columnList);
		break;
	case "save" :
		$accPers = new AccountPerson($db);
		$accPers->setId($id);
		$accPers->setFirstname($firstname);
		$accPers->setLastname($lastname);
		$accPers->setIsEmployee($employee);
		$accPers->setAddress($address);
		$accPers->setPostnmb($postnmb);
		$accPers->setPhone($phone);
		$accPers->setEmail($email);
		$accPers->setCellphone($cellphone);
		
		echo $accPers->save();
		break;
}
?>
