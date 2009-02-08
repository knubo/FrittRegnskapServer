<?php
class ReportYear {

	private $db;

	function ReportYear($db) {
		$this->db = $db;
	}

	function fixNum($num) {
        if($num == 0) {
        	return "0.00";
        }
        $exp = explode(".", $num);

        if(count($exp) == 1) {
        	return "$num.00";
        }

        if(strlen($exp[1]) == 1) {
        	return "$num"."0";
        }

        return $num;
	}

    function list_sums_earnings($year) {
    	return $this->list_sums_int($year, "((RP.post_type >= 3000 and RP.post_type < 4000) or RP.post_type=8400 or RP.post_type=8040)", -1);
    }

    function list_sums_cost($year) {
        return $this->list_sums_int($year, "RP.post_type >= 4000 and RP.post_type <= 8500 and RP.post_type <> 8040", 1);
    }

    function list_sums_ownings($year) {
        return $this->list_sums_int($year, "RP.post_type < 3000 and RP.post_type >= 2000 and RP.post_type <> 2050 and RL.id not in (select RLI.id from " . AppConfig :: DB_PREFIX . "line RLI, " . AppConfig :: DB_PREFIX . "post RPI where RLI.id = RPI.line and " .
                "RLI.month = 12 and RPI.post_type = 2050 and RLI.year=$year and RLI.postnmb = (select max(YL.postnmb) from " . AppConfig :: DB_PREFIX . "line YL where YL.year=$year and YL.month=12) )", 1);
    }


    function list_sums_2000($year) {
        $prep = $this->db->prepare("select RP.post_type, sum(RP.amount) as sumpost  from " . AppConfig :: DB_PREFIX . "line RL, " . AppConfig :: DB_PREFIX . "post RP " .
                "where RP.line = RL.id and debet = ? and year=? and RP.post_type < 2000 and RP.amount > 0 and RL.id not in " .
                "(select RLI.id from " . AppConfig :: DB_PREFIX . "line RLI, " . AppConfig :: DB_PREFIX . "post RPI where RLI.id = RPI.line and " .
                "RLI.month = 12 and RLI.postnmb = (select max(YL.postnmb) from " . AppConfig :: DB_PREFIX . "line YL where YL.year=? and YL.month=12) and RPI.post_type = 2050 and RLI.year=?) group by RP.post_type ");

        $prep->bind_params("siii", '1', $year, $year, $year);
        $resDebet = $this->makeSumPerPostType($prep->execute());

        $prep->bind_params("siii", '-1', $year, $year, $year);
        $resKredit = $this->makeSumPerPostType($prep->execute());

        $sums = $this->sumDebetAndKreditValues($resDebet, $resKredit, 1);

        return $this->addDescriptionsAndFixSums2000($sums, $year);
    }

    function addDescriptionsAndFixSums2000($sums, $year) {
        $prep = $this->db->prepare("select distinct(RP.post_type),RPT.description from " . AppConfig :: DB_PREFIX . "post RP, " . AppConfig :: DB_PREFIX . "line RL," . AppConfig :: DB_PREFIX . "post_type RPT where RL.id=RP.line and RL.year=? and RPT.post_type = RP.post_type and RP.post_type <= 2000 group by post_type,debet order by post_type");
        $prep->bind_params("i", $year);

        $res = $prep->execute();

        foreach($res as $one) {
        	$sums[$one["post_type"]]["desc"] = $one["description"];
            $sums[$one["post_type"]]["value"] = $this->fixNum($sums[$one["post_type"]]["value"]);
        }

        return $sums;
    }

    function sumDebetAndKreditValues($resDebet, $resKredit, $sign) {
    	$sums = array();
        foreach(array_keys($resDebet) as $debKey) {
            $sums[$debKey] = array("value" => $resDebet[$debKey]["sumpost"], "description" => $resDebet[$debKey]["description"]);
        }

        foreach(array_keys($resKredit) as $kredKey) {
            if(array_key_exists($kredKey, $sums)) {
                $sums[$kredKey]["value"] -= ($resKredit[$kredKey]["sumpost"] * $sign);
            } else {
                $sums[$kredKey] = array("value" => (0 - ($resKredit[$kredKey]["sumpost"] * $sign)));
            }
            $sums[$kredKey]["description"] = $resKredit[$kredKey]["description"];
        }
        return $sums;
    }

    function makeSumPerPostType($lines) {
        $res = array();
        foreach($lines as $one) {
            $res[$one["post_type"]] = array("sumpost" => $one["sumpost"], "description" => $one["description"]);
        }
        return $res;
    }

	function list_sums_int($year, $ignore, $sign) {
		$prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost, RPT.description from " . AppConfig :: DB_PREFIX . "post RP, " . AppConfig :: DB_PREFIX . "line RL," . AppConfig :: DB_PREFIX . "post_type RPT where RL.id=RP.line and RL.year=? and RP.debet = ? and $ignore and RPT.post_type = RP.post_type group by post_type,debet order by post_type");
		$prep->bind_params("is", $year, '1');
        $resDebet = $this->makeSumPerPostType($prep->execute());


        $prep->bind_params("is", $year, '-1');
        $resKredit = $this->makeSumPerPostType($prep->execute());

        $sums = $this->sumDebetAndKreditValues($resDebet, $resKredit, $sign);

        return $sums;
    }


}