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
    	$bind = $this->db->prepare("select username, person, concat_ws(' ',firstname, lastname) as name from ". AppConfig :: DB_PREFIX ."user, ".AppConfig :: DB_PREFIX."person where id=person");
        return $bind->execute();
    }
    
    function save($user, $password, $person) {
        if(!$password) {
            $bind = $this->db->prepare("update ". AppConfig :: DB_PREFIX ."user set person=? where username=?");
            $bind->bind_params("is", $person, $user);
            $bind->execute();
        
            return $this->db->affected_rows();
        }
        
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
