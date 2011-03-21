<?php

class AccountBelonging {
    private $db;

    function AccountBelonging($db) {
        $this->db = $db;
    }

    function getOne($id) {
        $prep = $this->db->prepare("select B.*,P.firstname,P.lastname from " .
        AppConfig::pre() .
        	"belonging B left join " . AppConfig::pre() .
        	"person P on (P.id = B.person) where B.id = ?");

        $prep->bind_params("i", $id);
         
        return array_shift($prep->execute());
    }

    function listAll($filter) {
        $filterSQL = "where 1 = 1";

        $searchWrap = $this->db->search("select * from " . AppConfig::pre() . "belonging", "order by belonging limit 200");

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
                $wd = new eZDate();
                $wd->setDate($req["warrentyDate"]);
                $warrenty_date = $wd->mySQLDate();
            }

            $pd = new eZDate();
            $pd->setDate($req["purchaseDate"]);
            $purchase_date = $pd->mySQLDate();

            $ad = new eZDate();
            $added_date = $ad->mySQLDate();

            if($req["accountDeprecation"] && $req["currentAmount"] > 0) {
                $prep = $this->db->prepare("insert into " . AppConfig::pre() . "belonging (belonging,description,serial,year_deprecation,purchase_price,warrenty_date,owning_account,deprecation_account,added_by_person,added_date,current_price,deprecation_amount,deleted,purchase_date) values (?,?,?,?,?,?,?,?,?,?,?,?,0),?");
                $prep->bind_params("sssidsiiisds", $req["owning"],$req["description"],$req["serial"],$req["yearsDeprecation"],$req["purchasePrice"],$warrenty_date,$req["accountOwning"],$req["accountDeprecation"],$added_by_person,$added_date,$req["currentAmount"],$req["eachMonth"], $purchase_date);
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
                $prep->bind_params("sssdsiss",$req["owning"],$req["description"],$req["serial"],$req["purchasePrice"],$warrenty_date,$added_by_person,$added_date,$purchase_date);
                $prep->execute();
            }

            $this->db->commit();

            return array("status" => 1);
        } catch(Exception $error) {
            $this->db->rollback();
            return array("error" => $error, "status" => 0);
        }
    }

    function updateBelonging($req, $personId) {
        $pd = new eZDate();
        $now_date = $pd->mySQLDate();
        $person = array_key_exists("person", $req) ? $req["person"] : NULL;

        $prep = $this->db->prepare("update " . AppConfig::pre() . "belonging set person = ?, changed_by_person=?,changed_date=?");

        $prep->bind_params("iis", $person, $personId, $now_date);

        $prep->execute();
    }

    function updatePreview($req) {

    }

    function deprecateBelongingFully($id, $changeData, $personId) {
        $data = $this->getOne($id);

        if($data["current_price"] > 0) {
            $accLine = new AccountLine($this->db);

            $accLine->setNewLatest($changeData->description, $changeData->day, $changeData->year, $changeData->month,$added_by_person);
            $accLine->setAttachment($changeData->attachment);
            $accLine->setPostnmb($changeData->postNmb);
            $accLine->store();
            $lineId = $accLine->getId();

            $accLine->addPostSingleAmount($lineId, '1', $data["deprecation_account"], $data["current_price"]);
            $accLine->addPostSingleAmount($lineId, '-1', $data["owning_account"], $data["current_price"]);
            
            $prep = $this->db->prepare("update " . AppConfig::pre() . "belonging set current_price = 0 where id = ?");
            $prep->bind_params("i", $id);
            $prep->execute();
        }
    }

    function deleteBeloning($id, $change, $personId) {
        $this->deprecateBelongingFully($id, json_decode($change), $personId);

        $pd = new eZDate();
        $now_date = $pd->mySQLDate();

        $prep = $this->db->prepare("update " . AppConfig::pre() . "belonging set deleted = 1, changed_by_person=?,changed_date=? where id = ?");
        $prep->bind_params("isi", $personId, $now_date, $id);
        $prep->execute();

        return array("status" => 1);
    }
}

