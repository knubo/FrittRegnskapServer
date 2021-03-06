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

        if (strlen($info->eventEndDate) > 0) {
            $eventEndDate = new eZDate();
            $eventEndDate->setDate($info->eventEndDate);
        }

        if (!$info->id) {

            $prep = $this->db->prepare("insert into " . AppConfig::pre() . "event_schema (form, eventdesc, active, start_date, end_date, event_date, event_end_date, max_people) values(?,?,0,?,?,?,?, ?)");
            $prep->bind_params("ssssssi", $data, $info->name, $startDate->mySQLDate(), $endDate->mySQLDate(), $eventDate->mySQLDate(), $eventEndDate
                                                ? $eventEndDate->mySQLDate() : null, $info->maxPeople);
        } else {
            $prep = $this->db->prepare("update " . AppConfig::pre() . "event_schema set form = ?, eventdesc = ?, start_date = ? , end_date = ?, event_date = ?, max_people = ?,active=?, event_end_date = ? where id = ?");
            $prep->bind_params("sssssiisi", $data, $info->name, $startDate->mySQLDate(), $endDate->mySQLDate(), $eventDate->mySQLDate(), $info->maxPeople, $info->active, $eventEndDate
                                                  ? $eventEndDate->mySQLDate() : null, $info->id);
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

        $result = $prep->execute();
        $res = array_shift($result);

        return $res["form"];
    }

    public function getIfActive($id) {
        $prep = $this->db->prepare("select form from " . AppConfig::pre() . "event_schema where id=? and start_date <= now() and end_date >= now() and active=1");
        $prep->bind_params("i", $id);

        $res = $prep->execute();

        if (count($res) == 0) {
            return array("error" => "Not active");
        }

        $res = array_shift($res);

        return $res["form"];

    }

    public function listAllActive() {
        $prep = $this->db->prepare("select id,eventdesc, start_date as startDate, end_date as endDate, event_date as eventDate from " . AppConfig::pre() . "event_schema where active=1");

        return $prep->execute();
    }

    public function register($personId, $data) {
        $prep = $this->db->prepare("select 1 from regn_event_schema where id=? and start_date <= now() and end_date >= now() and active=1");
        $prep->bind_params(i, $data->id);
        $res = $prep->execute();

        if (count($res) == 0) {
            die("Bad input ");
        }

        try {
            $prep = $this->db->prepare("insert into " . AppConfig::pre() . "event_partisipant_meta (event_id, person_id, created_time, changed_time) values (?,?, now(), now()) ".
                                   " on duplicate key update changed_time = now(), created_time=created_time");
            $prep->bind_params("ii", $data->id, $personId);
            $prep->execute();

            $prep = $this->db->prepare("delete from " . AppConfig::pre() . "event_partisipant where event_id = ? and person_id = ?");
            $prep->bind_params("ii", $data->id, $personId);
            $prep->execute();

            $prep = $this->db->prepare("insert into " . AppConfig::pre() . "event_partisipant (event_id, person_id, group_key, group_value, gender) values (?,?,?,?,?)");

            foreach ($data as $key => $value) {
                if ($key == "id") {
                    continue;
                }

                $prep->bind_params("iisss", $data->id, $personId, $key, $value, 0);
                $prep->execute();
            }
            return 1;
        } catch (Exception $e) {
            return 0;
        }

    }

    public function participants($id) {
        $prep = $this->db->prepare("select P.id, firstname, lastname, created_time, changed_time, EP.gender from  " . AppConfig::pre() . "event_partisipant EP ,  " . AppConfig::pre() . "person P," . AppConfig::pre() . "event_partisipant_meta M where EP.person_id = P.id and EP.event_id = ? and M.person_id = P.id and M.event_id = EP.event_id group by EP.person_id");
        $prep->bind_params("i", $id);
        return $prep->execute();
    }

    public function groupedBy($id) {
        $prep = $this->db->prepare("select group_key, group_value, count(*) as count from " . AppConfig::pre() . "event_partisipant where event_id = ? group by group_key, group_value");
        $prep->bind_params("i", $id);
        return $prep->execute();
    }

    public function listParticipants() {
        $prep = $this->db->prepare("select id,eventdesc, start_date as startDate, count(distinct(person_id)) as participants from  " . AppConfig::pre() . "event_schema,  " . AppConfig::pre() . "event_partisipant P where id = event_id");
        return $prep->execute();
    }


}

?>