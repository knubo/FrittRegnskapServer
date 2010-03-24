<?php

class AccountPostType {

	public $PostType;
	public $CollPost;
	public $Description;
	public $DetailPost;
	public $InUse;
	private $AllEntries;
	private $db;

	function AccountPostType($db, $a = 0, $b = 0, $c = 0, $d = 0, $f = 0) {
		$this->db = $db;
		$this->PostType = & $a;
		$this->CollPost = & $b;
		$this->Description = & $c;
		$this->DetailPost = & $d;
		$this->InUse = & $f;
	}

	function getInUse() {
		return $this->InUse;
	}

	function getPosttype() {
		return $this->PostType;
	}

	function getCollectionPost() {
		return $this->CollPost;
	}

	function getDescription() {
		return $this->Description;
	}

	function getDetailPost() {
		return $this->DetailPost;
	}

	function getSomeIndexedById($ids) {
		$p = $this->getSome($ids);

		$answer = array ();

		foreach ($p as $one) {
			$answer[$one->getPosttype()] = $one;
		}
		return $answer;
	}

	function save($posttype, $desc, $collpost, $detailpost) {
		$prep = $this->db->prepare("insert into " . AppConfig::pre() . "post_type (post_type, coll_post, detail_post, description, in_use) values (?, ?, ?, ?, 1) on duplicate key update coll_post=?, detail_post=?, description=?");

		$prep->bind_params("iiisiis", $posttype, $collpost, $detailpost, $desc,  $collpost, $detailpost, $desc);
		$prep->execute();
        return $this->db->affected_rows() > 0 ? 1 : 0;
	}

	function getSome($ids, $from = 0, $to = 0) {

		$return_array = array ();

		$where = 0;
		$prep = 0;

		if ($from && $to) {
			$prep = $this->db->prepare("SELECT * FROM " . AppConfig::pre() . "post_type WHERE post_type >= ? and post_type <= ? order by post_type");
			$prep->bind_params("ii", $from, $to);
		} else {
			$params = implode(",", array_fill(0, count($ids), "?"));
			$prep = $this->db->prepare("SELECT * FROM " . AppConfig::pre() . "post_type where post_type IN ($params)");

			$prep->bind_array_params(str_repeat("i", count($ids)), $ids);
		}

		$group_array = $prep->execute();

		if (count($group_array) > 0) {

			for ($i = 0; $i < count($group_array); $i++) {

				$pt = $group_array[$i]["post_type"];

				$one = new AccountPostType($this->db, $pt, $group_array[$i]["coll_post"], $group_array[$i]["description"], $group_array[$i]["detail_post"]);
				$return_array[$i] = $one;
			}
		}

		return $return_array;
	}

	function getAllFordringer() {
		return $this->getSome(AppConfig :: FordingPosts());
	}

	function getAll($disableFilter = 0) {

		$return_array = array ();

		$this->AllEntries = array ();

		$q = 0;

		if ($disableFilter) {
			$q = "SELECT * FROM " . AppConfig::pre() . "post_type order by in_use DESC, post_type, description";
		} else {
			$q = "SELECT * FROM " . AppConfig::pre() . "post_type where in_use = 1 order by description";
		}
		$prep = $this->db->prepare($q);
		$group_array = $prep->execute();

		if (count($group_array) >= 0) {

			for ($i = 0; $i < count($group_array); $i++) {

				$pt = $group_array[$i]["post_type"];

				$one = new AccountPostType($this->db, $pt, $group_array[$i]["coll_post"], $group_array[$i]["description"], $group_array[$i]["detail_post"], $group_array[$i]["in_use"]);
				$return_array[$i] = $one;
				$this->AllEntries[$pt] = $one;
			}
		}

		return $return_array;
	}

	/*! Call this only after you have fethced all posttypes */
	function getAccountPostType($id) {
		if(array_key_exists($id, $this->AllEntries)) {
			return $this->AllEntries[$id];
		}
		return 0;
	}



	function getYearEndTransferPost() {
		return AppConfig :: EndPostYearTransferPost;
	}

    function updateInUse($posttype, $inuse) {
		$prep = $this->db->prepare("update " . AppConfig::pre() . "post_type set in_use = ? where post_type = ?");
		$prep->bind_params("ii", $inuse, $posttype);
        $prep->execute();
        return $this->db->affected_rows();
    }

	function getEndTransferPost() {
		return AppConfig :: EndPostTransferPost;
	}

}
?>
