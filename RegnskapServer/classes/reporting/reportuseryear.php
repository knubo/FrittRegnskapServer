<?php
/*
 * Created on Jul 7, 2007
 */

class ReportUserYear {
    
    public $firstname;
    public $birthdate;
    public $lastname;
    public $id;
    
    function ReportUserYear($id, $firstname, $lastname, $birthdate) {
        $this->id = $id;
    	$this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->birthdate = $birthdate;
    }
    
    function getBirthdate() {
    	return $this->birthdate;
    }
}

?>
