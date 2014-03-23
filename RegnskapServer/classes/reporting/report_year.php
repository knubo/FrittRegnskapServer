<?php
class ReportYear {

    private $db;

    function ReportYear($db) {
        $this->db = $db;
    }

    static function fixNum($num) {
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

    function list_sums_earnings($year, $month = 12) {
        return $this->list_sums_int($year, "((RP.post_type >= 3000 and RP.post_type < 4000) or RP.post_type=8400 or RP.post_type=8040)", -1.0, $month);
    }

    function list_sums_fond($year, $post, $month = 12) {
        return $this->list_sums_int($year, "RP.post_type = $post", 1.0, $month);
    }

    function list_sums_2000_excluded_fond($year, $month = 12) {
        return $this->list_sums_int($year, "RP.post_type >= 1000 and RP.post_type < 2000 and RP.post_type NOT IN (1926, 1927)", 1.0, $month);
    }

    function list_sums_ownings_excluded_fond($year, $month = 12) {
         return $this->list_sums_int($year, "RP.post_type >= 2000 and RP.post_type < 3000 and RP.post_type NOT IN (2050, 2002, 2001)", 1.0, $month);
    }

    function list_sums_cost($year, $month = 12) {
        return $this->list_sums_int($year, "RP.post_type >= 4000 and RP.post_type <= 8500 and RP.post_type <> 8040", 1.0, $month);
    }

    function list_sums_ownings($year, $month = 12) {
        return $this->list_sums_int($year, "RP.post_type < 3000 and RP.post_type >= 2000 and RP.post_type <> 2050 and RL.id not in (select RLI.id from " . AppConfig::pre() . "line RLI, " . AppConfig::pre() . "post RPI where RLI.id = RPI.line and " .
                "RLI.month = 12 and RPI.post_type = 2050 and RLI.year=$year and RLI.postnmb = (select max(YL.postnmb) from " . AppConfig::pre() . "line YL where YL.year=$year and YL.month=12) )", 1, $month);
    }


    function list_sums_2000($year, $month = 12) {
        $prep = $this->db->prepare("select RP.post_type, sum(RP.amount) as sumpost from " . AppConfig::pre() . "line RL, " . AppConfig::pre() . "post RP " .
                "where RP.line = RL.id and debet = ? and year=? and RP.post_type < 2000 and RP.amount > 0 and RL.id not in " .
                "(select RLI.id from " . AppConfig::pre() . "line RLI, " . AppConfig::pre() . "post RPI where RLI.id = RPI.line and " .
                "RLI.month = 12 and RLI.postnmb = (select max(YL.postnmb) from " . AppConfig::pre() . "line YL where YL.year=? and YL.month=12) and RPI.post_type = 2050 and RLI.year=?) and RL.month <= ? group by RP.post_type ");

        $prep->bind_params("siiii", '1', $year, $year, $year, $month);
        $resDebet = $this->makeSumPerPostType($prep->execute());

        $prep->bind_params("siiii", '-1', $year, $year, $year, $month);
        $resKredit = $this->makeSumPerPostType($prep->execute());

        $sums = $this->sumDebetAndKreditValues($resDebet, $resKredit, 1);

        return $this->addDescriptionsAndFixSums2000($sums, $year);
    }

    function addDescriptionsAndFixSums2000($sums, $year) {
        $prep = $this->db->prepare("select distinct(RP.post_type),RPT.description from " . AppConfig::pre() . "post RP, " . AppConfig::pre() . "line RL," . AppConfig::pre() . "post_type RPT where RL.id=RP.line and RL.year=? and RPT.post_type = RP.post_type and RP.post_type <= 2000 group by post_type,debet order by post_type");
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
                $sums[$kredKey]["value"] -= ($resKredit[$kredKey]["sumpost"] );
            } else {
                $sums[$kredKey] = array("value" => (0.0 - ($resKredit[$kredKey]["sumpost"])));
            }
            $sums[$kredKey]["description"] = $resKredit[$kredKey]["description"];
        }

        foreach(array_keys($sums) as $key) {
            $sums[$key]["value"] = $sums[$key]["value"] * $sign;
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

    function list_sums_int($year, $ignore, $sign, $month) {
        $prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost, RPT.description from " . AppConfig::pre() . "post RP, " . AppConfig::pre() . "line RL," . AppConfig::pre() . "post_type RPT where RL.id=RP.line and RL.year=? and RP.debet = '1' and $ignore and RPT.post_type = RP.post_type and RL.month <= ? group by post_type order by post_type");
        $prep->bind_params("ii", $year, $month);

        $debRes = $prep->execute();

        $resDebet = $this->makeSumPerPostType($debRes);

        $prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost, RPT.description from " . AppConfig::pre() . "post RP, " . AppConfig::pre() . "line RL," . AppConfig::pre() . "post_type RPT where RL.id=RP.line and RL.year=? and RP.debet = '-1' and $ignore and RPT.post_type = RP.post_type and RL.month <= ? group by post_type order by post_type");

        $prep->bind_params("ii", $year, $month);

        $kredLines = $prep->execute();

        $resKredit = $this->makeSumPerPostType($kredLines);

        $sums = $this->sumDebetAndKreditValues($resDebet, $resKredit, $sign);

        return $sums;
    }


}