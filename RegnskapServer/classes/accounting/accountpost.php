<?php
class AccountPost {
	public $Id;
	public $Line;
	public $Debet;
	public $Post_type;
	public $Amount;
	public $Project;
	public $Person;
    public $EditedByPerson;
    public $EditedByPersonName;
    public $Earning;
    public $Cost;
	public $Belonging;
    
	private $db;

	function AccountPost($db, $line = 0, $debet = 0, $post_type = 0, $amount = 0, $id = 0, $project = 0, $person = 0, $edited_by_person = 0, $earning = 0,$cost = 0) {
		$this->db = $db;
		$this->Line = $line;
		$this->Debet = $debet;
		$this->Post_type = $post_type;
		$this->Amount = $amount;
		$this->Project = $project;
		$this->Person = $person;
        $this->EditedByPerson = $edited_by_person;
	    $this->Earning = $earning;
	    $this->Cost = $cost;
		$this->Id = $id;
	}

	function setBelonging($belonging) {
	    $this->Belonging = $belonging;
	}
	
	function getProject() {
		return $this->Project;
	}

	function getPerson() {
		return $this->Person;
	}
	
	function getEarning() {
        return $this->Earning;	    
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

		$prep = $this->db->prepare("insert into " . AppConfig::pre() . "post set id=null, line=?, debet=?,post_type=?, amount=?, person=?, project=?, edited_by_person=?, belonging_id=?");

		$prep->bind_params("isidiiii", $this->Line, $this->Debet, $this->Post_type, $this->Amount, $this->Person, $this->Project, $this->EditedByPerson, $this->Belonging);

		$prep->execute();
	    $this->Id = $this->db->insert_id();
	}

	function getRange($start, $stop) {
		$prep = $this->db->prepare("SELECT *, (select post_type >= 4000 and post_type <= 8500 and post_type <> 8040) as cost,".
		                            "((post_type >= 3000 and post_type < 4000) or post_type=8400 or post_type = 8040) as earning ".
		" FROM " . AppConfig::pre() . "post where line >= ? and line <= ?");
		$prep->bind_params("ii", $start, $stop);

		return $this->filled_result($prep->execute());
	}

	function getAll($parent) {
		$prep = $this->db->prepare("SELECT * FROM " . AppConfig::pre() . "post where line=?");
		$prep->bind_params("i", $parent);

		return $this->filled_result($prep->execute());
	}

	function filled_result($group_array) {
		$return_array = array ();

		foreach ($group_array as $one) {
		    $return_array[] = new AccountPost($this->db, $one["line"], $one["debet"], $one["post_type"], $one["amount"], $one["id"], $one["project"], $one["person"], $one["edited_by_person"], $one["earning"], $one["cost"]);
		}
		return $return_array;
	}

	function delete($lineId, $postId) {
		$prep = $this->db->prepare("delete from " . AppConfig::pre() . "post where line=? and id=?");
		$prep->bind_params("ii", $lineId, $postId);
		$prep->execute();
    	return $this->db->affected_rows();
	}
    
    function sumForPostType($postType) {
        $prep = $this->db->prepare("select (select sum(amount) from " . AppConfig::pre() . "post where post_type=? and debet='1') - ".
                                           "(select sum(amount) from " . AppConfig::pre() . "post where post_type=? and debet='-1') as D");
    	$prep->bind_params("ii", $postType, $postType);
        $res = $prep->execute();
        
        foreach($res as $one) {
        	return $one["D"];
        }
    }
	
	function sumForLine($lineId) {
		$prep = $this->db->prepare("select sum(amount) as D from " . AppConfig::pre() . "post where line=? and debet='1'");
		$prep->bind_params("d", $lineId);
		$debet = $prep->execute();

		$prep = $this->db->prepare("select sum(amount) as K from " . AppConfig::pre() . "post where line=? and debet='-1'");
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
