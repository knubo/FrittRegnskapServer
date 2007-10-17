<?php
/*
 * Created on Oct 15, 2007
 */
 
 class AccountTrackAccount {
    private $db;
    
    function AccountTrackAccount($db) {
    	$this->db = $db;
    }
 	function getAll() {
 		$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "accounttrack");
        
        return $prep->execute();
 	}
 }
?>
