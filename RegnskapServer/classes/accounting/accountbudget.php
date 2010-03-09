<?php
class AccountBudget {

    private $db;

    public $PostType;
    public $Amount;

    function AccountBudget($db, $postType = 0, $amount = 0) {
        $this->db = $db;
        $this->PostType = $postType;
        $this->Amount = $amount;
    }

    function save($year, $budgetData) {
        $this->db->begin();

        $delPrep = $this->db->prepare("delete from " . AppConfig :: DB_PREFIX . "budsjett where year = ?");

        $delPrep->bind_params("i", $year);
        $delPrep->execute();

        $savePrep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "budsjett (year, post_type, amount, earning) values (?,?,?, ?)");

        foreach($budgetData as $oneRow) {
            $savePrep->bind_params("iidi", $year, $oneRow->postType, str_replace(",","", $oneRow->value), $oneRow->earning == "true" ? "1" : 0);
            $savePrep->execute();
        }

        $this->db->commit();
        return 1;
    }

    function getAllBudgetYears() {
        $prep = $this->db->prepare("select distinct year from " . AppConfig :: DB_PREFIX . "budsjett order by year");
        return $prep->execute();
    }

    function getBudgetData($year = 0) {
        if($year) {
            $prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "budsjett where year = ?");
            $prep->bind_params("i", $year);
        } else {
            $prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "budsjett where year = (select max(year) from " . AppConfig :: DB_PREFIX . "budsjett)");
        }
         
        return $prep->execute();
    }

    function getEarningsAndCostsFromGivenYear($year) {

        $result = array();

        $addedWhere = "RP.post_type >= 4000 and RP.post_type <= 8500 and RP.post_type <> 8040";
        $prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost from " . AppConfig :: DB_PREFIX . "post RP, " . AppConfig :: DB_PREFIX . "line RL where RL.year = ? and RL.id=RP.line and RP.debet = ? and $addedWhere group by post_type order by post_type");
        $prep->bind_params("is", $year, "1");
        $costDebet = $prep->execute();

        $prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost from " . AppConfig :: DB_PREFIX . "post RP, " . AppConfig :: DB_PREFIX . "line RL where RL.year = ? and RL.id=RP.line and RP.debet = ? and $addedWhere group by post_type order by post_type");
        $prep->bind_params("is", $year, "-1");
        $costKredit = $prep->execute();

        $result["cost"] = $this->sumPerYear($costDebet, $costKredit);

        $addedWhere = "((RP.post_type >= 3000 and RP.post_type < 4000) or RP.post_type=8400 or RP.post_type=8040)";
        $prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost from " . AppConfig :: DB_PREFIX . "post RP, " . AppConfig :: DB_PREFIX . "line RL where RL.year = ? and RL.id=RP.line and RP.debet = ? and $addedWhere group by post_type order by post_type");
        $prep->bind_params("is", $year, "1");
        $earningsDebet = $prep->execute();

        $prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost from " . AppConfig :: DB_PREFIX . "post RP, " . AppConfig :: DB_PREFIX . "line RL where RL.year = ? and RL.id=RP.line and RP.debet = ? and $addedWhere group by post_type order by post_type");
        $prep->bind_params("is", $year, "-1");
        $earningsKredit = $prep->execute();

        $result["earnings"] = $this->sumPerYear($earningsKredit, $earningsDebet);

        return $result;
    }


    function getEarningsAndCostsFromAllYears() {

        $result = array();

        $addedWhere = "RP.post_type >= 4000 and RP.post_type <= 8500 and RP.post_type <> 8040";
        $prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost, year from " . AppConfig :: DB_PREFIX . "post RP, " . AppConfig :: DB_PREFIX . "line RL where RL.id=RP.line and RP.debet = ? and $addedWhere group by year, post_type order by post_type");
        $prep->bind_params("s", "1");
        $costDebet = $prep->execute();

        $prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost, year from " . AppConfig :: DB_PREFIX . "post RP, " . AppConfig :: DB_PREFIX . "line RL where RL.id=RP.line and RP.debet = ? and $addedWhere group by year, post_type order by post_type");
        $prep->bind_params("s", "-1");
        $costKredit = $prep->execute();

        $result["cost"] = $this->sumPerYear($costDebet, $costKredit);

        $addedWhere = "((RP.post_type >= 3000 and RP.post_type < 4000) or RP.post_type=8400 or RP.post_type=8040)";
        $prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost, year from " . AppConfig :: DB_PREFIX . "post RP, " . AppConfig :: DB_PREFIX . "line RL where RL.id=RP.line and RP.debet = ? and $addedWhere group by year, post_type order by post_type");
        $prep->bind_params("s", "1");
        $earningsDebet = $prep->execute();

        $prep = $this->db->prepare("select RP.post_type,sum(amount) as sumpost, year from " . AppConfig :: DB_PREFIX . "post RP, " . AppConfig :: DB_PREFIX . "line RL where RL.id=RP.line and RP.debet = ? and $addedWhere group by year, post_type order by post_type");
        $prep->bind_params("s", "-1");
        $earningsKredit = $prep->execute();

        $result["earnings"] = $this->sumPerYear($earningsKredit, $earningsDebet);

        return $result;
    }

    function sumPerYear($oneList, $twoList) {
        $result = array();

        foreach ($oneList as $one) {
            $k = $one["year"]."-".$one["post_type"];
            $result[$k] = $one["sumpost"];
        }

        foreach($twoList as $one) {
            $k = $one["year"]."-".$one["post_type"];

            if(array_key_exists($k, $result)) {
                $result[$k] -= $one["sumpost"];
            } else {
                $result[$k] = 0 - $one["sumpost"];
            }
        }
        return $result;
    }


    function getMemberships($year) {
        $prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "budget_membership where year = ?");
        $prep->bind_params("i", $year);

        $res = $prep->execute();

        if (count($res) == 0) {
            return array (
				"year" => $year,
				"year_members" => 0,
                "year_youth" => 0,
				"spring_train" => 0,
				"spring_course" => 0,
				"spring_youth" => 0,
				"fall_youth" => 0,
				"fall_train" => 0,
				"fall_course" => 0
            );
        }

        return array_shift($res);
    }

    function saveMemberships($memberships) {
        $keyYear = $memberships->year;

        $prep = $this->db->prepare("select * from " . AppConfig :: DB_PREFIX . "budget_membership where year = ?");
        $prep->bind_params("i", $keyYear);

        $res = $prep->execute();

        if (count($res) == 0) {
            $prep = $this->db->prepare("insert into " . AppConfig :: DB_PREFIX . "budget_membership set year=?");
            $prep->bind_params("i", $keyYear);
            $res = $prep->execute();
        }

        $prep = $this->db->prepare("update " . AppConfig :: DB_PREFIX . "budget_membership set fall_train=?,fall_course=?,fall_youth=?, spring_train=?,spring_course=?,spring_youth=?,year_members=?,year_youth=? where year=?");
        $prep->bind_params("iiiiiiiii", $memberships->fall_train,$memberships->fall_course,$memberships->fall_youth,$memberships->spring_train,$memberships->spring_course,$memberships->spring_youth,$memberships->year_members,$memberships->year_youth, $keyYear);
        $prep->execute();

        return $this->db->affected_rows();

    }
}
?>
