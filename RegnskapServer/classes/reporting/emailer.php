<?php

/*
 * Created on Aug 9, 2007
 *
 */

class Emailer {

	private $db;

	function Emailer($db) {
		$this->db = $db;
	}

	function sendEmail($subject, $email, $body, $sender) {
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
      $msg = "This is a multi-part message in MIME format.".$eol.$eol;
      $msg.= "--".$bndp.$eol;
      $bnd = md5(time()).rand(1000,9999);
      $msg.= "Content-Type: multipart/alternative; $eol       boundary=\"".$bnd."\"".$eol.$eol;
      $msg.= "--".$bnd.$eol;
      $msg.= "Content-Type: text/plain; charset=UTF-8".$eol;
      $msg.= "Content-Transfer-Encoding: 8bit".$eol.$eol;
      $msg.= $body.$eol;
//      $msg.= "--".$bnd.$eol;
//      $msg.= "Content-Type: text/html; charset=UTF-8".$eol;
//      $msg.= "Content-Transfer-Encoding: 8-bit".$eol.$eol;
//      $msg.= $body.$eol;
      $msg .= "--".$bnd."--".$eol.$eol;
      $msg .= "--".$bndp."--";
	     
      # SEND THE EMAIL
      $sendmail_from = 0;
      ini_set($sendmail_from,$sender);  // the INI lines are to force the From Address to be used !
      $mail_sent = mail($email, $subject, $msg, $headers);
     
      ini_restore($sendmail_from);
     
      return $mail_sent;	
      }

}
?>
