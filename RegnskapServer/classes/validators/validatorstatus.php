<?php
/*
 * Created on Aug 18, 2007
 *
 */
 
class ValidatorStatus {
 	public $invalidFields;
    
    function ValidatorStatus() {
    	$this->invalidFields = array();
    }
    
    function addInvalidField($field) {
    	$this->invalidFields[] = $field;
    }
    
    function dieIfNotValidated() {
    	if(count($this->invalidFields) == 0) {
    		return;
    	}
        
        header("HTTP/1.0 513 Validation Error");
        
        die(json_encode($this->invalidFields));           
    }
    
    
 }
?>
