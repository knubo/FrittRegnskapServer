<?php

class AccountStandard {

	private $db;

	function AccountStandard($db) {
		$this->db = $db;
	}

	function setValue($id, $value) {

		$prep = $this->db->prepare("select * from regn_standard where id=?");
		$prep->bind_params("s", $id);
		$query_array = $prep->execute();

		if (count($query_array) > 0) {
			$prep = $this->db->prepare("update regn_standard set value=? where id=?");
			$prep->bind_params("ss", $value, $id);
			$prep->execute();
		} else {
			$prep = $this->db->prepare("insert into regn_standard (id, value) values (?,?)");
			$prep->bind_params("ss", $id, $value);
			$prep->execute();
		}
	}

	function getValue($id) {
		$prep = $this->db->prepare("select value from regn_standard where id=?");
		$prep->bind_params("s", $id);

		$return_array = array ();

		$query_array = $prep->execute();
		if (count($query_array) >= 0) {
			for ($i = 0; $i < count($query_array); $i++) {
				$return_array[$i] = $query_array[$i]["value"];
			}
		}

		return $return_array;
	}

	function getOneValue($id) {
		$res = $this->getValue($id);

		if (count($res)) {
			return $res[0];
		}
	}
}
?>
