<?php

/*
 * Created on Apr 5, 2007
 *
 */

class User {
	const AUTH_OK = 1;
	const AUTH_FAILED = 0;

	private $db;

	function __construct($dbi) {
		$this->db = $dbi;
	}

	function authenticate($username, $password) {

		$toBind = $this->db->prepare("select pass from ". AppConfig :: DB_PREFIX ."user where username = ?");
		
		$toBind->bind_params("s", $username);
		
		$result = $toBind->execute($toBind);

		if (!$result && !sizeof($result)) {
			return User::AUTH_FAILED;
		}
		
		$crypted = $result[0]["pass"];
		
		if (crypt($password, $crypted) == $crypted) {
			return User::AUTH_OK;
		}
		return User::AUTH_FAILED;
	}
    
    function getAll() {
    	$bind = $this->db->prepare("select username, person from ". AppConfig :: DB_PREFIX ."user");
        return $bind->execute();
    }
    
    function save($user, $password, $person) {
    	$bind = $this->db->prepare("insert into ". AppConfig :: DB_PREFIX ."user set pass=?, person=?, username=? ON DUPLICATE KEY UPDATE pass=?,person=?");
        $pass = crypt($password);
        
        $bind->bind_params("sissi", $pass, $person, $user,$pass, $person);
        $bind->execute();
        
        return $this->db->affected_rows();
    }
    
    function delete($user) {
        $bind = $this->db->prepare("delete from ". AppConfig :: DB_PREFIX ."user where username=?");
        
        $bind->bind_params("s", $user);
        $bind->execute();
        
        return $this->db->affected_rows();    	
    }
}
?>
