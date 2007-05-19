<?php
/*
 * Created on May 19, 2007
 *
 */

class AccountHappening {
	private $db;

    function AccountHappening($db) {
    	$this->db = $db;
    }
    
    function getAll() {
    	$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "happening");
    	return $prep->execute();
    }	
}
?>
