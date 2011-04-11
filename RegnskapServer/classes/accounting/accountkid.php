<?php

class AccountKID {

    private $db;

    function AccountKID($db) {
        $this->db = $db;
    }

    function unhandled() {
        $prep = $this->db->prepare("select K.*,P.id, P.firstname,P.lastname,M.memberid from " . AppConfig::pre() . "kid K left join ".
        AppConfig::pre() . "person P on (id = SUBSTRING(kid, 5,5)) ".
        " left join ".AppConfig::pre() . "year_membership M ON ".
        "(memberid = SUBSTRING(kid, 5,5) and year = (select value from ".AppConfig::pre() . "standard where id = ?)) ".
        "where kid_status = 0 and ".
        "settlement_date >= str_to_date(CONCAT((SELECT value from regn_standard where id = 'STD_YEAR'),".
				                               "'-',".
					                          "(SELECT value from regn_standard where id = 'STD_MONTH'),".
					                           "'-1'), '%Y-%m-%d') ".
		"and settlement_date <".
			"date_add(str_to_date(CONCAT((SELECT value from regn_standard where id = 'STD_YEAR'),".
										"'-',".
										"(SELECT value from regn_standard where id = 'STD_MONTH'),".
										"'-1'), '%Y-%m-%d'), interval 1 MONTH)"   
        );

        $prep->bind_params("s", AccountStandard::CONST_YEAR);

        return $prep->execute();
    }
}


?>