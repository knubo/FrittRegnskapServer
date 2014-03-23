<?php

class AccountKID {

    private $db;

    function AccountKID($db) {
        if (!$db) {
            $db = new DB();
        }

        $this->db = $db;
    }

    function unhandled() {
        $prep = $this->db->prepare("select K.*,P.id as personId, P.firstname,P.lastname,M.memberid, C.memberid as course, T.memberid as train, Y.memberid as youth from "
                                   . AppConfig::pre() . "kid K " .
                                   "left join " . AppConfig::pre() . "person P on (P.id = SUBSTRING(kid, 5,5)) " .

                                   " left join " . AppConfig::pre() . "year_membership M ON " .
                                   "(M.memberid = SUBSTRING(kid, 5,5) and year = (select value from " . AppConfig::pre() . "standard S where S.id = 'STD_YEAR')) " .

                                   " left join " . AppConfig::pre() . "course_membership C ON " .
                                   "(C.memberid = SUBSTRING(kid, 5,5) and C.semester = (select value from " . AppConfig::pre() . "standard S where S.id = 'STD_SEMESTER')) " .

                                   " left join " . AppConfig::pre() . "train_membership T ON " .
                                   "(T.memberid = SUBSTRING(kid, 5,5) and T.semester = (select value from " . AppConfig::pre() . "standard S where S.id = 'STD_SEMESTER')) " .

                                   " left join " . AppConfig::pre() . "youth_membership Y ON " .
                                   "(Y.memberid = SUBSTRING(kid, 5,5) and Y.semester = (select value from " . AppConfig::pre() . "standard S where S.id = 'STD_SEMESTER')) " .

                                   "where kid_status = 0 and " .
                                   "settlement_date >= str_to_date(CONCAT((SELECT value from " . AppConfig::pre() . "standard S where S.id = 'STD_YEAR')," .
                                   "'-'," .
                                   "(SELECT value from " . AppConfig::pre() . "standard S where S.id = 'STD_MONTH')," .
                                   "'-1'), '%Y-%m-%d') " .
                                   "and settlement_date <" .
                                   "date_add(str_to_date(CONCAT((SELECT value from " . AppConfig::pre() . "standard S where S.id = 'STD_YEAR')," .
                                   "'-'," .
                                   "(SELECT value from " . AppConfig::pre() . "standard S where S.id = 'STD_MONTH')," .
                                   "'-1'), '%Y-%m-%d'), interval 1 MONTH)"
        );

        return $prep->execute();
    }

    function save($kids, $editedByPerson) {
        $accStd = new AccountStandard($this->db);
        $std = $accStd->getValues(array(AccountStandard::CONST_YEAR, AccountStandard::CONST_MONTH, AccountStandard::CONST_SEMESTER, AccountStandard::CONST_KID_BANK_ACCOUNT));

        foreach ($kids as $kid) {
            $accLine = new AccountLine($this->db);

            $desc = $kid->description;

            $accLine->setLatestWithDate($desc, $kid->settlement_date, $std[AccountStandard::CONST_YEAR], $std[AccountStandard::CONST_MONTH], $editByPerson);
            $accLine->store();

            $lineId = $accLine->Id;

            $kidPosts = $kid->accounting;
            foreach ($kidPosts as $post => $value) {
                if (substr($post, -4) == "_tip") {
                    continue;
                }

                $accLine->addPostSingleAmount($lineId,
                                              '-1',
                                              $post,
                                              $value);
            }

            $accLine->addPostSingleAmount($lineId, '1', $std[AccountStandard::CONST_KID_BANK_ACCOUNT], $kid->amount);

            $memberships = $kid->payments;

            foreach ($memberships as $memb) {
                switch ($memb) {
                    case "year":
                        $yearM = new AccountYearMembership($this->db, $kid->personId, $std[AccountStandard::CONST_YEAR], $lineId);
                        $yearM->store();
                        break;
                    case "yearyouth":
                        $yearM = new AccountYearMembership($this->db, $kid->personId, $std[AccountStandard::CONST_YEAR], $lineId, 1);
                        $yearM->store();
                        break;
                    default:
                        $memb = new AccountSemesterMembership($this->db, $memb, $kid->personId, $std[AccountStandard::CONST_SEMESTER], $lineId);
                        $memb->store();
                        break;
                }
            }

            $prep = $this->db->prepare("update " . AppConfig::pre() . "kid set kid_status = ?, regn_line=? where id =?");
            $prep->bind_params("iii", array_key_exists("edited", $kid) ? 2 : 1, $lineId, $kid->id);
            $prep->execute();

            return 1;
        }
    }

    function register($data, $personId) {
        $kids = json_decode($data);

        $this->db->begin();
        try {
            $this->save($kids, $personId);
            $this->db->commit();
        } catch (exception $e) {
            $this->db->rollback();
        }

        return 1;
    }

    public function listKID($masterRecord, $data) {
        $searchWrap = $this->db->search("select K.*,P.firstname, P.lastname from " . AppConfig::pre() .
                                        "kid K left join " .
                                        AppConfig::pre() . "person P on (P.id = SUBSTRING(kid, 5,5))",
                                        "order by id");

        if ($data["member"] > 0) {
            $kidTool = new KID();
            $kid = $kidTool->generateKIDmod10($masterRecord[id], 4, $data["member"], 5);
            $searchWrap->addAndParam("s", "kid", $kid);
        }

        if ($data["fromDate"]) {
            $date = new eZDate();
            $date->setDate($data["fromDate"]);
            $searchWrap->addAndQuery("s", $date->mySQLDate(), "settlement_date >= ?");
        }

        if ($data["toDate"]) {
            $date = new eZDate();
            $date->setDate($data["toDate"]);
            $searchWrap->addAndQuery("s", $date->mySQLDate(), "settlement_date <= ?");
        }

        $searchWrap->addAndParam("i", "kid_status", $data["status"]);


        return $searchWrap->execute();
    }

    public function unhandledForMonth($year, $month) {
        $startDate = new eZDate($year, $month, 1);
        $endDate = new eZDate($year, $month, $startDate->daysInMonth());

        $prep = $this->db->prepare("select count(*) as kids from " . AppConfig::pre() ."kid where settlement_date >= ? and settlement_date <=? and kid_status = 0");
        $prep->bind_params("ss", $startDate->mySQLDate(), $endDate->mySQLDate());

        $result = $prep->execute();
        return array_shift($result);
    }

}


?>