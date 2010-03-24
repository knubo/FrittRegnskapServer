<?php


/*
 * Created on Jun 22, 2007
 */

class AccountTrustAction {
	private $db;
	public $fond;
	public $actionclub;
	public $actionfond;
	public $debetpost;
	public $creditpost;
    public $description;
    public $defaultdesc;
    public $id;
    
	function AccountTrustAction($db, $fond = 0, $description =0, $defaultdesc = 0, $actionclub = 0, $actionfond = 0, $debetpost=0, $creditpost = 0, $id = 0) {
		$this->db = $db;
        $this->fond = $fond;
        $this->description = $description;
        $this->defaultdesc = $defaultdesc;
        $this->actionclub = $actionclub;
        $this->actionfond = $actionfond;
        $this->debetpost = $debetpost;
        $this->creditpost = $creditpost;
        $this->id = $id;
	}

	function getAll() {
		$prep = $this->db->prepare("select * from " . AppConfig::pre() . "fond_action");

		return $prep->execute();
	}

    function save() {
        if($this->id) {
        	$prep = $this->db->prepare("update " . AppConfig::pre() . "fond_action set fond = ?, description = ?, defaultdesc = ?, actionclub = ?, actionfond = ?, debetpost = ?, creditpost = ? where id = ?");
            $prep->bind_params("sssiiiii", $this->fond, $this->description, $this->defaultdesc, $this->actionclub, $this->actionfond, $this->debetpost, $this->creditpost, $this->id);
            $prep->execute();
            return $db->affected_rows();        	
        } else {
        	$prep = $this->db->prepare("insert into " . AppConfig::pre() . "fond_action set fond = ?, description = ?, defaultdesc = ?, actionclub = ?, actionfond = ?, debetpost = ?, creditpost = ?");
            $prep->bind_params("sssiiii", $this->fond, $this->description, $this->defaultdesc, $this->actionclub, $this->actionfond, $this->debetpost, $this->creditpost);
            $this->id = $this->db->insert_id();
            $prep->execute();
            return 1;            
        }
    }

	function load($id) {
		$prep = $this->db->prepare("select * from " . AppConfig::pre() . "fond_action where id = ?");
		$prep->bind_params("i", $id);

		$res = $prep->execute();
		$one = array_pop($res);

		$this->id = $one["id"];
		$this->fond = $one["fond"];
		$this->actionclub = $one["actionclub"];
		$this->actionfond = $one["actionfond"];
		$this->debetpost = $one["debetpost"];
		$this->creditpost = $one["creditpost"];
	}

	function addAccountTrust($day, $month, $year, $desc, $attachment, $postnmb, $amount) {
        $accountLine = 0;
		if ($this->debetpost) {
			$accountLine = new AccountLine($this->db);
			$accountLine->setNewLatest($desc, $day, $year, $month);
			$accountLine->store();

			/** The debet post */
			$accountLine->addPostSingleAmount($accountLine->getId(), "1", $this->debetpost, $amount);

			/** The credit post */
			$accountLine->addPostSingleAmount($accountLine->getId(), "-1", $this->creditpost, $amount);
		}

		$newEntry = new AccountTrust($this->db, $this->fond, $desc, $amount * $this->actionfond, $amount * $this->actionclub, 0, 0, $accountLine != null ?  $accountLine->getId() : 0);
		$newEntry->setDate($day, $month, $year);
		$newEntry->store();
        
		return 1;
	}
}
?>
