<?php

/*
 * Created on Apr 5, 2007
 *
 */

class PortalUser {
    const AUTH_OK = 1;
    const AUTH_FAILED = 0;
    const AUTH_BLOCKED = 2;
    
    private $db;
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

    function authenticate($username, $password, $prefix) {

        $toBind = $this->db->prepare("select pass,person,email,deactivated from ". $prefix ."portal_user U, ". $prefix ."person P where U.person = P.id and P.email like ?");

        $toBind->bind_params("s", "%".$username."%");

        $result = $toBind->execute($toBind);

        if (!$result && sizeof($result) != 1) {
            return PortalUser::AUTH_FAILED;
        }

        $email = $result[0]["email"];

        if($username != $email) {
            $emails = explode(",", $email);
            if(count($emails) > 0) {
                if(array_search($username, $emails) === FALSE) {
                    return PortalUser::AUTH_FAILED;
                }
            } else {
                return PortalUser::AUTH_FAILED;
            }
        }

        $crypted = $result[0]["pass"];

        if (crypt($password, $crypted) != $crypted) {
            return PortalUser::AUTH_FAILED;
        }

        if($result[0]["deactivated"]) {
            return PortalUser::AUTH_BLOCKED;
        }
        
        $this->personId = $result[0]["person"];
        return PortalUser::AUTH_OK;
    }
    function getPersonId() {
        return $this->personId;
    }


    function updatePassword($user, $password) {
        $pass = crypt($password, $this->makesalt());

        $bind = $this->db->prepare("update ". AppConfig::pre() ."portal_user set pass=? where username=?");
        $bind->bind_params("ss", $pass, $user);
        $bind->execute();

        return $this->db->affected_rows();
    }

    function getOne($user) {
        $prep = $this->db->prepare("select * from ". AppConfig::pre() ."portal_user where username = ?");
        $prep->bind_params("s", $user);
        $res = $prep->execute();
        return array_shift($res);
    }

    /*
     function save($user, $password, $person) {
     if(!$password) {
     $bind = $this->db->prepare("update ". AppConfig::pre() ."portal_user set person=?,readonly=?,reducedwrite=?,project_required=?, see_secret=? where username=?");
     $bind->bind_params("iiiiis", $person, $user);
     $bind->execute();

     return $this->db->affected_rows();
     }

     $bind = $this->db->prepare("insert into ". AppConfig::pre() ."user set pass=?, person=?, username=?,readonly=?,project_required=?,see_secret=? ON DUPLICATE KEY UPDATE pass=?,person=?,readonly=?,reducedwrite=?,project_required=?,see_secret=?");
     $pass = crypt($password, $this->makesalt());

     $bind->bind_params("sisiiisiiiii", $pass, $person, $user, $readonly, $project_required, $see_secret, $pass, $person, $readonly,$reducedwrite, $project_required,$see_secret);
     $bind->execute();

     return $this->db->affected_rows();
     }

     function delete($user) {
     $bind = $this->db->prepare("delete from ". AppConfig::pre() ."user where username=?");

     $bind->bind_params("s", $user);
     $bind->execute();

     return $this->db->affected_rows();
     }
     */
}
?>
