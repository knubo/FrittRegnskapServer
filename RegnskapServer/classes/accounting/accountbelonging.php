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

    function listItemsToDeprecate() {
        $prep = $this->db->prepare("select id, belonging, serial, deprecation_amount, current_price,owning_account, deprecation_account from  " . AppConfig::pre() ."belonging where (current_price > deprecation_amount) or (current_price < deprecation_amount and current_price > 0);");

        return $prep->execute();
    }
    
    function listAll($filter) {
        $filterSQL = "where 1 = 1";

        $searchWrap = $this->db->search("select B.*,P.firstname,P.lastname from " . AppConfig::pre() . "belonging B left join ". AppConfig::pre() .
        	"person P on (P.id = B.person)", "order by belonging limit 200");

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
                $prep = $this->db->prepare("insert into " . AppConfig::pre() . "belonging (belonging,description,serial,year_deprecation,purchase_price,warrenty_date,owning_account,deprecation_account,added_by_person,added_date,current_price,deprecation_amount,deleted,purchase_date) values (?,?,?,?,?,?,?,?,?,?,?,?,0,?)");
                $prep->bind_params("sssidsiiisdss", $req["owning"],$req["description"],$req["serial"],$req["yearsDeprecation"],$req["purchasePrice"],$warrenty_date,
                $req["accountOwning"],$req["accountDeprecation"],$added_by_person,$added_date,$req["currentAmount"],$req["eachMonth"], $purchase_date);
                $prep->execute();

                $belongingId = $this->db->insert_id();

                $accLine = new AccountLine($this->db);

                $accLine->setNewLatest($req["deprecationTitle"].":".$req["owning"], $req["day"], $req["year"], $req["month"],$added_by_person);
                $accLine->setAttachment($req["attachment"]);
                $accLine->setPostnmb($req["postnmb"]);
                $accLine->store();
                $lineId = $accLine->getId();

                $accLine->addPostSingleAmount($lineId, '1', $req["accountOwning"], $req["currentAmount"],0,0,$belongingId);
                $accLine->addPostSingleAmount($lineId, '-1', $req["accountDeprecation"], $req["currentAmount"],0,0,$belongingId);
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

    function belongingChangeAccounting($req, $added_by_person) {
        $posts = $this->updatePreview($req);

        $changeData = json_decode($req["change"]);

        $accLine = new AccountLine($this->db);

        $accLine->setNewLatest($changeData->description, $changeData->day, $changeData->year, $changeData->month,$added_by_person);
        $accLine->setAttachment($changeData->attachment);
        $accLine->setPostnmb($changeData->postNmb);
        $accLine->store();
        $lineId = $accLine->getId();

        foreach($posts as $post => $value) {
            if($value > 0) {
                $accLine->addPostSingleAmount($lineId, '1', $post, $value,0,0,$req["id"]);
            } else {
                $accLine->addPostSingleAmount($lineId, '-1', $post, 0 - $value,0,0,$req["id"]);
            }
        }
    }

    function updateBelonging($req, $personId) {

        if(array_key_exists("change", $req)) {
            $this->belongingChangeAccounting($req, $personId);
        }

        $pd = new eZDate();
        $now_date = $pd->mySQLDate();
        $person = array_key_exists("person", $req) ? $req["person"] : NULL;

        $pd = new eZDate();
        $pd->setDate($req["purchaseDate"]);
        $purchase_date = $pd->mySQLDate();

        $warrenty_date = NULL;

        if($req["warrentyDate"]) {
            $wd = new eZDate();
            $wd->setDate($req["warrentyDate"]);
            $warrenty_date = $wd->mySQLDate();
        }

        $prep = $this->db->prepare("update " . AppConfig::pre() . "belonging set person = ?, changed_by_person=?,changed_date=?,belonging=?,description=?,serial=?,deprecation_amount=?,purchase_date=?,warrenty_date=?,purchase_price=?,deprecation_account=?,owning_account=?,current_price=? where id = ?");

        $prep->bind_params("iissssdssdiidi", $person, $personId, $now_date, $req["owning"], $req["description"], $req["serial"], $req["eachMonth"], $purchase_date, $warrenty_date, $req["purchasePrice"], $req["accountDeprecation"], $req["accountOwning"], $req["currentAmount"], $req["id"]);

        $prep->execute();

        return array("updated" => 1);
    }

    function addToResult($result, $key, $diff) {
        if(array_key_exists($key, $result)) {
            $result[$key] += $diff;
        } else {
            $result[$key] = $diff;
        }
    }

    function updatePreview($req) {
        $data = $this->getOne($req["id"]);

        $result = array();

        if($req["accountDeprecation"] != $data["deprecation_account"]) {
            $result[$data["deprecation_account"]] = $data["current_price"];
            $result[$req["accountDeprecation"]] = 0 - $data["current_price"];
        }

        if($req["accountOwning"] != $data["owning_account"]) {
            $result[$data["owning_account"]] = 0 - $data["current_price"];
            $result[$req["accountOwning"]] = $data["current_price"];
        }


        if($req["currentAmount"] != $data["current_price"]) {
            $diff = $req["currentAmount"] - $data["current_price"];

            if($diff < 0) {
                $this->addToResult(&$result, $req["accountOwning"], $diff);
                $this->addToResult(&$result, $req["accountDeprecation"], 0 - $diff);
            } else {
                $this->addToResult(&$result, $req["accountDeprecation"], $diff);
                $this->addToResult(&$result, $req["accountOwning"], 0 - $diff);
            }
        }

        return $result;

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

            $accLine->addPostSingleAmount($lineId, '1', $data["deprecation_account"], $data["current_price"],0,0,$id);
            $accLine->addPostSingleAmount($lineId, '-1', $data["owning_account"], $data["current_price"],0,0,$id);

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

