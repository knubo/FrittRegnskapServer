<?php
class AccountProject {
	public $Project;
	public $Description;
	private $db;

	function AccountProject($db, $project = 0, $description = 0) {
		$this->db = $db;
		$this->Project = $project;
		$this->Description = $description;
	}

	function setProject($p) {
		$this->Project = $p;
	}
	
	function setDescription($d) {
		$this->Description = $d;
	}
	
	function description() {
		return $this->Description;
	}

	function project() {
		return $this->Project;
	}

	function getAll() {
		$prep = $this->db->prepare("select project, description from " . AppConfig :: DB_PREFIX . "project order by description");

		return $prep->execute();
	}

	function delete($id) {
		$prep = $this->db->prepare("delete from " . AppConfig :: DB_PREFIX . "project where project = ?");
		$prep->bind_params("i", $id);
		$prep->execute();
	}

	function save() {
		if (!$this->Project) {
			$prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "project (description) values (?)");
			$prep->bind_params("s", $this->Description);
			$prep->execute();
			$this->Project = $this->db->insert_id();
		} else {
			$prep = $this->db->prepare("update " . AppConfig :: DB_PREFIX . "project set description = ? where project = ?");
			$prep->bind_params("si", $this->Description, $this->Project);
			$prep->execute();
		}
	}
}