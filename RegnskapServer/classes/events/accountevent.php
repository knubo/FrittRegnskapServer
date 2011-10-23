<?php

class AccountEvent {
    function AccountEvent($db) {
        if (!$db) {
            $db = new DB();
        }
        $this->db = $db;
    }

    public function save($data) {

        $info = json_decode($data);

        $startDate = new eZDate();
        $startDate->setDate($info->startDate);

        $endDate = new eZDate();
        $endDate->setDate($info->endDate);

        $eventDate = new eZDate();
        $eventDate->setDate($info->eventDate);

        if (!$info->id) {

            $prep = $this->db->prepare("insert into " . AppConfig::pre() . "event_schema (form, eventdesc, active, start_date, end_date, event_date, max_people) values(?,?,0,?,?,?,?)");
            $prep->bind_params("sssssi", $data, $info->name, $startDate->mySQLDate(), $endDate->mySQLDate(), $eventDate->mySQLDate(), $info->maxPeople);
        } else {
            $prep = $this->db->prepare("update " . AppConfig::pre() . "event_schema set form = ?, eventdesc = ?, start_date = ? , end_date = ?, event_date = ?, max_people = ?,active=? where id = ?");
            $prep->bind_params("sssssiii", $data, $info->name, $startDate->mySQLDate(), $endDate->mySQLDate(), $eventDate->mySQLDate(), $info->maxPeople, $info->active, $info->id);
        }

        $prep->execute();


        return $info->id ? $info->id : $this->db->insert_id();

    }

    public function listAll() {
        $prep = $this->db->prepare("select id,eventdesc as name, active, start_date as startDate, end_date as endDate, event_date as eventDate, max_people as maxPeople from " . AppConfig::pre() . "event_schema");

        return $prep->execute();
    }

    public function get($id) {
        $prep = $this->db->prepare("select form from " . AppConfig::pre() . "event_schema where id=?");
        $prep->bind_params("i", $id);

        $res = array_shift($prep->execute());

        return $res["form"];
    }

    public function listAllActive() {
        $prep = $this->db->prepare("select id,eventdesc, start_date as startDate, end_date as endDate, event_date as eventDate from " . AppConfig::pre() . "event_schema where active=1");

        return $prep->execute();
    }


}

?>