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

        $active_year = $acStandard->getOneValue(AccountStandard::CONST_YEAR);
        $active_month = $acStandard->getOneValue(AccountStandard::CONST_MONTH);
        $endPostIds = $acStandard->getOneValueAsArray(AccountStandard::CONST_END_MONTH_TRANSFER_POSTS);

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

    function insertParams() {
        $acStandard = new AccountStandard($this->db);
        $values = $acStandard->getValues(array(AccountStandard::CONST_YEAR, AccountStandard::CONST_MONTH, AccountStandard::LAST_SPRING_MONTH));
        $year = $values[AccountStandard::CONST_YEAR];
        $month = $values[AccountStandard::CONST_MONTH];

        $lastDay = new eZDate();
        $lastDay->setDay(1);
        $lastDay->setMonth($active_month);
        $lastDay->setYear($active_year);
        $daysInMonth = $lastDay->daysInMonth();
        return array("month" => $month, "year" => $year, "lastDay" => $daysInMonth, "lastSpringMonth" => $values[AccountStandard::LAST_SPRING_MONTH]);
    }

    function endMonth($params, $changePersonId) {
        $acStandard = new AccountStandard($this->db);
        $active_month = $params["month"];
        $active_year = $params["year"];
        $endTransferPost = $acStandard->getOneValue(AccountStandard::CONST_END_MONTH_POST);

        $acPostType = new AccountPostType($this->db);
        $endPostIds = $acStandard->getOneValueAsArray(AccountStandard::CONST_END_MONTH_TRANSFER_POSTS);
        $this->endPosts = $acPostType->getSomeIndexedById($endPostIds);


        if ($active_month == 12) {
            header("HTTP/1.0 514 Illegal state");

            die("Can't end last month in year - use end year");
        }

        $amounts = $this->status();

        foreach (array_keys($amounts) as $post) {
            $amount = $amounts[$post];

            $this->transferPost(($active_month + 1), $active_month, $active_year, $post, $amount, $params["lastDay"], $endTransferPost, 1,$changePersonId);
            $this->transferPost($active_month, ($active_month + 1), $active_year, $post, $amount, 1, $endTransferPost, -1,$changePersonId);
        }

        $acStandard->setValue(AccountStandard::CONST_MONTH, ($active_month +1));

    }

    function transferPost($transfer_month, $active_month, $active_year, $post, $amount, $dayInMonth, $endTransferPost, $revFactor,$changePersonId) {
        $acAccountLine = new AccountLine($this->db);
        $ezDate = new eZDate();
        $acAccountLine->setNewLatest("Overf&oslash;ring " . $this->endPosts[$post]->getDescription() . " " . $ezDate->monthNameNor($transfer_month), $dayInMonth, $active_year, $active_month);
        $acAccountLine->store($active_month, $active_year);

        if ($amount > 0) {
            $acAccountLine->addPostSingleAmount($acAccountLine->getId(), (1 * $revFactor), $endTransferPost, $amount,0,$changePersonId);
            $acAccountLine->addPostSingleAmount($acAccountLine->getId(), (-1 * $revFactor), $post, $amount,0,$changePersonId);
        } else {
            $acAccountLine->addPostSingleAmount($acAccountLine->getId(), (-1 * $revFactor), $endTransferPost, abs($amount),0,$changePersonId);
            $acAccountLine->addPostSingleAmount($acAccountLine->getId(), (1 * $revFactor), $post, abs($amount),0,$changePersonId);
        }
    }
}
?>
