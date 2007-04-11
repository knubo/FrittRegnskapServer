<?php

class AccountColumn {
	private $Name;
	private $Id;
	private $DisplayOrder;

	function AccountColumn($db, $name = 0, $id = 0) {
		$this->db = $db;
		$this->Name = $name;
		$this->Id = $id;
	}

	function getColPostIds() {
		$prep = $this->db->prepare("select id from regn_coll_post_type order by name");
		$dbQuery = $prep->execute();

		$res = array ();

		foreach ($dbQuery as $one) {
			$res[] = $one["id"];
		}

		return $res;
	}

	function getLookupMap() {
		$return_array = array ();

		$prep = $this->db->prepare("select id from regn_coll_post_type");
		$query_arr = $prep->execute();

		foreach ($query_arr as $one) {
			$return_array[$one["id"]] = $one["name"];
		}

		return $return_array;
	}

	function getAllColumns() {
		$return_array = array ();

		$prep = $this->db->prepare("select id,name from regn_coll_post_type order by display_order");
		$query_arr = $prep->execute();

		if (count($query_arr) >= 0) {
			for ($i = 0; $i < count($query_arr); $i++) {
				$return_array[$i] = new AccountColumn($this->db, $query_arr[$i]["name"], $query_arr[$i]["id"]);
			}
		}

		return $return_array;
	}

	function getId() {
		return $this->Id;
	}
	function getName() {
		return $this->Name;
	}

}