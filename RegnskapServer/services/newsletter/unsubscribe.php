<?php

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/strings.php");
include_once ("../../classes/accounting/accountperson.php");


$secret = array_key_exists("secret", $_REQUEST) ? $_REQUEST["secret"] : "";

$secretAndPrefix = explode(":", substr($secret,1));


$id = substr($secret,0,1);

$db = new DB();
$accPerson = new AccountPerson($db);

$result = $accPerson->unsubscribeToNewsletter($secretAndPrefix[0], $secretAndPrefix[1], $id);

if($result) {
    echo "Du er n&aring; avmeldt nyhetsbrevet.";
} else {
    echo "Ingen aksjon.";
}


?>