<?php

/*
 * Created on Apr 5, 2007
 *
 */

class User {
	const AUTH_OK = 1;
	const AUTH_FAILED = 0;

	private $db;
    private $read_only;
    private $reduced_write;

	function __construct($dbi) {
		$this->db = $dbi;
	}

    function makesalt($type=CRYPT_SALT_LENGTH) {
        switch($type) {
        case 8:
            $saltlen=9; 
            $saltprefix='$1$'; 
            $saltsuffix='$'; 
            break;
        case 2:
        default: // by default, fall back on Standard DES (should work everywhere)
            $saltlen=2; 
            $saltprefix=''; 
            $saltsuffix=''; break;
        }
         $salt='';
        while(strlen($salt)<$saltlen) $salt.=chr(rand(64,126));
        return $saltprefix.$salt.$saltsuffix;
}
 
	function authenticate($username, $password) {

		$toBind = $this->db->prepare("select pass,readonly,reducedwrite from ". AppConfig :: DB_PREFIX ."user where username = ?");
		
		$toBind->bind_params("s", $username);
		
		$result = $toBind->execute($toBind);

		if (!$result && !sizeof($result)) {
			return User::AUTH_FAILED;
		}
		
		$crypted = $result[0]["pass"];
		
        $this->read_only = $result[0]["readonly"];
        $this->reduced_write = $result[0]["reducedwrite"];
        
		if (crypt($password, $crypted) == $crypted) {
			return User::AUTH_OK;
		}
		return User::AUTH_FAILED;
	}
    
    function hasOnlyReadAccess() {
    	return $this->read_only;
    }
    
    function hasReducedWrite() {
    	return $this->reduced_write;
    }
    
    function getAll() {
    	$bind = $this->db->prepare("select username, person, concat_ws(' ',firstname, lastname) as name, readonly,reducedwrite from ". AppConfig :: DB_PREFIX ."user, ".AppConfig :: DB_PREFIX."person where id=person");
        return $bind->execute();
    }
    
    function updatePassword($user, $password) {
        $pass = crypt($password, $this->makesalt());

        $bind = $this->db->prepare("update ". AppConfig :: DB_PREFIX ."user set pass=? where username=?");
        $bind->bind_params("ss", $pass, $user);
        $bind->execute();
        
        return $this->db->affected_rows();    	
    }
    
    function save($user, $password, $person, $readonly, $reducedwrite) {
        if(!$password) {
            $bind = $this->db->prepare("update ". AppConfig :: DB_PREFIX ."user set person=?,readonly=?,reducedwrite=? where username=?");
            $bind->bind_params("iiis", $person, $readonly, $reducedwrite, $user);
            $bind->execute();
        
            return $this->db->affected_rows();
        }
        
    	$bind = $this->db->prepare("insert into ". AppConfig :: DB_PREFIX ."user set pass=?, person=?, username=?,readonly=? ON DUPLICATE KEY UPDATE pass=?,person=?,readonly=?,reducedwrite=?");
        $pass = crypt($password, $this->makesalt());
        
        $bind->bind_params("sisisiii", $pass, $person, $user, $readonly, $pass, $person, $readonly,$reducedwrite);
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
