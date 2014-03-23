<?php
/* Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php */

function logsubstr($str, $pos, $len) {

    return substr($str, $pos - 1, $len+1);
}

class KID {
    const AGREEMENT_ID = "agreement_id";
    const ASSIGNMENT_DATE = "assignment_date";
    const AMOUNT = "amount";
    const CARD_ISSUER = "card_issuer";
    const CENTRAL_ID = "central_id";
    const DATE_SENDER = "data_sender";
    const DATE_RECEIVER = "data_receiver";
    const DAY_CODE = "day_code";
    const DEBET_ACCOUNT = "debet_account";
    const FORM_NUMBER = "form_number";
    const FREE_TEXT_MESSAGE = "free_text_message";
    const ITEM_NO = "item_no";
    const KID = "kid";
    const PART_PAYMENT_NUMBER = "part_payment_number";
    const RECORD_COUNT = "record_count";
    const SETTLEMENT_DATE = "settlement_date";
    const SHIPMENT_NUMBER = "shipment_number";
    const SIGN = "sign";
    const SUM_AMOUNT = "sum_amount";
    const TRANSACTION_COUNT = "transaction_count";
    const TRANSACTION_TYPE = "transaction_type";
    const TRANSACTION_NUMBER = "transaction_number";

    function generateKIDmod10($pre, $prelen, $post, $postlen) {
        $calc = sprintf("%0".$prelen."d%0".$postlen."d", $pre, $post);

        $sum = 0;
        $weight = 2;
        for($pos = strlen($calc) - 1; $pos >= 0; $pos--) {
            $product = $calc[$pos] * $weight;

            for($i = 0; $i < strlen($product); $i++) {
                $sum += substr($product, $i, 1);
            }

            if($weight == 2) {
                $weight = 1;
            } else {
                $weight = 2;
            }

        }

        $check = 10 - substr($sum, -1, 1);

        return $calc.$check;
    }

    function readHeader($startRecord) {
        $header = array();

        $header[KID::DATE_SENDER] = logsubstr($startRecord, 9, 16-9);
        $header[KID::SHIPMENT_NUMBER] = logsubstr($startRecord, 17, 23-17);
        $header[KID::DATE_RECEIVER] = logsubstr($startRecord, 24, 31-24);

        return $header;
    }

    function readEndRecord($header, $endRecord) {
        $header[KID::TRANSACTION_COUNT] = logsubstr($endRecord, 9, 16-9);
        $header[KID::RECORD_COUNT] = logsubstr($endRecord, 17, 24-17);
        $header[KID::SUM_AMOUNT] = logsubstr($endRecord, 25, 41-25);
        $header[KID::SETTLEMENT_DATE] = logsubstr($endRecord, 42, 47-42);
    }

    function makeTransactions($records) {
        $result = array();

        $trans = NULL;
        foreach ($records as $one) {
            $recordType = logsubstr($one, 7, 8-7);
            $transnr = logsubstr($one, 9, 15-9);

            if($recordType < 30 || $recordType > 32) {
                continue;
            }


            if(!array_key_exists($transnr, $result)) {
                $result[$transnr] = array();
            }

            $trans = &$result[$transnr];

            if($recordType == "30") {
                $this->fillAmountPost1($trans, $one);
            } else if($recordType == "31") {
                $this->fillAmountPost2($trans, $one);
            } else if($recordType == "32") {
                $this->fillAmountPost3($trans, $one);
            }
        }


        return array_values($result);
    }

    function fillAmountPost1(&$trans, $one) {
        $trans[KID::TRANSACTION_TYPE] = logsubstr($one, 5, 6-5);
        $trans[KID::TRANSACTION_NUMBER] =logsubstr($one, 9, 15-9);
        $trans[KID::SETTLEMENT_DATE] = logsubstr($one, 16, 21-16);
        $trans[KID::CENTRAL_ID] = logsubstr($one, 22, 23-22);
        $trans[KID::DAY_CODE] = logsubstr($one, 24, 25-24);
        $trans[KID::PART_PAYMENT_NUMBER] = logsubstr($one, 26, 26-26);
        $trans[KID::ITEM_NO] = logsubstr($one, 27, 31-27);
        $trans[KID::SIGN] = logsubstr($one, 32, 33-33);
        $trans[KID::AMOUNT] = logsubstr($one, 33, 49-33);
        $trans[KID::KID] = logsubstr($one, 50, 74-50);
        $trans[KID::CARD_ISSUER] = logsubstr($one, 75, 76-75);
    }

    function fillAmountPost2(&$trans, $one) {
        $trans[KID::FORM_NUMBER] = logsubstr($one, 16, 25-16);
        $trans[KID::AGREEMENT_ID] = logsubstr($one, 26, 34-26);
        $trans[KID::ASSIGNMENT_DATE] = logsubstr($one, 42, 47-42);
        $trans[KID::DEBET_ACCOUNT] = logsubstr($one, 48, 58-48);
    }

    function fillAmountPost3(&$trans, $one) {
        $trans[KID::FREE_TEXT_MESSAGE] = logsubstr($one, 16, 55-16);
    }

    function parseDataFile($fileinfo) {
        $matches = array();
        preg_match_all("/(NY.{78})/", $fileinfo, $matches);

        $records = $matches[0];

        $res = array();

        $res["header"] = $this->readHeader(array_shift($records));

        $this->readEndRecord($res["header"], array_pop($records));

        $res["transactions"] = $this->makeTransactions($records);

        return $res;
    }

}

?>