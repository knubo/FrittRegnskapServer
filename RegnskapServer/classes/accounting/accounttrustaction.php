<?php

/*
 * Created on Jun 22, 2007
 */

class AccountTrustAction {
	private $db;

	function AccountTrustAction($db) {
		$this->db = $db;
	}

	function getAll() {
		$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "fond_action");
        
        return $prep->execute();
	}

}
?>
