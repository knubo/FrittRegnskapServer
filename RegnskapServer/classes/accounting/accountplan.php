<?php
/*
 * Created on Jul 20, 2007
 *
 */

class AccountPlan {
	private $db;

    function AccountPlan($db) {
    	$this->db = $db;
    }

    function getCollectionPosts() {
    	$prep = $this->db->prepare("select * from " . AppConfig::pre() . "detail_post_type");
        return $prep->execute();
    }
}

?>
