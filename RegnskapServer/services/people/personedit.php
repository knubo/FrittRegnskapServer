<?php
/*
 * Created on Apr 27, 2007
 *
 */
 
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountperson.php");

$action = array_key_exists("action", $_REQUEST) ? $_REQUEST["action"] : "query";

 
?>
