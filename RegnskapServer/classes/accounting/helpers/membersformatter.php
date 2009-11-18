<?php
/*
 * Created on Nov 8, 2007
 *
 */

class MembersFormatter {
    function loop($grouped, $info, $type) {

        foreach($info as $one) {
            $year = $one["year"];

            $fall = array_key_exists("fall", $one) ? $one["fall"] : 0;

            $prev = 0;
            if(array_key_exists("$year-$fall", $grouped)) {
                $prev = $grouped["$year-$fall"];
            } else {
                $prev = array();
            }
            $prev[$type] = $one["C"];
            $prev["semester"] = $one["semester"];
            $grouped["$year-$fall"] = $prev;
        }
    }

    function group($yearData, $courseData, $trainData, $youthData, $budgetData, $semesterInfo) {
        $grouped = array();

        MembersFormatter::loop(&$grouped, $yearData, "year");
        MembersFormatter::loop(&$grouped, $courseData, "course");
        MembersFormatter::loop(&$grouped, $trainData, "train");
        MembersFormatter::loop(&$grouped, $youthData, "youth");

        $springSemester = array_shift($semesterInfo);
        $fallSemester = array_shift($semesterInfo);

        $grouped[$budgetData["year"]."-0"] = array("budget"=>1, "semester"=> $springSemester["semester"], "year"=>$budgetData["year_members"], "course"=>$budgetData["spring_course"],"train"=>$budgetData["spring_train"], "keyYear"=>$budgetData["year"], "keyFall" => 0);
        $grouped[$budgetData["year"]."-1"] = array("budget"=>1, "semester"=> $fallSemester["semester"], "year"=>0, "course"=>$budgetData["fall_course"],"train"=>$budgetData["fall_train"], "keyYear"=>$budgetData["year"], "keyFall"=> 1);

        return $grouped;
    }

    function addForUser($grouped, $info, $type) {
        if(array_key_exists($info[2], $grouped)) {
        	$userobj = $grouped[$info[2]];
        } else {
        	$userobj = array();
            $userobj["first"] = $info[0];
            $userobj["last"] = $info[1];
            $userobj["id"] = $info[2];
        }
        $userobj[$type] = 1;

        $grouped[$info[2]] = $userobj;
    }

    function allInOne($yearData, $courseData, $trainData, $youthData) {
        function cmp($one, $two) {
            $res = strcmp($one["last"], $two["last"]);

            if($res) {
            	return $res;
            }

            return strcmp($one["first"], $two["first"]);
        }
        $grouped = array();

        foreach($yearData as $one) {
            MembersFormatter::addForUser(&$grouped, $one, "year");
        }

        foreach($courseData as $one) {
        	Membersformatter::addForuser(&$grouped, $one, "course");
        }

        foreach($trainData as $one) {
        	Membersformatter::addForuser(&$grouped, $one, "train");
        }

        foreach($youthData as $one) {
            Membersformatter::addForuser(&$grouped, $one, "youth");
        }

        $arr = array_values($grouped);
        usort($arr, "cmp");

        return $arr;
    }
}
?>
