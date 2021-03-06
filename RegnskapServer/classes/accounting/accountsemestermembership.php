<?php
class AccountSemesterMembership {
    public $Semester;
    public $User;
    public $Regn_line;
    public $Text;
    private $db;
    private $Type;

    function AccountSemesterMembership($db, $type = 0, $user = 0, $semester = 0, $regn_line = 0, $desc = 0) {
        $this->db = $db;
        $this->Type = $type;
        $this->Semester = $semester;
        $this->User = $user;
        $this->Regn_line = $regn_line;
        $this->Text = $desc;
    }

    function addCreditPost($line, $amount) {

        $standard = new AccountStandard($this->db);

        switch ($this->Type) {
            case "course" :
                $postType = $standard->getOneValue(AccountStandard::CONST_BUDGET_COURSE_POST);
                break;
            case "train" :
                $postType = $standard->getOneValue(AccountStandard::CONST_BUDGET_TRAIN_POST);
                break;
            case "youth" :
                $postType = $standard->getOneValue(AccountStandard::CONST_BUDGET_YOUTH_POST);
                break;
        }

        $post = new AccountPost($this->db, $line, "-1", $postType, $amount);
        return $post->store();

    }


    function getAll() {
        $prep = $this->db->prepare("select * from " . AppConfig::pre() . $this->Type . "_membership");
        $res = $prep->execute();

        foreach ($res as &$one) {
            unset($one["regn_line"]);
        }
        return $res;
    }


    function addDebetPost($line, $postType, $amount) {
        $post = new AccountPost($this->db, $line, "1", $postType, $amount);
        return $post->store();
    }

    function getAllMemberNames($semester) {
        /* Using group by here due to previous bug which added duplicate entries. */
        $prep = $this->db->prepare("select firstname, lastname, id from " . AppConfig::pre() . "person P," . AppConfig::pre() . $this->Type . "_membership C where C.memberid = P.id and semester=? group by lastname, firstname,id order by lastname, firstname");
        $prep->bind_params("i", $semester);
        $query_array = $prep->execute();

        $result = array();

        foreach ($query_array as $one) {
            $result[] = array(
                $one["firstname"],
                $one["lastname"],
                $one["id"]
            );
        }
        return $result;

    }


    function delete($semester, $person) {
        $prep = $this->db->prepare("delete from " . AppConfig::pre() . $this->Type . "_membership where memberid = ? and semester=?");
        $prep->bind_params("ii", $person, $semester);
        $prep->execute();
        return $this->db->affected_rows();
    }

    function getUserMemberships($user, $type) {

        $prep = $this->db->prepare("select M.memberid, M.semester, M.regn_line, S.description from " . AppConfig::pre() . $type . "_membership M, " . AppConfig::pre() . "semester S where memberid = ? and S.semester = M.semester group by memberid, semester, regn_line order by semester");
        $prep->bind_params("i", $user);
        $query_array = $prep->execute();

        $result = array();

        foreach ($query_array as $one) {
            $result[] = & new AccountSemesterMembership(null, $type, $user, $one["semester"], $one["regn_line"], $one["description"]);
        }
        return $result;
    }

    function store() {
        $prep = $this->db->prepare("select * from " . AppConfig::pre() . $this->Type . "_membership where semester = ? and memberid=?");
        $prep->bind_params("ii", $this->Semester, $this->User);
        $res = $prep->execute();

        if (sizeof($res)) {
            return;
        }

        $sql = "insert into " . AppConfig::pre() . $this->Type . "_membership set semester = ?, memberid=?, regn_line=?";
        $prep = $this->db->prepare($sql);

        $prep->bind_params("iii", $this->Semester, $this->User, $this->Regn_line);

        $prep->execute();

        return $this->db->affected_rows();
    }

    function getOverview() {
        $prep = $this->db->prepare("select count(*) as C, M.semester,fall,year from " . AppConfig::pre() . $this->Type . "_membership M," . AppConfig::pre() . "semester S where S.semester=M.semester group by semester;");
        return $prep->execute();
    }

    function getFirstSemester($default) {
        $prep = $this->db->prepare("select min(C.semester) as c, min(T.semester) as t, min(Y.semester) as y from " .
                AppConfig::pre() . $this->course() . "_membership C," .
                AppConfig::pre() . $this->train() . "_membership T," .
                AppConfig::pre() . $this->youth() . "_membership Y");
        $res = $prep->execute();

        foreach ($res as $one) {
            $min = $default;
            if ($one["c"] && $one["c"] < $min) {
                $min = $one["c"];
            }

            if ($one["t"] && $one["t"] < $min) {
                $min = $one["t"];
            }

            if ($one["y"] && $one["y"] < $min) {
                $min = $one["y"];
            }


            return $min;
        }
    }


    function course() {
        return "course";
    }

    function train() {
        return "train";
    }

    function youth() {
        return "youth";
    }

    function getSemesterList() {
        $prep = $this->db->prepare("select distinct S.semester, description from " . AppConfig::pre() . $this->Type . "_membership M, " . AppConfig::pre() . "semester S where S.semester = M.semester order by year,fall");

        return $prep->execute();
    }

    public function getAllSemestersWithYears() {
        $prep = $this->db->prepare("select * from " . AppConfig::pre() . "semester where semester IN (" .
                "select semester from " . AppConfig::pre() . "train_membership union " .
                "select semester from " . AppConfig::pre() . "youth_membership union " .
                "select semester from " . AppConfig::pre() . "course_membership course_membership) " .
                "or year in (select year from " . AppConfig::pre() . "year_membership) order by year, fall");


        return $prep->execute();
    }

    public function missingMemberships($semester) {
        $prep = $this->db->prepare("select id,firstname,lastname from " . AppConfig::pre() . "person P where semester_membership_required = 1 " .
                "and P.id not in " .
                "(select memberid from " . AppConfig::pre() . "course_membership M where M.semester = ? union all " .
                "select memberid from " . AppConfig::pre() . "train_membership M where M.semester = ? union all " .
                "select memberid from " . AppConfig::pre() . "youth_membership M where M.semester = ? " .
                ")");

        $prep->bind_params("iii", $semester, $semester, $semester);
        return $prep->execute();
    }

    public function missingMembershipsComparedToPrevious($semester) {

        $sql = "select ID, firstname, lastname from regn_person, regn_semester RN, regn_semester RP where ".
          " RN.semester = ? and RP.fall = if (RN.fall = 1, 0, 1) and RP.year = if(RN.fall = 1, RN.year, RN.year-1) ".
        " and id IN ( ".
        " select M.memberid from regn_course_membership M ".
        " where ".
        " M.semester = RP.semester union all ".
        " select M.memberid from regn_train_membership M ".
        " where ".
        " M.semester = RP.semester union all ".
        " select M.memberid from regn_youth_membership M ".
        " where ".
        " M.semester = RP.semester ".
        " ) ".
        " and id not in ( ".
        " select M.memberid from regn_course_membership M ".
        "   where ".
        "    M.semester = ? union all ".
        " select M.memberid from regn_train_membership M ".
        "    where ".
        "    M.semester = ? union all ".
        " select M.memberid from regn_youth_membership M ".
        "    where ".
        "    M.semester = ? ".
        ")";

        $prep = $this->db->prepare($sql);
        $prep->bind_params("iiii", $semester, $semester,  $semester, $semester);

        return $prep->execute();

    }
}

?>
