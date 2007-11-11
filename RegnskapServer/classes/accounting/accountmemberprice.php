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

    function getAll() {
        $res = array();
        $prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "course_price");
        $res["course"] = $prep->execute();

        $prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "train_price");
        $res["train"] = $prep->execute();

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

    function save($year, $yearPrice, $springCoursePrice, $springTrainPrice, $fallCoursePrice, $fallTrainPrice) {
        $res = false;

        $res = $res | ($this->updateYear($yearPrice, $year) != 0);
        $res = $res | ($this->courseUpdate($year, $springCoursePrice, 0, "course") != 0);
        $res = $res | ($this->courseUpdate($year, $fallCoursePrice, 1, "course") != 0);
        $res = $res | ($this->courseUpdate($year, $springTrainPrice, 0, "train") != 0);
        $res = $res | ($this->courseUpdate($year, $fallTrainPrice, 1, "train") != 0);

        return $res;
    }

}
?>
