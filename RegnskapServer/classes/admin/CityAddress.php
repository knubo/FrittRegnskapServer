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
        $columns = explode(";", utf8_encode($rowData));

        $type = str_replace("\"", "", $columns[3]);

        switch ($type) {
            case "Postmottaker med eget postnr":
            case "Gate/vei adresse":
                $street = str_replace("\"", "", $columns[2]);
                break;
            default:
                $street = "";
        }

        $zip = str_replace("\"", "", $columns[0]);
        $city = str_replace("\"", "", $columns[1]);

        $prep = $this->db->prepare("insert into norwegiancities (zipcode, city, street) values (?,?,?)");

        $prep->bind_params("iss", $zip, $city, $street);
        $prep->execute();

    }

    public function find($zipcode, $street) {

        if ($zipcode) {
            $prep = $this->db->prepare("select * from norwegiancities where zipcode like ? order by street");
            $prep->bind_params(i, $zipcode);
        } else if ($street) {

            $parts = explode(" ",$street);

            if(preg_match("/.*\d.*/",end($parts))) {
                $street = implode(" ", array_splice($parts, 0, -1));
            }

            $prep = $this->db->prepare("select * from norwegiancities where street like ? order by street");
            $prep->bind_params(s, $street."%");
        }


        return $prep->execute();
    }

}
