<?php


/*
 * Created on May 15, 2007
 *
 */

class Memberships {

	private $Year;
	private $Course;
	private $Train;
	private $Youth;
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

            if (Memberships :: starts_with($one, "youth")) {
                Memberships :: find($perMemberId, substr($one, 5))->Youth = true;
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

	function store($db, $objects) {
		$standard = new AccountStandard($db);
      	$active_month = $standard->getOneValue("STD_MONTH");
      	$active_year = $standard->getOneValue("STD_YEAR");
      	$active_semester = $standard->getOneValue("STD_SEMESTER");

        $accPrices = new AccountMemberPrice($db);
        $prices = $accPrices->getCurrentPrices();

		$memberPrice = $prices["year"];
		$coursePrice = $prices["course"];
		$trainPrice = $prices["train"];
		$trainPrice = $prices["youth"];

		foreach($objects as $one) {

			$line = 0;
			if($one->day()) {
				$user = new AccountPerson($db);
				$user->load($one->memberid());

				if(!$user) {
					throw new Exception("Failed to load user ".$one->memberid());
				}

				$line = new AccountLine($db);
				$line->setNewLatest("M: ".$user->name(), $one->day(), $active_year, $active_month);
				$line->store();
			}

			$lineId = $line ? $line->getId() : 0;

	     	/* Register the memberships... */
	     	if($one->year()) {
	 		  $yearM = new AccountYearMembership($db, $one->memberid(), $active_year, $lineId);
			  $yearM->store();
			  if($lineId) {
			  	  $yearM->addCreditPost($lineId, $memberPrice);
		  		  $yearM->addDebetPost($lineId, $one->post(), $memberPrice);
			  }
		  	}

		  	if($one->train()) {
				$trainM = new AccountSemesterMembership($db, AccountSemesterMembership::train(), $one->memberid(), $active_semester, $lineId);
				$trainM->store();
				if($lineId) {
			  		$trainM->addCreditPost($lineId, $trainPrice);
		  			$trainM->addDebetPost($lineId, $one->post(), $trainPrice);
				}
	      	}
			if($one->course()) {
				$courseM = new AccountSemesterMembership($db, AccountSemesterMembership::course(), $one->memberid(), $active_semester, $lineId);
				$courseM->store();
				if($lineId) {
			  		$courseM->addCreditPost($lineId, $coursePrice);
		  			$courseM->addDebetPost($lineId, $one->post(), $coursePrice);
				}
			}


            if($one->youth()) {
                $courseM = new AccountSemesterMembership($db, AccountSemesterMembership::youth(), $one->memberid(), $active_semester, $lineId);
                $courseM->store();
                if($lineId) {
                    $courseM->addCreditPost($lineId, $coursePrice);
                    $courseM->addDebetPost($lineId, $one->post(), $coursePrice);
                }
            }

		}
		return 1;
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

    function youth() {
        return $this->Youth;
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
