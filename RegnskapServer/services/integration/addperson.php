<?php
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountperson.php");
include_once ("../../classes/validators/emailvalidator.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/reporting/emailer.php");

$db = new DB(0, DB::MASTER_DB);
$master = new Master($db);
$masterRecord = $master->get_master_record();

if(!$masterRecord) {
    $arr = array (
				'error' => 'Ikke identifisert database.'
				);
				die(json_encode($arr));
}

$dbu = new DB(0, $masterRecord["parenthostprefix"] ? DB::dbhash($masterRecord["parenthostprefix"]) : DB::dbhash($masterRecord["hostprefix"]));
$dbp = $masterRecord["parentdbprefix"] ? $masterRecord["parentdbprefix"] : $masterRecord["dbprefix"];

$accStd = new AccountStandard($dbu, $dbp);

$vals = $accStd->getValues(array(AccountStandard::CONST_INTEGRATION_SECRET,
AccountStandard::CONST_INTEGRATION_EMAIL,
AccountStandard::CONST_BIRTHDATE_REQUIRED));

$token = array_key_exists("token", $_REQUEST) ? $_REQUEST["token"] : "";

if($token != $vals[AccountStandard::CONST_INTEGRATION_SECRET]) {
    die("Unknown token");
}
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
$newsletter = array_key_exists("newsletter", $_REQUEST) ? trim($_REQUEST["newsletter"]) : 0;
$gender = array_key_exists("gender", $_REQUEST) ? trim($_REQUEST["gender"]) : 0;

if(!$firstname || strlen($firstname) == 0) {
    die("Fornavn mangler");
}

if(!$lastname || strlen($lastname) == 0) {
    die("Etternavn mangler");
}


if(!$email || strlen($email) == 0) {
    die("Epost mangler");
}

if(!EmailValidator::check_email_address($email)) {
    die("Epost er ugyldig");
}

if(!$address || strlen($address) == 0) {
    die("Adresse mangler");
}

if(!$gender || strlen($gender) == 0) {
    die("Kj¿nn mangler");
}

if((!$birthdate || strlen($birthdate)) && $vals[AccountStandard::CONST_BIRTHDATE_REQUIRED]) {
    die("F¿dselsdato mangler");
}

if(!preg_match("/\d\d\.\d\d\.\d\d\d\d/", $birthdate)) {
    die("F¿dselsdato skal v¾re pŒ format dd.mm.yyyy");
}

$emailer = new Emailer();

$body = "Nyregistering av peron:\nFornavn:$firstname\nEtternavn:$lastname\nEpost:$email\n\nAutomatisk sendt av Fritt Regnskap.\n";
$emailer->sendEmail("Nyregistrering/New entry", $vals[AccountStandard::CONST_INTEGRATION_EMAIL],
$body,"admin@frittregnskap.no",0);

$accPers = new AccountPerson($dbu, $dbp);
$accPers->setFirstname($firstname);
$accPers->setLastname($lastname);
$accPers->setAddress($address);
$accPers->setPostnmb($postnmb);
$accPers->setCity($city);
$accPers->setCountry($country);
$accPers->setPhone($phone);
$accPers->setEmail($email);
$accPers->setCellphone($cellphone);
$accPers->setBirthdate($birthdate);
$accPers->setNewsletter($newsletter);
$accPers->setGender($gender);

echo json_encode(array("status" => $accPers->save()));
?>