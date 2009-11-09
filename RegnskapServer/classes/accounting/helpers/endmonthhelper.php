<?php

/*
 * Created on May 28, 2007
 *
 */

class EndMonthHelper {
	private $db;
    private $endPosts;

	function EndMonthHelper($db) {
		$this->db = $db;
	}

	function status() {
		$acStandard = new AccountStandard($this->db);
		$acPostType = new AccountPostType($this->db);
		$acAccountLine = new AccountLine($this->db);

		$active_year = $acStandard->getOneValue("STD_YEAR");
		$active_month = $acStandard->getOneValue("STD_MONTH");
		$endPostIds = AccountPostType :: getEndPosts();

		$sumPosts = array ();

		foreach ($endPostIds as $id) {
            if(array_key_exists($id, $sumPosts)) {			
			   $sumPosts[$id] += $acAccountLine->sumPosts($id, $active_year, $active_month, '1');
            } else {
            	$sumPosts[$id] = $acAccountLine->sumPosts($id, $active_year, $active_month, '1');
            }
            
            if(array_key_exists($id, $sumPosts)) {
    			$sumPosts[$id] -= $acAccountLine->sumPosts($id, $active_year, $active_month, '-1');            	
            } else {
    			$sumPosts[$id] = $acAccountLine->sumPosts($id, $active_year, $active_month, '-1');            	            	
            }
            
		}

		return $sumPosts;
	}

	function endMonth() {
		$acStandard = new AccountStandard($this->db);
		$active_month = $acStandard->getOneValue("STD_MONTH");
		$active_year = $acStandard->getOneValue("STD_YEAR");
		$endTransferPost = AccountPostType :: getEndTransferPost();

        $acPostType = new AccountPostType($this->db);
        $endPostIds = AccountPostType :: getEndPosts();
        $this->endPosts = $acPostType->getSomeIndexedById($endPostIds);


		$lastDay = new eZDate();
		$lastDay->setDay(1);
		$lastDay->setMonth($active_month);
		$lastDay->setYear($active_year);
		$daysInMonth = $lastDay->daysInMonth();

		if ($active_month == 12) {
			die("Can't end last month - use year");
		}

		$amounts = $this->status();

		foreach (array_keys($amounts) as $post) {
			$amount = $amounts[$post];

			if ($amount == 0) {
				continue;
			}

			$this->transferPost(($active_month + 1), $active_month, $active_year, $post, $amount, $daysInMonth, $endTransferPost, 1);
			$this->transferPost($active_month, ($active_month + 1), $active_year, $post, $amount, 1, $endTransferPost, -1);
		}

		$acStandard->setValue("STD_MONTH", ($active_month +1));

	}

	function transferPost($transfer_month, $active_month, $active_year, $post, $amount, $dayInMonth, $endTransferPost, $revFactor) {
	    $acAccountLine = new AccountLine($this->db);
		$acAccountLine->setNewLatest("Overf&oslash;ring " . $this->endPosts[$post]->getDescription() . " " . eZDate :: monthNameNor($transfer_month), $dayInMonth, $active_year, $active_month);
		$acAccountLine->store($active_month, $active_year);

		if ($amount > 0) {
			$acAccountLine->addPostSingleAmount($acAccountLine->getId(), (1 * $revFactor), $endTransferPost, $amount);
			$acAccountLine->addPostSingleAmount($acAccountLine->getId(), (-1 * $revFactor), $post, $amount);
		} else {
			$acAccountLine->addPostSingleAmount($acAccountLine->getId(), (-1 * $revFactor), $endTransferPost, abs($amount));
			$acAccountLine->addPostSingleAmount($acAccountLine->getId(), (1 * $revFactor), $post, abs($amount));
		}
	}
}
?>
