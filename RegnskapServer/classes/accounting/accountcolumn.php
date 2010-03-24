<?php
class AccountColumn {

	# Please don't access directly - required to do easy json encoding.
	public $Id;
	public $Name;
	private $db;
	
	function AccountColumn($db, $name = 0, $id = 0) {
		$this->db = $db;
		$this->Name = $name;
		$this->Id = $id;
	}

	function getColPostIds() {
		$prep = $this->db->prepare("select id from " . AppConfig::pre() . "coll_post_type order by name");
		$dbQuery = $prep->execute();

		$res = array ();

		foreach ($dbQuery as $one) {
			$res[] = $one["id"];
		}

		return $res;
	}

	function getLookupMap() {
		$return_array = array ();

		$prep = $this->db->prepare("select id from " . AppConfig::pre() . "coll_post_type");
		$query_arr = $prep->execute();

		foreach ($query_arr as $one) {
			$return_array[$one["id"]] = $one["name"];
		}

		return $return_array;
	}

	function getAllColumns() {
		$return_array = array ();

		$prep = $this->db->prepare("select id,name from " . AppConfig::pre() . "coll_post_type order by display_order");
		$query_arr = $prep->execute();

		if (count($query_arr) >= 0) {
			foreach ($query_arr as $one) {
				$return_array[]= new AccountColumn(0, $one["name"], $one["id"]);
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