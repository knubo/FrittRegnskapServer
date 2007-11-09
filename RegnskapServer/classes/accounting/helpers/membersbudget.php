<?php
/*
 * Created on Nov 8, 2007
 *
 */

class MembersBudget {
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
            $grouped["$year-$fall"] = $prev;
        }
    }

    function group($yearData, $courseData, $trainData) {
        $grouped = array();

        MembersBudget::loop(&$grouped, $yearData, "year");
        MembersBudget::loop(&$grouped, $courseData, "course");
        MembersBudget::loop(&$grouped, $trainData, "train");

        return $grouped;
    }

}
?>
