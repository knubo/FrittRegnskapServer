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
        $prep = $this->db->prepare("select C.amount as course, T.amount as train, Y.amount as year, U.amount as youth, Y.amountyouth as yearyouth from " .
        AppConfig::pre() . "course_price C,  " .
        AppConfig::pre() . "train_price T, ".
        AppConfig::pre() . "youth_price U, ".
        AppConfig::pre() . "year_price Y, ".
        AppConfig::pre() . "standard SY,".
        AppConfig::pre() . "standard SS ".
                                    "where SS.id = '".AccountStandard::CONST_SEMESTER."' and SY.id = '".AccountStandard::CONST_YEAR."' and ".
                                    "C.semester = SS.value and U.semester = SS.value and T.semester = SS.value and SY.value = Y.year");
        $res = $prep->execute();

        return $res[0];
    }

    function getAll() {
        $res = array();
        $prep = $this->db->prepare("select * from " . AppConfig::pre() . "course_price");
        $res["course"] = $prep->execute();

        $prep = $this->db->prepare("select * from " . AppConfig::pre() . "train_price");
        $res["train"] = $prep->execute();

        $prep = $this->db->prepare("select * from " . AppConfig::pre() . "youth_price");
        $res["youth"] = $prep->execute();

        $prep = $this->db->prepare("select * from " . AppConfig::pre() . "year_price");
        $res["year"] = $prep->execute();

        return $res;
    }

    function updateYear($yearPrice, $yearYouthPrice, $year) {
        $prep = $this->db->prepare("select year from " . AppConfig::pre() . "year_price where year = ?");
        $prep->bind_params("i", $year);
        $c = $prep->execute();

        if(count($c) != 0) {
            $prep = $this->db->prepare("update " . AppConfig::pre() . "year_price set amount=?,amountyouth=? where year = ?");
            $prep->bind_params("ddi", $yearPrice, $yearYouthPrice, $year);
            $prep->execute();
        } else {
            $prep = $this->db->prepare("insert into " . AppConfig::pre() . "year_price set amount=?, amountyouth=?, year = ?");
            $prep->bind_params("ddi", $yearPrice, $yearYouthPrice, $year);
            $prep->execute();
        }
        return $this->db->affected_rows();
    }


    function courseUpdate($year, $price, $fall, $type) {
        $countPrep = $this->db->prepare("select semester from " . AppConfig::pre() . $type."_price where semester = (select semester from " . AppConfig::pre() . "semester where year = ? and fall=?)");
        $updatePrep = $this->db->prepare("update " . AppConfig::pre() . $type."_price set amount=? where semester = (select semester from " . AppConfig::pre() . "semester where year = ? and fall=?)");
        $insertPrep = $this->db->prepare("insert into " . AppConfig::pre() . $type."_price set amount=?, semester = (select semester from " . AppConfig::pre() . "semester where year = ? and fall=?)");

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

    function save($year, $yearPrice, $springCoursePrice, $springTrainPrice, $springYouthPrice, $fallCoursePrice, $fallTrainPrice, $fallYouthPrice, $yearYouthPrice) {
        $res = false;

        if ($this->updateYear($yearPrice, $yearYouthPrice, $year) != 0) {
            $res = true;
        }
        if ($this->courseUpdate($year, $springCoursePrice, 0, "course") != 0) {
            $res = true;
        }
        if ($this->courseUpdate($year, $fallCoursePrice, 1, "course") != 0) {
            $res = true;
        }
        if ($this->courseUpdate($year, $springTrainPrice, 0, "train") != 0) {
            $res = true;
        }
        if ($this->courseUpdate($year, $fallTrainPrice, 1, "train") != 0) {
            $res = true;
        }
        if ($this->courseUpdate($year, $springYouthPrice, 0, "youth") != 0) {
            $res = true;
        }
        if ($this->courseUpdate($year, $fallYouthPrice, 1, "youth") != 0) {
            $res = true;
        }

        return $res;
    }

}
?>
