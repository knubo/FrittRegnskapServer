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
    private $project_required;
    private $personId;
    
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

	    $master = new Master($db);
	    $prefix = $master->calculate_prefix();
	    
		$toBind = $this->db->prepare("select pass,readonly,reducedwrite,project_required,person from ". $prefix ."user where username = ?");
		
		$toBind->bind_params("s", $username);
		
		$result = $toBind->execute($toBind);

		if (!$result && !sizeof($result)) {
			return User::AUTH_FAILED;
		}
		
		$crypted = $result[0]["pass"];
		
        $this->read_only = $result[0]["readonly"];
        $this->reduced_write = $result[0]["reducedwrite"];
        $this->project_required = $result[0]["project_required"];
        $this->personId = $result[0]["person"];
        
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

    function hasProjectRequired() {
        return $this->project_required;
    }
    
    function getPersonId() {
        return $this->personId;
    }
    
    
    function getAll() {
    	$bind = $this->db->prepare("select username, person, concat_ws(' ',firstname, lastname) as name, readonly,reducedwrite,project_required from ". AppConfig::pre() ."user, ".AppConfig::pre()."person where id=person");
        return $bind->execute();
    }
    
    function updatePassword($user, $password) {
        $pass = crypt($password, $this->makesalt());

        $bind = $this->db->prepare("update ". AppConfig::pre() ."user set pass=? where username=?");
        $bind->bind_params("ss", $pass, $user);
        $bind->execute();
        
        return $this->db->affected_rows();    	
    }
    
    function save($user, $password, $person, $readonly, $reducedwrite, $project_required) {
        if(!$password) {
            $bind = $this->db->prepare("update ". AppConfig::pre() ."user set person=?,readonly=?,reducedwrite=?,project_required=? where username=?");
            $bind->bind_params("iiiis", $person, $readonly, $reducedwrite, $project_required, $user);
            $bind->execute();
        
            return $this->db->affected_rows();
        }
        
    	$bind = $this->db->prepare("insert into ". AppConfig::pre() ."user set pass=?, person=?, username=?,readonly=?,project_required=? ON DUPLICATE KEY UPDATE pass=?,person=?,readonly=?,reducedwrite=?,project_required=?");
        $pass = crypt($password, $this->makesalt());
        
        $bind->bind_params("sisiisiiii", $pass, $person, $user, $readonly, $project_required, $pass, $person, $readonly,$reducedwrite, $project_required);
        $bind->execute();
        
        return $this->db->affected_rows();
    }
    
    function delete($user) {
        $bind = $this->db->prepare("delete from ". AppConfig::pre() ."user where username=?");
        
        $bind->bind_params("s", $user);
        $bind->execute();
        
        return $this->db->affected_rows();    	
    }
}
?>
