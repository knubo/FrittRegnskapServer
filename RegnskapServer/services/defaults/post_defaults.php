<?php

/*
 * Created on May 9, 2007
 *
 */
include_once ("../../conf/AppConfig.php");

$selection = array_key_exists("selection", $_REQUEST) ? $_REQUEST["selection"] : "membershippayment";

switch ($selection) {
	case "membershippayment" :
	echo json_encode(AppConfig::RegisterMembershipPosts());
		break;
}
?>
