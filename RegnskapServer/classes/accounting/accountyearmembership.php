<?php
class AccountyearMembership {
    public $Year;
    public $User;
    public $Regn_line;
    public $Youth;
    private $db;


    function AccountyearMembership($db, $user = 0, $year = 0, $regn_line = 0, $youth = 0) {
        if(!$db) {
            $db = new DB();
        }
        $this->db = $db;
        $this->Year = $year;
        $this->User = $user;
        $this->Regn_line = $regn_line;
        $this->Youth = $youth;
    }

    function addCreditPost($line, $amount) {

        $standard = new AccountStandard($this->db);

        $postType = $standard->getOneValue(AccountStandard::CONST_BUDGET_YEAR_POST);

        $post = new AccountPost($this->db, $line, "-1", $postType, $amount);
        return $post->store();

    }

    function getAll() {
        $prep = $this->db->prepare("select * from " . AppConfig::pre() . "year_membership");
        $res = $prep->execute();

        foreach($res as &$one) {
            if(!$one["youth"]) {
                $one["youth"] = 0;
            }
            unset($one["regn_line"]);
        }
        return $res;
    }

    function addDebetPost($line, $postType, $amount) {
        $post = new AccountPost($this->db, $line, "1", $postType, $amount);
        return $post->store();
    }

    function getAllMemberNames($year) {
        /* Using group by here due to previous bug which added duplicate entries. */
        $prep = $this->db->prepare("select firstname, lastname,id, youth from " . AppConfig::pre() . "person P," . AppConfig::pre() . "year_membership C where C.memberid = P.id and C.year=? group by P.firstname,P.lastname order by P.lastname, P.firstname");
        $prep->bind_params("i", $year);
        $query_array = $prep->execute();

        $result = array ();

        foreach ($query_array as $one) {
            $result[] = array (
            $one["firstname"],
            $one["lastname"],
            $one["id"],
            $one["youth"]
            );
        }
        return $result;

    }

    function delete($year, $person) {
        $prep = $this->db->prepare("delete from " . AppConfig::pre() . "year_membership where memberid = ? and year=?");
        $prep->bind_params("ii", $person, $year);
        $prep->execute();
        return $this->db->affected_rows();
    }

    function getUserMemberships($user) {

        $prep = $this->db->prepare("select memberid, year, regn_line from " . AppConfig::pre() . "year_membership where memberid = ? group by memberid, year, regn_line order by year");
        $prep->bind_params("i", $user);
        $query_array = $prep->execute();

        $result = array ();

        foreach ($query_array as $one) {
            $result[] = & new AccountyearMembership(null, $user, $one["year"], $one["regn_line"]);
        }
        return $result;
    }

    function store() {
        $prep = $this->db->prepare("select * from " . AppConfig::pre() . "year_membership where year = ? and memberid=?");
        $prep->bind_params("ii", $this->Year, $this->User);
        $res = $prep->execute();

        if (sizeof($res)) {
            return;
        }

        $prep = $this->db->prepare("insert into " . AppConfig::pre() . "year_membership set year = ?, memberid=?, regn_line=?, youth=?");

        $prep->bind_params("iiii", $this->Year, $this->User, $this->Regn_line, $this->Youth);

        $prep->execute();

        return $this->db->affected_rows();
    }

    function getReportUsersBirthdate($year) {
        $prep = $this->db->prepare("select distinct id as id, firstname as firstname, lastname as lastname, birthdate as birthdate, gender from " . AppConfig::pre() . "year_membership, " . AppConfig::pre() . "person where memberid=id and year=? order by birthdate desc,lastname,firstname");
        $prep->bind_params("i", $year);
        $res = $prep->execute();

        $arr = array ();
        foreach ($res as $one) {

            $d = "";
            if(array_key_exists("birthdate", $one) && $one["birthdate"]) {
                $tmpdate = new eZDate();
                $tmpdate->setMySQLDate($one["birthdate"]);
                $d = $tmpdate->displayAccount();
            }

            $arr[] = new ReportUserBirthdate($one["id"], $one["firstname"], $one["lastname"], $d, $one["gender"]);
        }

        return $arr;
    }

    function getReportUsersFull($year, $limit = 0) {
        $sql = "select distinct * from " . AppConfig::pre() . "year_membership, " . AppConfig::pre() . "person where memberid=id and year=? order by lastname,firstname";

        if($limit) {
            $sql .= " limit 1";
        }

        $prep = $this->db->prepare($sql);
        $prep->bind_params("i", $year);
        $res = $prep->execute();

        return $res;
    }

    function getOverview() {
        $prep = $this->db->prepare("select count(*) as C, year, youth from " . AppConfig::pre() . "year_membership group by year,youth");
        return $prep->execute();
    }

    function getFirstYear($default) {
        $prep = $this->db->prepare("select min(year) as y from " . AppConfig::pre() . "year_membership");
        $res = $prep->execute();

        foreach($res as $one) {
            return $one["y"];
        }
        return $default;
    }

    public function getYearList() {
        $prep = $this->db->prepare("select distinct(year) from " . AppConfig::pre() . "year_membership");

        $res = $prep->execute();

        $years = array();

        foreach($res as $one) {
            $years[] = $one["year"];
        }

        return $years;
    }

    public function missingMemberships($year) {
        $prep = $this->db->prepare("select id, firstname, lastname from " . AppConfig::pre() . "person P where year_membership_required = 1 and P.id not in (select memberid from " . AppConfig::pre() . "year_membership M where M.year = ?) order by lastname, firstname");
        $prep->bind_params("i", $year);
        return $prep->execute();
    }

}

?>
