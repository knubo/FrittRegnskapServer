<?php

/*
 * Created on May 24, 2007
 *
 */

class AccountCount {
	private $db;
	private $Id;
	# map of column -> value

	function AccountCount($db) {
		$this->db = $db;
	}

	# Postcols must be column->value
	function save($lineId, $postCols) {
		$values = array();
		$sql = "insert into " . AppConfig :: DB_PREFIX . "telling set regn_line=?";
		$params = "i";
		$values[] = $lineId;

		foreach (array_keys($postCols) as $one) {
			$sql.=", $one=?";
			$values[] = $postCols[$one];
			$params.="i";
		}
		$prep = $db->prepare($sql);
		$prep->bind_array_params($params, $values);
		$prep->execute();
		$this->Id = $db->insert_id();	
	}

	function load($lineid) {
		$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "telling where regn_line=?");
		$prep->bind_params("i", $lineid);
		$data = $prep->execute();
		
		foreach($data as $one) {
			return $one;
		}
	}

	function getId() {
		return $this->Id;
	}
}
?>
