<?php


class Luhn {

    public static function generateDigit($s) {
        $digit = 10 - Luhn::doLuhn($s, true) % 10;
        return "" . $digit;
    }

    private static function doLuhn($s, $e) {
        $evenPosition = $e;
        $sum = 0;
        for ($i = strlen($s) - 1; $i >= 0; $i--) {
            $n = substr($s, $i, $i + 1);
            if ($evenPosition) {
                $n *= 2;
                if ($n > 9) {
                    $n = ($n % 10) + 1;
                }
            }
            $sum += $n;
            $evenPosition = !$evenPosition;
        }

        return $sum;
    }

}
