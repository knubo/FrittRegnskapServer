<?php

/*
 * Created on Aug 9, 2007
 *
 */

class Emailer {

    function sendPlainTextEmail($subject, $email, $body, $sender, $attachObj, $prefix="") {
        $eol="\r\n";
        $mime_boundary=md5(time());

        $subject = "=?UTF-8?B?".base64_encode($subject)."?=";

        # Common Headers
        $headers .= "From: ".$sender.$eol;
        $headers .= "Reply-To: ".$sender.$eol;
        $headers .= "Return-Path: ".$sender.$eol;    // these two to set reply address
        $headers .= "Message-ID: <".time()."-".$sender.">".$eol;
        $headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters

        $bndp = md5(time()).rand(1000,9999);
        $headers .= "Content-Type: multipart/mixed; $eol       boundary=\"".$bndp."\"".$eol.$eol;
        $msg = "This is a multi-part message in MIME format.".$eol;
        $msg .= $eol;

        $msg.= "--".$bndp.$eol;
        $msg.= "Content-Type: text/plain; charset=UTF-8".$eol;
        $msg.= "Content-Transfer-Encoding: 8bit".$eol;
        $msg.= $eol;
        $msg.= $body.$eol;

        if($attachObj) {
            foreach($attachObj as $one) {
                $one = rtrim($one);
                if($one[0] == '.') {
                    die("Illegal file name");
                }

                $fileData = chunk_split(base64_encode(file_get_contents("../../storage/".$prefix.$one)), 68, $eol);
                $fileName = $one;

                $msg.= "--".$bndp.$eol;
                $msg.= "Content-Type: application/octet-stream;name=\"$fileName\"".$eol;
                $msg.= "Content-Transfer-Encoding: base64".$eol;
                $msg.= $eol;
                $msg.= $fileData.$eol;
            }
        }


        $msg .= "--".$bndp."--$eol";

        # SEND THE EMAIL
        $sendmail_from = 0;
        ini_set("sendmail_from",$sender);  // the INI lines are to force the From Address to be used !

        $mail_sent = mail($email, $subject, $msg, $headers);

        ini_restore("sendmail_from");

        return $mail_sent;
    }

}
?>
