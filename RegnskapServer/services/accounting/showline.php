<?php

/*
 * Created on Apr 12, 2007
 */

include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");

$db = new DB();

if(array_key_exists("line", $_GET)) { 
   $lineId = $_GET["line"];
} else {
	$lineId = 1;
}
$line = new AccountLine($db);
$line->read($lineId);
$line->fetchAllPosts();

echo json_encode($line);

?>
