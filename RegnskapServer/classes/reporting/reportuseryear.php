<?php
/*
 * Created on Jul 7, 2007
 */

class ReportUserYear {
    
    public $firstname;
    public $birthdate;
    public $lastname;
    public $id;
    public $age;
    
    function ReportUserYear($id, $firstname, $lastname, $birthdate) {
        $this->id = $id;
    	$this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->birthdate = $birthdate;
    }
    
    function getBirthdate() {
    	return $this->birthdate;
    }
    
    function setAge($age) {
    	$this->age = $age;
    }
}

?>
