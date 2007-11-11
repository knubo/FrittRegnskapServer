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

}
?>
