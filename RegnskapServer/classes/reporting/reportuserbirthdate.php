<?php
/*
 * Created on Jul 7, 2007
 */

class ReportUserBirthdate {

    public $firstname;
    public $birthdate;
    public $lastname;
    public $id;
    public $age;
    public $gender;

    function ReportUserBirthdate($id, $firstname, $lastname, $birthdate, $gender) {
        $this->id = $id;
    	$this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->birthdate = $birthdate;
        $this->gender = $gender;
    }

    function getBirthdate() {
    	return $this->birthdate;
    }

    function setAge($age) {
    	$this->age = $age;
    }
}

?>
