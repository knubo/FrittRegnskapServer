<?php

/*
 * Created on Aug 9, 2007
 *
 */

class Emailer {

    function sendEmail($subject, $email, $body, $sender, $attachObj, $prefix="", $html = 0) {
        $eol="\r\n";
        $bndp = md5(time()).rand(1000,9999);
        $bndp2 = "2$bndp";

        $subject = "=?UTF-8?B?".base64_encode($subject)."?=";

        # Common Headers
        $headers .= "From: ".$sender.$eol;
        $headers .= "Content-Type:multipart/alternative; boundary=".$bndp.$eol;
        $headers .= "Reply-To: ".$sender.$eol;
        $headers .= "Return-Path: ".$sender.$eol;    // these two to set reply address
        $headers .= "Message-ID: <".time()."-".$sender.">".$eol;
        $headers .= "X-Mailer: PHP v".phpversion().$eol;          // These two to help avoid spam-filters



        $msg.= "--".$bndp.$eol;
        $msg.= "Content-Transfer-Encoding: 8bit".$eol;
        $msg.= "Content-Type: TEXT/PLAIN; charset=UTF-8".$eol;
        $msg.= $eol;
        $msg.= $body.$eol;

        $msg.= "--".$bndp.$eol;
        $msg.= "Content-Type: multipart/MIXED;".$eol;
        $msg.= "	boundary=$bndp2".$eol.$eol;

        if($html) {
            $msg.= "--".$bndp2.$eol;
            $msg.= "Content-Type: text/html;".$eol;
            $msg.= "	charset=UTF-8".$eol;
            $msg.= $eol;
            $msg.= $html.$eol.$eol;
        }


        if($attachObj) {
            foreach($attachObj as $one) {
                $one = rtrim($one);
                if($one[0] == '.') {
                    die("Illegal file name");
                }

                $contentType = $this->findContentType("../../storage/".$prefix.$one);

                $fileData = chunk_split(base64_encode(file_get_contents("../../storage/".$prefix.$one)), 68, $eol);
                $fileName = $one;

                $msg.= "--".$bndp2.$eol;
                $msg.= "Content-Disposition: INLINE;".$eol;
                $msg.= "	filename*=\"$fileName\"".$eol;
                $msg.= "Content-Type: $contentType;".$eol;
	            $msg.= "	name*=\"$fileName\"".$eol;
                $msg.= "Content-Transfer-Encoding: base64".$eol;
                $msg.= $eol;
                $msg.= $fileData.$eol;
            }
        }


        $msg .= "--".$bndp2."--".$eol;
        $msg .= "--".$bndp."--".$eol;

        # SEND THE EMAIL
        $sendmail_from = 0;
        ini_set("sendmail_from",$sender);  // the INI lines are to force the From Address to be used !

        $mail_sent = mail($email, $subject, $msg, $headers);

        ini_restore("sendmail_from");

        return $mail_sent;
    }

    function findContentType($file) {
        $mimepath='/usr/share/file/magic'; // may differ depending on your machine
        // try /usr/share/file/magic if it doesn't work
        $mime = finfo_open(FILEINFO_MIME,$mimepath);
        if ($mime===FALSE) {
            throw new Exception('Unable to open finfo');
        }
        $filetype = finfo_file($mime,$file);
        finfo_close($mime);
        if ($filetype===FALSE) {
            throw new Exception('Unable to recognise filetype');
        }
        return $filetype;
    }
}
?>
