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
    private $diskQuota;
    private $can_see_secret;

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

        $toBind = $this->db->prepare("select pass,readonly,reducedwrite,project_required,person,see_secret from ". $prefix ."user where username = ?");

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
        $this->can_see_secret = $result[0]["see_secret"];

        if (crypt($password, $crypted) == $crypted) {
            return User::AUTH_OK;
        }
        return User::AUTH_FAILED;
    }

    function hasOnlyReadAccess() {
        return $this->read_only ? 1 : 0;
    }

    function hasReducedWrite() {
        return $this->reduced_write ? 1 : 0;
    }

    function canSeeSecret() {
        return $this->can_see_secret ? 1 : 0;
    }

    function hasProjectRequired() {
        return $this->project_required ? 1 : 0;
    }

    function getPersonId() {
        return $this->personId;
    }

    function mergeProfile($user, $toMergeStr) {
        $profile = $this->getProfile($user);
        $toMerge = json_decode($toMergeStr);

        if(is_array($profile)) {
            foreach($toMerge as $key => $value) {
                $profile[$key] = $value;
            }
            
        } else {
            foreach($toMerge as $key => $value) {
                $profile->$key = $value;
            }
        }

        $this->updateProfile($user, $profile);
    }

    function updateProfile($user, $profile) {
        $prep = $this->db->prepare("update ". AppConfig::pre() ."user set profile=? where username = ?");
        $prep->bind_params("ss", json_encode($profile), $user);
        $res = $prep->execute();

    }

    function getProfile($user) {
        $prep = $this->db->prepare("select profile from ". AppConfig::pre() ."user where username = ?");
        $prep->bind_params("s", $user);
        $res = $prep->execute();

        if(!$res[0]["profile"]) {
            return json_decode(json_encode(array()));
        }
        return json_decode($res[0]["profile"]);
    }


    function getAll() {
        $prep = $this->db->prepare("select username, person, concat_ws(' ',firstname, lastname) as name, readonly,reducedwrite,project_required, see_secret from ". AppConfig::pre() ."user, ".AppConfig::pre()."person where id=person");
        return $prep->execute();
    }

    function isOnlyOneUserWithSecretAccess() {
        $prep = $this->db->prepare("select count(*) as c from ". AppConfig::pre() ."user where see_secret = 1 and readonly <> 1");
        $res = $prep->execute();

        return $res[0]["c"] < 2;
    }

    function updatePassword($user, $password) {
        $pass = crypt($password, $this->makesalt());

        $bind = $this->db->prepare("update ". AppConfig::pre() ."user set pass=? where username=?");
        $bind->bind_params("ss", $pass, $user);
        $bind->execute();

        return $this->db->affected_rows();
    }

    function getOne($user) {
        $prep = $this->db->prepare("select * from ". AppConfig::pre() ."user where username = ?");
        $prep->bind_params("s", $user);
        $res = $prep->execute();
        return array_shift($res);
    }

    function save($user, $password, $person, $readonly, $reducedwrite, $project_required, $see_secret) {
        if(!$password) {
            $bind = $this->db->prepare("update ". AppConfig::pre() ."user set person=?,readonly=?,reducedwrite=?,project_required=?, see_secret=? where username=?");
            $bind->bind_params("iiiiis", $person, $readonly, $reducedwrite, $project_required, $see_secret, $user);
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
}
?>
