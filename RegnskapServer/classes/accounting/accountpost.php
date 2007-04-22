<?php
class AccountPost {
	public $Id;
	public $Line;
	public $Debet;
	public $Post_type;
	public $Amount;
	public $Project;
	public $Person;
	private $db;

	function AccountPost($db, $line = 0, $debet = 0, $post_type = 0, $amount = 0, $id = 0, $project = 0, $person = 0) {
		$this->db = $db;
		$this->Line = $line;
		$this->Debet = $debet;
		$this->Post_type = $post_type;
		$this->Amount = $amount;
		$this->Project = $project;
		$this->Person = $person;

		$this->Id = $id;
	}

	function getProject() {
		return $this->Project;
	}

	function getPerson() {
		return $this->Person;
	}

	function getId() {
		return $this->Id;
	}
	function getLine() {
		return $this->Line;
	}
	function getDebet() {
		return $this->Debet;
	}
	function getPost_type() {
		return $this->Post_type;
	}
	function getAmount() {
		return $this->Amount;
	}

	function store() {

		$prep = $this->db->prepare("insert into regn_post set id=null, line=?, debet=?,post_type=?, amount=?, person=?, project=?");

		$prep->bind_params("isidii", $this->Line, $this->Debet, $this->Post_type, $this->Amount, $this->Person, $this->Project);

		$prep->execute();
	    $this->Id = $this->db->insert_id();
	}

	function getRange($start, $stop) {
		$prep = $this->db->prepare("SELECT * FROM regn_post where line >= ? and line <= ?");
		$prep->bind_params("ii", $start, $stop);

		return $this->filled_result($prep->execute());
	}

	function getAll($parent) {
		$prep = $this->db->prepare("SELECT * FROM regn_post where line=?");
		$prep->bind_params("i", $parent);

		return $this->filled_result($prep->execute());
	}

	function filled_result($group_array) {
		$return_array = array ();

		foreach ($group_array as $one) {
			$return_array[] = new AccountPost($this->db, $one["line"], $one["debet"], $one["post_type"], $one["amount"], $one["id"], $one["project"], $one["person"]);
		}
		return $return_array;
	}

	function delete($lineId, $postId) {
		$prep = $this->db->prepare("delete from regn_post where line=? and id=?");
		$prep->bind_params("ii", $lineId, $postId);
		$prep->execute();
    	return $this->db->affected_rows();
	}
	
	function sumForLine($lineId) {
		$prep = $this->db->prepare("select sum(amount) as D from regn_post where line=? and debet='1'");
		$prep->bind_params("d", $lineId);
		$debet = $prep->execute();

		$prep = $this->db->prepare("select sum(amount) as K from regn_post where line=? and debet='-1'");
		$prep->bind_params("d", $lineId);
		$prep->execute();
		$kredit=$prep->execute();
		
		$result = 0;
		
		if(sizeof($debet)) {
			foreach($debet as $one) {
			    $result += $one["D"];
			}
		}
		
		if(sizeof($kredit)) {
			foreach($kredit as $one) {
			    $result -= $one["K"];
			}
		}
		return $result;
	}
}
?>
