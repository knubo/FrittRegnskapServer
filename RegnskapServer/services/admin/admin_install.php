<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/admin/installer.php");
include_once ("../../classes/auth/User.php");
include_once ("../../classes/reporting/emailer.php");

$db = new DB();
$installer = new Installer($db);

$secret = array_key_exists("secret", $_REQUEST) ? trim($_REQUEST["secret"]) : "";
$wikilogin = array_key_exists("wikilogin", $_REQUEST) ? trim($_REQUEST["wikilogin"]) : "";
$phone = array_key_exists("phone", $_REQUEST) ? trim($_REQUEST["phone"]) : "";
$zipcode = array_key_exists("zipcode", $_REQUEST) ? trim($_REQUEST["zipcode"]) : "";
$city = array_key_exists("city", $_REQUEST) ? trim($_REQUEST["city"]) : "";
$address = array_key_exists("address", $_REQUEST) ? trim($_REQUEST["address"]) : "";
$email = array_key_exists("email", $_REQUEST) ? trim($_REQUEST["email"]) : "";
$contact = array_key_exists("contact", $_REQUEST) ? trim($_REQUEST["contact"]) : "";
$clubname = array_key_exists("clubname", $_REQUEST) ? trim($_REQUEST["clubname"]) : "";
$domainname = array_key_exists("domainname", $_REQUEST) ? trim($_REQUEST["domainname"]) : "";
$password = array_key_exists("password", $_REQUEST) ? trim($_REQUEST["password"]) : "";
$superuser = array_key_exists("superuser", $_REQUEST) ? trim($_REQUEST["superuser"]) : "";

$prep = $db->prepare("select * from to_install where secret = ? and wikilogin = ?");
$prep->bind_params("ss", $secret, $wikilogin);
$res = $prep->execute();

if(count($res) == 0) {
    die("Illegal combination of secret and wikilogin provided");
}

$installer->validate($_REQUEST);

$dbprefix = $installer->createUniquePrefix($domainname);

if(!$dbprefix) {
    die("Failed to calculate DB-prefix");
}

$installer->createTables($dbprefix);
$installer->createIndexes($dbprefix);
$installer->addAccountPlan($dbprefix);
$installer->addStandardData($dbprefix);

try {
    $db->begin();

    $prep = $db->prepare("insert into installations (wikilogin, diskquota, description, hostprefix, dbprefix) values (?,?,?,?,?)");
    $prep->bind_params("sisss", $wikilogin, 5, $clubname, $domainname, $dbprefix."_");
    $prep->execute();

    $prep = $db->prepare("insert into master_person  (firstname, lastname, email, address,postnmb, city,phone) values (?,?,?,?,?,?,?)");
    $prep->bind_params("sssssss", "Klubb:$clubname", $contact, $email, $address, $zipcode, $city, $phone);
    $prep->execute();

    $prep = $db->prepare("insert into ".$dbprefix."_person  (firstname, lastname, email, address,postnmb, city,phone) values (?,?,?,?,?,?,?)");
    $prep->bind_params("sssssss", "Superbruker", $contact, $email, $address, $zipcode, $city, $phone);
    $prep->execute();

    $user = new User(0);
    $crypted = crypt($password, $user->makesalt());
    $prep = $db->prepare("insert into ".$dbprefix."_user (username, pass, person, readonly, reducedwrite, project_required) values (?,?,1,0,0,0)");
    $prep->bind_params("ss", $superuser, $crypted);
    $prep->execute();

    $installer->sendEmailRequestDomain($wikilogin, $address, $email, $contact, $clubname, $domainname);

    $prep = $db->prepare("delete from to_install where secret = ? and wikilogin = ?");
    $prep->bind_params("ss", $secret, $wikilogin);
    $prep->execute();

    $db->commit();
} catch(Exception $e) {
    $db->rollback();

    $prep = $db->prepare("delete from to_install where secret = ? and wikilogin = ?");
    $prep->bind_params("ss", $secret, $wikilogin);
    $prep->execute();

    header("HTTP/1.0 515 DB error");
    die("Databasefeil:".$e);

}
//$installer->dropTables("master");
//$installer->createTables("master");
//$installer->createIndexes("master");
//$installer->addAccountPlan("master");

echo json_encode(array("result"=>1));


?>