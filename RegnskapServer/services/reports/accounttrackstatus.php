<?php
/*
 * Created on Oct 15, 2007
 *
 */
 
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accounttrackaccount.php");
include_once ("../../classes/accounting/accountpost.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$db = new DB();
$regnSession = new RegnSession($db);
$regnSession->auth();

$accPost = new AccountPost($db);
$accTrackStatus = new AccountTrackAccount($db);

$posts = $accTrackStatus->getAll();

$result = array();
foreach($posts as $one) {
	$postId = $one["post"];
    $sum = $accPost->sumForPostType($postId);
    
    $result[$postId] = $sum;
}

echo json_encode($result);
?>
