<?php
/*
 * Created on Nov 9, 2007
 *
 */

class AccountMemberPrice {

    private $db;

    function AccountMemberPrice($db) {
    	$this->db = $db;
    }

    function getCurrentPrices() {
    	$prep = $this->db->prepare("select C.amount as course, T.amount as train, Y.amount as year, U.amount as youth from " .
                                    AppConfig :: DB_PREFIX . "course_price C,  " .
                                    AppConfig :: DB_PREFIX . "train_price T, ".
                                    AppConfig :: DB_PREFIX . "youth_price U, ".
                                    AppConfig :: DB_PREFIX . "year_price Y, ".
                                    AppConfig :: DB_PREFIX . "standard SY,".
                                    AppConfig :: DB_PREFIX . "standard SS ".
                                    "where SS.id = 'STD_SEMESTER' and SY.id = 'STD_YEAR' and ".
                                    "C.semester = SS.value and U.semester = SS.value and T.semester = SS.value and SY.value = Y.year");
        $res = $prep->execute();

        return $res[0];
    }

    function getAll() {
        $res = array();
        $prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "course_price");
        $res["course"] = $prep->execute();

        $prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "train_price");
        $res["train"] = $prep->execute();

        $prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "youth_price");
        $res["youth"] = $prep->execute();


        $prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "year_price");
        $res["year"] = $prep->execute();

        return $res;
    }

    function updateYear($yearPrice, $year) {
    	$prep = $this->db->prepare("select year from " . AppConfig :: DB_PREFIX . "year_price where year = ?");
        $prep->bind_params("i", $year);
        $c = $prep->execute();

        if(count($c) != 0) {
            $prep = $this->db->prepare("update " . AppConfig :: DB_PREFIX . "year_price set amount=? where year = ?");
            $prep->bind_params("di", $yearPrice, $year);
            $prep->execute();
        } else {
            $prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "year_price set amount=?, year = ?");
            $prep->bind_params("di", $yearPrice, $year);
            $prep->execute();
        }
        return $this->db->affected_rows();

    }

    function courseUpdate($year, $price, $fall, $type) {
        $countPrep = $this->db->prepare("select semester from " . AppConfig :: DB_PREFIX . $type."_price where semester = (select semester from " . AppConfig :: DB_PREFIX . "semester where year = ? and fall=?)");
        $updatePrep = $this->db->prepare("update " . AppConfig :: DB_PREFIX . $type."_price set amount=? where semester = (select semester from " . AppConfig :: DB_PREFIX . "semester where year = ? and fall=?)");
        $insertPrep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . $type."_price set amount=?, semester = (select semester from " . AppConfig :: DB_PREFIX . "semester where year = ? and fall=?)");

        $countPrep->bind_params("ii", $year, $fall);
        $c = $countPrep->execute();

        if(count($c) != 0) {
            $updatePrep->bind_params("dii", $price, $year, $fall);
            $updatePrep->execute();
        } else {
            $insertPrep->bind_params("dii", $price, $year, $fall);
            $insertPrep->execute();
        }

        return $this->db->affected_rows();
    }

    function save($year, $yearPrice, $springCoursePrice, $springTrainPrice, $springYouthPrice, $fallCoursePrice, $fallTrainPrice, $fallYouthPrice) {
        $res = false;

        $res = $res | ($this->updateYear($yearPrice, $year) != 0);
        $res = $res | ($this->courseUpdate($year, $springCoursePrice, 0, "course") != 0);
        $res = $res | ($this->courseUpdate($year, $fallCoursePrice, 1, "course") != 0);
        $res = $res | ($this->courseUpdate($year, $springTrainPrice, 0, "train") != 0);
        $res = $res | ($this->courseUpdate($year, $fallTrainPrice, 1, "train") != 0);
        $res = $res | ($this->courseUpdate($year, $springYouthPrice, 0, "youth") != 0);
        $res = $res | ($this->courseUpdate($year, $fallYouthPrice, 1, "youth") != 0);

        return $res;
    }

}
?>
