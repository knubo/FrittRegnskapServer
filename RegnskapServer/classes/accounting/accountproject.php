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

	function description() {
		return $this->Description;
	}

	function project() {
		return $this->Project;
	}

	function getAll() {
		$prep = $this->db->prepare("select project, description from regn_project order by description");

		return $prep->execute();
	}

	function delete($id) {
		$prep = $this->db->prepare("delete from regn_project where project = ?");
		$prep->bind_params("i", $id);
		$prep->execute();
	}

	function save() {
		if ($this->project) {
			$prep = $this->db->prepare("insert from regn_project (description) values (?)");
			$prep->bind_params("s", $this->Description);
			$prep->execute();
		} else {
			$prep = $this->db->prepare("update regn_project set description = ? where project = ?");
			$prep->bind_params("si", $this->Description, $this->Project);
			$prep->execute();
		}
	}
}