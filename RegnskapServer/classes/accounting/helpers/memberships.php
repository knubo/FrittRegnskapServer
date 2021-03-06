<?php


/*
 * Created on May 15, 2007
 *
 */

class Memberships {

    private $Year;
    private $YearYouth;
    private $Course;
    private $Train;
    private $Youth;
    private $Day;
    private $Memberid;
    private $Post;

    static function starts_with($string, $match) {
        if (strlen($string) < strlen($match)) {
            return false;
        }

        return substr($string, 0, strlen($match)) == $match;
    }

    static function find($perMemberid, $id) {
        if (!array_key_exists($id, $perMemberid)) {
            $perMemberid[$id] = new Memberships();
            $perMemberid[$id]->Memberid = $id;
        }
        return $perMemberid[$id];
    }

    static function parseParams($requestparams) {

        $perMemberId = array ();

        foreach (array_keys($requestparams) as $one) {

            if (Memberships :: starts_with($one, "yearyouth")) {
                Memberships :: find($perMemberId, substr($one, 9))->YearYouth = true;
            } else if (Memberships :: starts_with($one, "year")) {
                Memberships :: find($perMemberId, substr($one, 4))->Year = true;
            } else if (Memberships :: starts_with($one, "course")) {
                Memberships :: find($perMemberId, substr($one, 6))->Course = true;
            } else if (Memberships :: starts_with($one, "train")) {
                Memberships :: find($perMemberId, substr($one, 5))->Train = true;
            } else if (Memberships :: starts_with($one, "youth")) {
                Memberships :: find($perMemberId, substr($one, 5))->Youth = true;
            } else if (Memberships :: starts_with($one, "day")) {
                Memberships :: find($perMemberId, substr($one, 3))->Day = $requestparams[$one];
            } else if (Memberships :: starts_with($one, "post")) {
                Memberships :: find($perMemberId, substr($one, 4))->Post = $requestparams[$one];
            }
        }
        return array_values($perMemberId);
    }

    static function store($db, $objects) {
        $standard = new AccountStandard($db);
        $active_month = $standard->getOneValue(AccountStandard::CONST_MONTH);
        $active_year = $standard->getOneValue(AccountStandard::CONST_YEAR);
        $active_semester = $standard->getOneValue(AccountStandard::CONST_SEMESTER);

        $accPrices = new AccountMemberPrice($db);
        $prices = $accPrices->getCurrentPrices();

        $memberPrice = $prices["year"];
        $memberYouthPrice = $prices["yearyouth"];
        $coursePrice = $prices["course"];
        $trainPrice = $prices["train"];
        $youthPrice = $prices["youth"];

        if(!$memberPrice) {
            header("HTTP/1.0 514 Missing data ".json_encode($prices));
            die("missing_member_price");
        }

        if(!$memberYouthPrice) {
            header("HTTP/1.0 514 Missing data");
            die("missing_member_youth_price");
        }

        if(!$coursePrice) {
            header("HTTP/1.0 514 Missing data");
            die("missing_course_price");
        }

        if(!$trainPrice) {
            header("HTTP/1.0 514 Missing data");
            die("missing_train_price");
        }
        if(!$youthPrice) {
            header("HTTP/1.0 514 Missing data");
            die("missing_youth_price");
        }

        foreach($objects as $one) {

            $line = 0;
            if($one->day()) {
                $user = new AccountPerson($db);
                $user->load($one->memberid());

                if(!$user) {
                    throw new Exception("Failed to load user ".$one->memberid());
                }

                $line = new AccountLine($db);
                $line->setNewLatest("M: ".$user->name(), $one->day(), $active_year, $active_month);
                $line->store();
            }

            $lineId = $line ? $line->getId() : 0;

            /* Register the memberships... */
            if($one->year()) {
                $yearM = new AccountYearMembership($db, $one->memberid(), $active_year, $lineId);
                $yearM->store();
                if($lineId) {
                    $yearM->addCreditPost($lineId, $memberPrice);
                    $yearM->addDebetPost($lineId, $one->post(), $memberPrice);
                }
            }

            if($one->yearYouth()) {
                $yearMY = new AccountYearMembership($db, $one->memberid(), $active_year, $lineId, 1);
                $yearMY->store();
                if($lineId) {
                    $yearMY->addCreditPost($lineId, $memberYouthPrice);
                    $yearMY->addDebetPost($lineId, $one->post(), $memberYouthPrice);
                }
            }

            if($one->train()) {
                $trainM = new AccountSemesterMembership($db, AccountSemesterMembership::train(), $one->memberid(), $active_semester, $lineId);
                $trainM->store();
                if($lineId) {
                    $trainM->addCreditPost($lineId, $trainPrice);
                    $trainM->addDebetPost($lineId, $one->post(), $trainPrice);
                }
            }
            if($one->course()) {
                $courseM = new AccountSemesterMembership($db, AccountSemesterMembership::course(), $one->memberid(), $active_semester, $lineId);
                $courseM->store();
                if($lineId) {
                    $courseM->addCreditPost($lineId, $coursePrice);
                    $courseM->addDebetPost($lineId, $one->post(), $coursePrice);
                }
            }


            if($one->youth()) {
                $courseM = new AccountSemesterMembership($db, AccountSemesterMembership::youth(), $one->memberid(), $active_semester, $lineId);
                $courseM->store();
                if($lineId) {
                    $courseM->addCreditPost($lineId, $youthPrice);
                    $courseM->addDebetPost($lineId, $one->post(), $youthPrice);
                }
            }

        }
        return 1;
    }


    function year() {
        return $this->Year;
    }

    function yearYouth() {
        return $this->YearYouth;
    }

    function course() {
        return $this->Course;
    }

    function train() {
        return $this->Train;
    }

    function youth() {
        return $this->Youth;
    }


    function day() {
        return $this->Day;
    }
    function memberid() {
        return $this->Memberid;
    }

    function post() {
        return $this->Post;
    }

}
?>
