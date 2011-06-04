<?php

class CityAddress {
    private $db;

    function CityAddress($db) {
        if (!$db) {
            $db = new DB();
        }
        $this->db = $db;
    }

    public function deleteAll() {
        $prep = $this->db->prepare("delete from norwegiancities");
        $prep->execute();
    }

    public function insert($rowData) {
        $columns = explode(";", $rowData);

        $type = str_replace("\"", "", $columns[3]);

        switch ($type) {
            case "Postmottaker med eget postnr":
            case "Gate/vei adresse":
                $street = $columns[2];
                break;
            default:
                $street = "";
        }

        $zip = str_replace("\"", "", $columns[0]);
        $city = str_replace("\"", "", $columns[1]);

        echo "\n$zip $city $street";

        $prep = $this->db->prepare("insert into norwegiancities (zipcode, city, street) values (?,?,?)");

        $prep->bind_params("iss", $zip, $city, $street);
        $prep->execute();

    }

}
