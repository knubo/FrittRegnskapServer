<?php

class ReportYear {

    private $db;

    function ReportYear($db) {
        $this->db = $db;
    }

    function list_sums($year) {
    	$prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost,debet from regn_post RP, regn_line RL where RL.id=RP.line and RL.year=? group by post_type,debet order by post_type");
        $prep->bind_params("i", $year);

        $res = $prep->execute();

        $arr = array();
        foreach($res as $one) {
            if(array_key_exists($one["post_type"], $arr)) {
            	$arr[$one["post_type"]] += $one["sumpost"] * $one["debet"];
            } else {
            	$arr[$one["post_type"]] = $one["sumpost"] * $one["debet"];
            }
        }

        $prep = $this->db->prepare("select distinct(RP.post_type),RPT.description from regn_post RP, regn_line RL,regn_post_type RPT where RL.id=RP.line and RL.year=? and RPT.post_type = RP.post_type group by post_type,debet order by post_type");
        $prep->bind_params("i", $year);

        $res = $prep->execute();

        foreach($res as &$one) {
        	$one["sum"] = $arr[$one["post_type"]];
        }
        return $res;
    }

}