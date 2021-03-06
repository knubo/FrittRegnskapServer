<?php 
include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");
include_once ("../../classes/auth/User.php");

$db = new DB();
$regnSession = new RegnSession($db);
$currentUser = $regnSession->auth();

?>


<table>
<tr>
<th colspan="2">Sesjonsvariabler/Session variables</th>
</tr>

<?php 

if($_SESSION) {
foreach(array_keys($_SESSION) as $one) {
    echo "<tr><td>";
    echo $one;
    echo "</td><td>";
    echo $_SESSION[$one];
    echo "</td></tr>";    
}
} else {
  echo "<tr><td colspan='2'>INGEN SESJON/NO SESSION</td></tr>";
}
?>
</table>

R�profildata/Raw profile data 
<pre>
<?php
	$accUser = new User($db);
	
	$profile = $accUser->getProfile($currentUser);
	
	echo json_encode($profile);
?>	
</pre>