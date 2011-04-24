<?php

class AccountKID {

    private $db;

    function AccountKID($db) {
        $this->db = $db;
    }

    function unhandled() {
        $prep = $this->db->prepare("select K.*,P.id as personId, P.firstname,P.lastname,M.memberid, C.memberid as course, T.memberid as train, Y.memberid as youth from "
        			 . AppConfig::pre() . "kid K ".
        "left join ".AppConfig::pre() . "person P on (P.id = SUBSTRING(kid, 5,5)) ".
        
        " left join ".AppConfig::pre() . "year_membership M ON ".
        "(M.memberid = SUBSTRING(kid, 5,5) and year = (select value from ".AppConfig::pre() . "standard S where S.id = 'STD_YEAR')) ".

        " left join ".AppConfig::pre() . "course_membership C ON ".
        "(C.memberid = SUBSTRING(kid, 5,5) and C.semester = (select value from ".AppConfig::pre() . "standard S where S.id = 'STD_SEMESTER')) ".

        " left join ".AppConfig::pre() . "train_membership T ON ".
        "(T.memberid = SUBSTRING(kid, 5,5) and T.semester = (select value from ".AppConfig::pre() . "standard S where S.id = 'STD_SEMESTER')) ".

        " left join ".AppConfig::pre() . "youth_membership Y ON ".
        "(Y.memberid = SUBSTRING(kid, 5,5) and Y.semester = (select value from ".AppConfig::pre() . "standard S where S.id = 'STD_SEMESTER')) ".

        "where kid_status = 0 and ".
        "settlement_date >= str_to_date(CONCAT((SELECT value from ".AppConfig::pre() . "standard S where S.id = 'STD_YEAR'),".
				                               "'-',".
					                          "(SELECT value from ".AppConfig::pre() . "standard S where S.id = 'STD_MONTH'),".
					                           "'-1'), '%Y-%m-%d') ".
		"and settlement_date <".
			"date_add(str_to_date(CONCAT((SELECT value from ".AppConfig::pre() . "standard S where S.id = 'STD_YEAR'),".
										"'-',".
										"(SELECT value from ".AppConfig::pre() . "standard S where S.id = 'STD_MONTH'),".
										"'-1'), '%Y-%m-%d'), interval 1 MONTH)"   
        );

        return $prep->execute();
    }
    
    function register($data) {
        $kids = json_decode($data);
        
        foreach ($kid as $kids) {
            
        }
        
    }
}


?>