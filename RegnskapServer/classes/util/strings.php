<?php
class Strings {

    static function file_get_contents_utf8($fn) {
        $content = file_get_contents($fn);
        return mb_convert_encoding($content, 'ISO-8859-1', mb_detect_encoding($content, 'ISO-8859-1, UTF-8', true));
    }

    static function whitelist($dirty_data) {

        $dirty_array = str_split($dirty_data);
        $clean_data = "";
        foreach ($dirty_array as $char) {
            $clean_char = preg_replace("/[^a-zA-Z0-9_\-\.]/", "", $char);
            $clean_data = $clean_data . $clean_char;
        }

        return $clean_data;
    }

    static function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    static function createSecret($length = 40) {
        $secret = "";
        for ($i=0; $i < $length; $i++) {
            $secret.= chr(mt_rand(97, 122));
        }

        return $secret;
    }

    public static function money($clientMoney) {
        return preg_replace("/,/", "", $clientMoney);
    }

}
?>
