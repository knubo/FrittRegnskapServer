<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/accounting/accountperson.php");


$secret = array_key_exists("secret", $_REQUEST) ? $_REQUEST["secret"] : "";

$secretAndPrefix = explode(":", substr($secret,1));

$matches = array();
preg_match("/([0-9]+)(.+):(.*)/",$secret, &$matches);

if(count($matches) != 4) {
    die("Klarte ikke tolke inputdata.");
}

$id = $matches[1];
$prefix = $matches[2];
$secretKey = $matches[3];

$db = new DB();
$accPerson = new AccountPerson($db);

$result = $accPerson->unsubscribeToNewsletter($prefix, $secretKey, $id);

if($result) {
    echo "Du er n&aring; avmeldt nyhetsbrevet.";
} else {
    echo "Ingen aksjon.";
}


?>