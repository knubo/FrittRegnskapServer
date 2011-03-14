<?php

class AccountBelonging {
    private $db;

    function AccountBelonging($db) {
        $this->db = $db;
    }

    function listAll($filter) {
        $filterSQL = "where 1 = 1";

        $searchWrap = $this->db->search("select * from " . AppConfig::pre() . "belonging", "limit 200");

        if($filter["belonging"]) {
            $searchWrap->addAndParam("s", "belonging", "%".(array_key_exists("belonging", $filter) ? $filter["belonging"] : "")."%");
        }
        if($filter["serial"]) {
            $searchWrap->addAndParam("s", "serial", "%".(array_key_exists("serial", $filter) ? $filter["serial"] : "")."%");
        }
        if($filter["description"]) {
            $searchWrap->addAndParam("s", "description", "%".(array_key_exists("description", $filter) ? $filter["description"] : "")."%");
        }

        $searchWrap->addAndParam("i", "deleted", $filter["deleted"]);

        return $searchWrap->execute();
    }

    function addBelonging($req, $added_by_person) {
        $this->db->begin();
        try {
            $warrenty_date = NULL;

            if($req["warrentyDate"]) {
                $warrenty_date = new eZDate();
                $warrenty_date->setDate($req["warrentyDate"]);
                $warrenty_date = $warrenty_date->displayAccount();
            }

            $added_date = new eZDate();
            $added_date = $added_date->displayAccount();

            if($req["accountDeprecation"] && $req["currentAmount"] > 0) {
                $prep = $this->db->prepare("insert into " . AppConfig::pre() . "belonging (belonging,description,serial,year_deprecation,purchase_price,warrenty_date,owning_account,deprecation_account,added_by_person,added_date,current_price,deprecation_amount,deleted,purchase_date) values (?,?,?,?,?,?,?,?,?,?,?,?,0),?");
                $prep->bind_params("sssiddiiiddd", $req["owning"],$req["description"],$req["serial"],$req["yearsDeprecation"],$req["purchasePrice"],$warrenty_date,$req["accountOwning"],$req["accountDeprecation"],$added_by_person,$added_date,$req["currentAmount"],$req["eachMonth"], $req["purchaseDate"]);
                $prep->execute();

                $accLine = new AccountLine($this->db);

                $accLine->setNewLatest($req["deprecationTitle"].":".$req["owning"], $req["day"], $req["year"], $req["month"],$added_by_person);
                $accLine->setAttachment($req["attachment"]);
                $accLine->setPostnmb($req["postnmb"]);
                $accLine->store();
                $lineId = $accLine->getId();

                $accLine->addPostSingleAmount($lineId, '1', $req["accountOwning"], $req["currentAmount"]);
                $accLine->addPostSingleAmount($lineId, '-1', $req["accountDeprecation"], $req["currentAmount"]);
            } else {
                $prep = $this->db->prepare("insert into " . AppConfig::pre() . "belonging (belonging,description,serial,purchase_price,warrenty_date,added_by_person,added_date,deleted,purchase_date) values (?,?,?,?,?,?,?,0,?)");
                $prep->bind_params("sssddid",$req["owning"],$req["description"],$req["serial"],$req["purchasePrice"],$warrenty_date,$added_by_person,$added_date,$req["purchaseDate"]);
                $prep->execute();
            }

            $this->db->commit();

            return array("status" => 1);
        } catch(Exception $error) {
            $this->db->rollback();
            return array("error" => $error, "status" => 0);
        }
    }
}