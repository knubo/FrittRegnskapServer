<?php
class ExportAccounts {
    private $db;
    function ExportAccounts($db) {
        $this->db = $db;
    }

    function getYearPosts($year) {
        $sql = "select RL.id, RL.occured, RL.description, RL.month, RP.debet, RP.post_type, RP.amount, RPT.description as accountdesc, ".
        		"PR.description as projectdesc, P.firstname as person_firstname, P.lastname as person_lastname,".
	  			"P2.firstname as edit_first_name, P2.lastname as edit_last_name". 
             	"from (" . AppConfig::pre() . "line RL, " . AppConfig::pre() . "post RP, " . AppConfig::pre() . "post_type RPT)". 
		    	"left join " . AppConfig::pre() . "project PR on (PR.project = RP.project)".  
		    	"left join " . AppConfig::pre() . "person P on (P.id = RP.person)".  
		    	"left join " . AppConfig::pre() . "person P2 on (P2.id = RL.edited_by_person)".  
		    	"where RL.year = ? and RP.line = RL.id and RPT.post_type = RP.post_type". 
		    	"order by month, RL.postnmb";
        
        $prep = $this->db->prepare($sql);
        $prep->bind_params("i", $year);
        return $prep->execute();
    }

}



?>