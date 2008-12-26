<?php
class ReportYear {

	private $db;

	function ReportYear($db) {
		$this->db = $db;
	}

	function fixNum($num) {
        $exp = explode(".", $num);

        if(count($exp) == 1) {
        	return "$num.00";
        }

        if(strlen($exp[1]) == 1) {
        	return "$num0";
        }

        return $num;
	}

    function list_sums($year, $debet) {
    	return $this->list_sums_int($year, $debet,"RP.post_type < 9000 and RP.post_type >= 3000");
    }


    function list_sums_3000($year, $debet) {
        return $this->list_sums_int($year, $debet,"RP.post_type < 3000 and RP.post_type >= 2000 and RP.post_type <> 2050");
    }

    function list_sums_2000($year) {
        $prep = $this->db->prepare("select RP.post_type, sum(RP.amount) as sumpost  from regn_line RL, regn_post RP " .
                "where RP.line = RL.id and debet = ? and year=? and month = 12 and RP.post_type < 2000 and " .
                "RL.id not in(select RL2.id from regn_line RL2, regn_post RP2 where RL2.month = 12 and " .
                "RL2.year = ? and RP2.line = RL2.id and RP2.post_type = 8800) group by RP.post_type;");

        $prep->bind_params("sii", '1', $year, $year);
        $resDebet = $this->makeSumPerPostType($prep->execute());

        $prep->bind_params("sii", '-1', $year, $year);
        $resKredit = $this->makeSumPerPostType($prep->execute());

        $sums = $this->sumDebetAndKreditValues($resDebet, $resKredit);

        return $this->addDescriptionsAndFixSums2000($sums, $year);
    }

    function addDescriptionsAndFixSums2000($sums, $year) {
        $prep = $this->db->prepare("select distinct(RP.post_type),RPT.description from regn_post RP, regn_line RL,regn_post_type RPT where RL.id=RP.line and RL.year=? and RPT.post_type = RP.post_type and RP.post_type <= 2000 group by post_type,debet order by post_type");
        $prep->bind_params("i", $year);

        $res = $prep->execute();

        foreach($res as $one) {
        	$sums[$one["post_type"]]["desc"] = $one["description"];
            $sums[$one["post_type"]]["value"] = $this->fixNum($sums[$one["post_type"]]["value"]);
        }

        return $sums;
    }

    function sumDebetAndKreditValues($resDebet, $resKredit) {
    	$sums = array();
        foreach(array_keys($resDebet) as $debKey) {

            $sums[$debKey] = array("value" => $resDebet[$debKey]);
        }
        foreach(array_keys($resKredit) as $kredKey) {
            if(array_key_exists($kredKey, $sums)) {
                $sums[$kredKey]["value"] -= $resKredit[$kredKey];
            } else {
                $sums[$kredKey] = array("value" => 0 - $resKredit[$kredKey]);
            }
        }
        return $sums;
    }

    function makeSumPerPostType($lines) {
        $res = array();
        foreach($lines as $one) {
            $res[$one["post_type"]] = $one["sumpost"];
        }
        return $res;
    }

	function list_sums_int($year, $debet, $ignore) {

		$prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost,debet from regn_post RP, regn_line RL where RL.id=RP.line and RL.year=? and RP.debet = ? and $ignore group by post_type,debet order by post_type");
		$prep->bind_params("is", $year, $debet);

		$res = $prep->execute();

        return $this->result($res, $year);
    }

    function result($res, $year) {
		$arr = array ();
		foreach ($res as $one) {
			if (array_key_exists($one["post_type"], $arr)) {
				$arr[$one["post_type"]] += $one["sumpost"];
			} else {
				$arr[$one["post_type"]] = $one["sumpost"];
			}
		}

		$prep = $this->db->prepare("select distinct(RP.post_type),RPT.description from regn_post RP, regn_line RL,regn_post_type RPT where RL.id=RP.line and RL.year=? and RPT.post_type = RP.post_type group by post_type,debet order by post_type");
		$prep->bind_params("i", $year);

		$res = $prep->execute();

		foreach ($res as & $one) {
			$one["sum"] = $this->fixNum($arr[$one["post_type"]]);
		}
		return $res;
	}

}