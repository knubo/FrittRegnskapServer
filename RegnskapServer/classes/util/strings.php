<?php
class Strings {

    function whitelist($dirty_data) {

        $dirty_array = str_split($dirty_data);
        $clean_data = "";
        foreach ($dirty_array as $char) {
            $clean_char = preg_replace("/[^a-zA-Z0-9_\-\.]/", "", $char);
            $clean_data = $clean_data . $clean_char;
        }

        return $clean_data;
    }

    function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
?>
