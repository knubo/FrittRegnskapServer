<?php
include_once ("../util/DB.php");
include_once ("../../conf/AppConfig.php");

class eZAccountPostType {

	private $PostType;
	private $CollPost;
	private $Description;
	private $DetailPost;
	private $InUse;
	private $AllEntries;

	function eZAccountPostType($db, $a = 0, $b = 0, $c = 0, $d = 0, $f = 0) {
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

	function store() {
		$prep = $this->db->prepare("insert into regn_post_type (post_type, coll_post, detail_post, description, in_use) values (?, ?, ?, ?, 1)");

		$prep->bind_params("iisi", $this->PostType, $this->CollPost, $this->Description, $this->DetailPost);
		$prep->execute();
	}

	function getSome($ids, $from = 0, $to = 0) {

		$return_array = array ();

		$where = 0;
		$prep = 0;

		if ($from && $to) {
			$prep = $this->db->prepare("SELECT * FROM regn_post_type WHERE post_type >= ? and post_type <= ? order by post_type");
			$prep->bind_params("ii", $from, $to);
		} else {
			$params = implode(",", array_fill(0, sizeof($ids), "?"));
			$prep = $this->db->prepare("SELECT * FROM regn_post_type where post_type IN ($params)");

			$prep->bind_array_params($prep, str_repeat("i", sizeof($ids)), $ids);
		}

		$group_array = $prep->execute();

		if (count($group_array) >= 0) {

			for ($i = 0; $i < count($group_array); $i++) {

				$pt = $group_array[$i]["post_type"];

				$one = new eZAccountPostType($this->db, $pt, $group_array[$i]["coll_post"], $group_array[$i]["description"], $group_array[$i]["detail_post"]);
				$return_array[$i] = $one;
			}
		}

		return $return_array;
	}

	function getAllFordringer() {
		return $this->getSome(AppConfig :: FordingPosts);
	}

	function getAll($disableFilter = 0) {

		$return_array = array ();

		$this->AllEntries = array ();

		$q = 0;

		if ($disableFilter) {
			$q = "SELECT * FROM regn_post_type order by in_use DESC, post_type, description";
		} else {
			$q = "SELECT * FROM regn_post_type where in_use = 1 order by description";
		}
		$prep = $this->db->prepare($q);
		$group_array = $prep->execute();

		if (count($group_array) >= 0) {

			for ($i = 0; $i < count($group_array); $i++) {

				$pt = $group_array[$i]["post_type"];

				$one = new eZAccountPostType($this->db, $pt, $group_array[$i]["coll_post"], $group_array[$i]["description"], $group_array[$i]["detail_post"], $group_array[$i]["in_use"]);
				$return_array[$i] = $one;
				$this->AllEntries[$pt] = $one;
			}
		}

		return $return_array;
	}

	/*! Call this only after you have fethced all posttypes */
	function getAccountPostType($id) {
		return $this->AllEntries[$id];
	}

	function getYearEndTransferPost() {
		return AppConfig :: EndPostYearTransferPost;
	}

	function aktiver($posts) {

		$params = implode(",", array_fill(0, sizeof($posts), "?"));
		$prep = $this->db->prepare("update regn_post_type set in_use = 1 where post_type IN($params)");
		$prep->bind_array_params($prep, str_repeat("i", sizeof($posts)), $posts);
		$prep->execute();

	}

	function slett($posts) {
		$params = implode(",", array_fill(0, sizeof($posts), "?"));
		$prep = $this->db->prepare("update regn_post_type set in_use = 0 where post_type IN($params)");
		$prep->bind_array_params($prep, str_repeat("i", sizeof($posts)), $posts);
		$prep->execute();

	}

	function getEndTransferPost() {
		return AppConfig :: EndPostTransferPost;
	}

	function getEndPosts() {
		return AppConfig :: EndPosts;
	}
}
?>
