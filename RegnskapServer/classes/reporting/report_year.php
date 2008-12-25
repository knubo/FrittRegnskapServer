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


	function list_sums_int($year, $debet, $ignore) {

		$prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost,debet from regn_post RP, regn_line RL where RL.id=RP.line and RL.year=? and RP.debet = ? and $ignore group by post_type,debet order by post_type");
		$prep->bind_params("is", $year, $debet);

		$res = $prep->execute();

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