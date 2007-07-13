<?php
/*
 * Created on Jul 11, 2007
 */
 
$fromdate = array_key_exists("fromdate", $_REQUEST) ? $_GET["fromdate"] : 0;
$todate = array_key_exists("todate", $_REQUEST) ? $_GET["todate"] : 0;
$account = array_key_exists("account", $_REQUEST) ? $_GET["account"] : 0;
$project = array_key_exists("project", $_REQUEST) ? $_GET["project"] : 0;
$person = array_key_exists("person", $_REQUEST) ? $_GET["person"] : 0;

if(!$fromdate && !$todate && !$account && !$project &&!$person) {
	die("Supply search arguments.");
}
?>
