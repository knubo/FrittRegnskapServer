<?php


/*
 * Created on May 19, 2007
 *
 */

class AccountHappening {
	private $db;
	public $Id;
	public $description;
	public $linedesc;
	public $debetpost;
	public $kredpost;
	public $count_req;

	function AccountHappening($db) {
		$this->db = $db;
	}

	function load($id) {
		$prep = $this->db->prepare("select debetpost,kredpost from " . AppConfig :: DB_PREFIX . "happeningv2 where id = ?");
		$prep->bind_params("i", $this->Id);
		return $prep->execute();
		
	}

	function getAll() {
		$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "happeningv2");
		return $prep->execute();
	}

	function setId($id) {
		$this->Id = $id;
	}
	function setDescription($description) {
		$this->description = $description;
	}
	function setLinedesc($linedesc) {
		$this->linedesc = $linedesc;
	}
	function setDebetpost($debetpost) {
		$this->debetpost = $debetpost;
	}
	function setKredpost($kredpost) {
		$this->kredpost = $kredpost;
	}
	function getDebetpost() {
		return $this->debetpost;
	}
	function getKredpost() {
		return $this->kredpost;
	}	
	function setCount_req($kontreq) {
		$this->count_req = $kontreq; 
	}

	function save() {
		if ($this->Id) {
			$prep = $this->db->prepare("update " . AppConfig :: DB_PREFIX . "happeningv2 set description = ?, linedesc=?, debetpost=?, kredpost=?,count_req=?  where id = ?");
			$prep->bind_params("ssiiii", $this->description, $this->linedesc, $this->debetpost, $this->kredpost, $this->count_req, $this->Id);
			$prep->execute();
			return $this->db->affected_rows();
			
		} else {
			$prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "happeningv2 (description, linedesc, debetpost, kredpost,count_req) values (?,?,?,?,?)");
			$prep->bind_params("ssiii", $this->description, $this->linedesc, $this->debetpost, $this->kredpost, $this->count_req);
			$prep->execute();
			$this->Id = $this->db->insert_id();
			return $this->Id;
		}
	}
}
?>
