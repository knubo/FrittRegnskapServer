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
    
    function addPosts($posts) {
    	$prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "accounttrack set post=?");
        
        foreach($posts as $one) {
            $prep->bind_params("i", $one);
            $prep->execute();
            if($this->db->affected_rows() == 0) {
                return 0;
            }
        }
        return 1;
    }
    
    function removePosts($posts) {
        $prep = $this->db->prepare("delete from " . AppConfig :: DB_PREFIX . "accounttrack where post=?");
        
        foreach($posts as $one) {
            $prep->bind_params("i", $one);
            $prep->execute();
            if($this->db->affected_rows() == 0) {
                return 0;
            }
        }
        return 1;
    }
 }
?>
