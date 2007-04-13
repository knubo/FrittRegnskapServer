<?php

/*
 * Created on Apr 13, 2007
 * 
 * query/insert/update service for accountline.
 */

include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/accounting/accountline.php");
include_once ("../../classes/accounting/accountpost.php");

if (array_key_exists("action", $_GET)) {
	$action = $_GET["action"];
} else {
	$action = "query";
}

if (array_key_exists("line", $_GET)) {
	$line = $_GET["line"];
} else {
	$line = 1;
}

$db = new DB();
$accLine = new AccountLine($db);

switch ($action) {
	case "query" :
		$accLine->read($line);
		$accLine->fetchAllPosts();
		echo json_encode($accLine);
		break;
}
		
?>

