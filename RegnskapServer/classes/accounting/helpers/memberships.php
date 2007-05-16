<?php


/*
 * Created on May 15, 2007
 *
 */

class Memberships {

	private $Year;
	private $Course;
	private $Train;
	private $Day;
	private $Memberid;
	private $Post;

	function starts_with($string, $match) {
		if (strlen($string) < strlen($match)) {
			return false;
		}

		return substr($string, 0, strlen($match)) == $match;
	}

	function find(&$perMemberid, $id) {
		if (!array_key_exists($id, $perMemberid)) {
			$perMemberid[$id] = new Memberships();
			$perMemberid[$id]->Memberid = $id;
		}
		return $perMemberid[$id];
	}

	function parseParams($requestparams) {

		$perMemberId = array ();

		foreach (array_keys($requestparams) as $one) {
			if (Memberships :: starts_with($one, "year")) {
				Memberships :: find($perMemberId, substr($one, 4))->Year = true;
			}

			if (Memberships :: starts_with($one, "course")) {
				Memberships :: find($perMemberId, substr($one, 6))->Course = true;
			}

			if (Memberships :: starts_with($one, "train")) {
				Memberships :: find($perMemberId, substr($one, 5))->Train = true;
			}

			if (Memberships :: starts_with($one, "day")) {
				Memberships :: find($perMemberId, substr($one, 3))->Day = $requestparams[$one];
			}

			if (Memberships :: starts_with($one, "post")) {
				Memberships :: find($perMemberId, substr($one, 4))->Post = $requestparams[$one];
			}
		}
		return array_values($perMemberId);
	}


	function year() {
		return $this->Year;
	}

	function course() {
		return $this->Course;
	}

	function train() {
		return $this->Train;
	}

	function day() {
		return $this->Day;
	}
	function memberid() {
		return $this->Memberid;
	}

	function post() {
		return $this->Post;
	}

}
?>
