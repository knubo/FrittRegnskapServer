<?php

include_once ("../../conf/Version.php");

include_once ("../../conf/AppConfig.php");
include_once ("../../classes/util/DB.php");
include_once ("../../classes/util/ezdate.php");
include_once ("../../classes/accounting/accountsemester.php");
include_once ("../../classes/accounting/accountstandard.php");
include_once ("../../classes/auth/RegnSession.php");
include_once ("../../classes/auth/Master.php");

$db = new DB();
$regnSession = new RegnSession($db);
$user = $regnSession->auth();

$pre = AppConfig::pre();


$leftJoinPart = "";
// Semester
$fields = "S.description as semester_description, S.semester as semester_id, S.fall as is_fall";
$prePart = $pre."semester S, ".$pre."standard SS";
$wherePart = " S.semester = SS.value and SS.id = '".AccountStandard::CONST_SEMESTER."'";

// Next semester
$fields .= ", NS.semester as NextSemester";
$leftJoinPart .= " left join ".$pre."semester NS on (NS.year = if(S.fall = 1, S.year+1,S.year) and NS.fall = if(S.fall = 0, 1, 0) )"; 

// Active month
$fields .= ", SM.value as active_month";
$prePart .= ", ".$pre."standard SM";
$wherePart .= " and SM.id = '".AccountStandard::CONST_MONTH."'";

// Person name and last login
$fields .= ", P.firstname as firstname, P.lastname as lastname, U.lastlogin as lastlogin";
$prePart .= ", ".$pre."user U, ".$pre."person P ";
$wherePart .= " and U.username = ? and U.person = P.id";

//Max semester id
$fields .= ", max(X.semester) as max_semester_id";
$prePart .= ", ".$pre."semester X";

//Next semester course price
$fields .= ", cp.amount as next_semester_course_price";
$leftJoinPart .= " left join ".$pre."course_price cp ON (cp.semester = NS.semester)";

//Next semester train price
$fields .= ", ct.amount as next_semester_train_price";
$leftJoinPart .= " left join ".$pre."train_price ct ON (ct.semester = NS.semester)";

//Next semester youth price
$fields .= ", cy.amount as next_semester_youth_price";
$leftJoinPart .= " left join ".$pre."youth_price cy ON (cy.semester = NS.semester)";

// Current YEAR for accounting
$fields .= ", YEAR.value as year";
$prePart .= ", ".$pre."standard YEAR";
$wherePart .=" and YEAR.id = '".AccountStandard::CONST_YEAR."'";


//Next year prices
$fields .= ", ca.amount as next_year_price, ca.amountyouth as next_year_youth_price";
$leftJoinPart .= " left join ".$pre."year_price ca ON (ca.year = (YEAR.value + 1))";

//Current semester course price
$fields .= ", cpc.amount as current_semester_course_price";
$leftJoinPart .= " left join ".$pre."course_price cpc ON (cpc.semester = S.semester)";

//Current semester train price
$fields .= ", ctc.amount as current_semester_train_price";
$leftJoinPart .= " left join ".$pre."train_price ctc ON (ctc.semester = S.semester)";

//Current semester youth price
$fields .= ", cyc.amount as current_semester_youth_price";
$leftJoinPart .= " left join ".$pre."youth_price cyc ON (cyc.semester = S.semester)";

//Current year prices
$fields .= ", cac.amount as current_year_price, cac.amountyouth as next_year_youth_price";
$leftJoinPart .= " left join ".$pre."year_price cac ON (cac.year = YEAR.value)";

//Last registered post
$fields .= ", RL.occured as last_when, RL.description as last_desc";
$leftJoinPart .= "left join ".$pre."line RL ON (RL.id = (select max(id) from ".$pre."line))";

//Last backup time
$fields .= ", BB.value as backup_by";
$leftJoinPart .= "left join ".$pre."standard BB ON (BB.id = 'BACKUP_BY')";

//Last backup done by
$fields .= ", BU.value as backup_time";
$leftJoinPart .= "left join ".$pre."standard BU ON (BU.id = 'BACKUP_TIME')";

//First time setup
$fields .= ", FT.value as first_time_complete";
$leftJoinPart .= "left join ".$pre."standard FT ON (FT.id = '".AccountStandard::CONST_FIRST_TIME_SETUP."')";


//Last registered by
$fields .=", (select concat(firstname, ' ', lastname) from ".$pre."person LP where LP.id = RL.edited_by_person) as last_by";


$query .= "select $fields from ($prePart) $leftJoinPart where $wherePart group by SM.value";

$prep = $db->prepare($query);
$prep->bind_params("s", $user);
$info = $prep->execute();


// Status for accounts.

$prep = $db->prepare("select A.post, (select sum(D.amount) from ".$pre."post D where D.post_type = A.post and D.debet = '1') as d,".
		      " (select sum(C.amount) from ".$pre."post C where C.post_type = A.post and C.debet = '-1') as c".
						   " from ".$pre."accounttrack A");

$accountstatus = $prep->execute();

foreach($accountstatus as &$one) {
    $one["s"] = $one["d"] - $one["c"];
}


$arr = array();
$arr["serverversion"] = Version::SERVER_VERSION;
$arr["info"] = array_shift($info);
$arr["accountstatus"] = $accountstatus;

/* Spesial case - first time no semesters are registered... */
if(!$arr["info"]) {
    $arr["info"] = array("first_time_complete" => 0, "firstname" => "", "lastname" => "", "lastlogin" => "...");    
}


$now = new eZDate();

if($arr["info"]["last_when"]) {
    $lastAdd = new eZDate();
    $lastAdd->setMySQLDate($arr["info"]["last_when"]);

    $lastAdd->move(0, 2, 0);

    if($lastAdd->isGreater($now)) {
        $arr["info"]["long_since_last_warning"] = 1;
    }
    $lastAdd->move(0, 2, 0);

    if($lastAdd->isGreater($now)) {
        $arr["info"]["long_since_last_error"] = 1;
    }

    if($arr["info"]["backup_time"]) {
        $lastBackup = new eZDate();
        $lastBackup->setDate($arr["info"]["backup_time"]);
        $lastBackup->move(0, 2, 0);

        if($lastBackup->isGreater($now)) {
            $arr["info"]["long_since_backup_error"] = 1;
        }

    }

}

if($arr["info"]["active_month"] >= 6 && $arr["info"]["is_fall"] == 0) {
    $arr["info"]["mabye_change_semester"] = 1;
}

/* Updates fist time complete to reflect that wizard has been used */
if(!$arr["info"]["first_time_complete"] && $arr["info"]["last_desc"]) {
	$arr["info"]["first_time_complete"] = 1;
	$standard = new AccountStandard($db);
	$standard->setValue(AccountStandard::CONST_FIRST_TIME_SETUP, 1);
}

$arr["see_secret"] = $regnSession->canSeeSecret();

$arr["reduced_mode"] = $regnSession->getReducedMode();

echo json_encode($arr);


if(intval(substr($arr["info"]["lastlogin"], 8,2)) != $now->day()) {
    $prep = $db->prepare("update ".$pre."user set lastlogin=now() where username = ?");
    $prep->bind_params("s", $user);
    $prep->execute();
}



?>