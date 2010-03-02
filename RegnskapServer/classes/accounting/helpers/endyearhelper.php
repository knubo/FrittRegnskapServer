<?php

class EndYearHelper {
    private $db;
    private $result;
    private $year;
    private $reportYear;

    function EndYearHelper($db) {
        $this->db = $db;
    }

    function endYear($db) {
        $acStandard = new AccountStandard($this->db);
		$active_month = $acStandard->getOneValue(AccountStandard::CONST_MONTH);
		$active_year = $acStandard->getOneValue(AccountStandard::CONST_YEAR);

	    $endYearData = $this->getEndYearData($active_year);
		
		
        $acPostType = new AccountPostType($this->db);

		$lastDay = new eZDate();
		$lastDay->setDay(1);
		$lastDay->setMonth($active_month);
		$lastDay->setYear($active_year);
		$daysInMonth = $lastDay->daysInMonth();

		$accountLineCurrentYear = new AccountLine($this->db);
		$accountLineCurrentYear->setNewLatest("UB ".$active_year, $daysInMonth, $active_year, $active_month);
		$accountLineCurrentYear->store($active_month, $active_year);
		
		foreach ($endYearData as $onePost) {
		    $accountLineCurrentYear->addPostSingleAmount($accountLineCurrentYear->getId(), $onePost["DEBET"], $onePost["post"], $onePost["value"]);
		}

		$accountLineNextYear = new AccountLine($this->db);
		$accountLineNextYear->setNewLatest("IB ".$active_year, 1, ($active_year + 1), 1);
		$accountLineNextYear->store(1, ($active_year + 1));
		
        foreach ($endYearData as $onePost) {
		    $accountLineNextYear->addPostSingleAmount($accountLineNextYear->getId(), $onePost["DEBET"] == "1" ? "-1" : "1", $onePost["post"], $onePost["value"]);
		}
		
    }
    
    
    function getEndYearData($year) {
        $this->result = array();
        $this->year = $year;
        $this->reportYear = new ReportYear($this->db);

        $this->addFor1000_to_2000();
        $this->addForFond(1926, 2001);
        $this->addForFond(1927, 2002);
        $this->addFor2000_to_3000();

        

        return $this->result;
    }

    function addFor2000_to_3000() {
        $data = $this->reportYear->list_sums_ownings_excluded_fond($this->year);

        $this->addForDatato2050($data);
    }

    function addForFond($fond, $balancePost) {
        $data = $this->reportYear->list_sums_fond($this->year, $fond);

        if(count($data) > 1) {
            header("HTTP/1.0 514 Illegal state");

            die("Got more than one sum line for fond $fond.");
        }

        foreach(array_keys($data) as $one) {
            $total += $data[$one]["value"];


            if($data[$one]["value"] > 0) {
                $add = array("description"=> $data[$one]["description"], "post" => $one, "value" => $data[$one]["value"]);
                $add["DEBET"] = -1;
                $this->result[] = $add;
                $this->result[] = array("post" => $balancePost, "DEBET" => 1, "value" => $data[$one]["value"]);

            } else if($data[$one]["value" < 0]) {
                $add = array("description"=> $data[$one]["description"], "post" => $one, "value" => ~$data[$one]["value"]);
                $add["DEBET"] = 1;
                $this->result[] = $add;
                $this->result[] = array("post" => $balancePost, "DEBET" => -1, "value" => ~$data[$one]["value"]);
            }
        }

    }


    function addFor1000_to_2000() {
        $data = $this->reportYear->list_sums_2000_excluded_fond($this->year);

        $this->addForDatato2050($data);
    }

    function addForDataTo2050($data) {

        $total = 0;
        foreach(array_keys($data) as $one) {
            $total += $data[$one]["value"];


            if($data[$one]["value"] > 0) {
                $add = array("description"=> $data[$one]["description"], "post" => $one, "value" => $data[$one]["value"]);
                $add["DEBET"] = -1;
                $this->result[] = $add;
            } else if($data[$one]["value"] < 0) {
                $add = array("description"=> $data[$one]["description"], "post" => $one, "value" => 0 - $data[$one]["value"]);
                $add["DEBET"] = 1;
                $this->result[] = $add;
            }
        }

        if($total > 0) {
            $this->result[] = array("post" => 2050, "DEBET" => 1, "value" => $total);
        } else if($total < 0) {
            $this->result[] = array("post" => 2050, "DEBET" => -1, "value" => 0 - $total);
        }

    }

     
}


?>