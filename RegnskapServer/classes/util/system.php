<?php
class System {
    function stdout_exec($cmd, $input='') {
        
        $proc=proc_open($cmd, array(0=>array('pipe', 'r'), 1=>array('pipe', 'w'), 2=>array('pipe', 'w')), $pipes);
        fwrite($pipes[0], $input);fclose($pipes[0]);
        $stdout=stream_get_contents($pipes[1]);fclose($pipes[1]);
        $stderr=stream_get_contents($pipes[2]);fclose($pipes[2]);
        $rtn=proc_close($proc);

        echo "STDOUT:$stdout\nSTDERR:$stderr\nReturn:$rtn\n";
    }

}

?>