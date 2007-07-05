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
    public $id;
    
	function AccountTrustAction($db) {
		$this->db = $db;
	}

	function getAll() {
		$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "fond_action");

		return $prep->execute();
	}

	function load($id) {
		$prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "fond_action where id = ?");
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
