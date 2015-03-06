<?php
    require_once('PHPMailer-master/class.phpmailer.php'); //or select the proper destination for this file if your page is in some   //other folder
    error_reporting(E_ALL);


    date_default_timezone_set('Europe/Budapest');
    ini_set('date.timezone', 'Europe/Budapest');  

    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Host = "10.180.12.71";
    $mail->Port = "25";

    $mail->CharSet = 'utf-8';
    
    $mail -> From = "sorfigyelo@katek.hu";
    $rec1 = "hu15203@katek.hu"; //receiver email addresses to which u want to send the mail.
    $mail->AddAddress($rec1);
    $mail->IsHTML(true);

    $mail->AddEmbeddedImage('katek.jpg', 'my-image', 'attachment', 'base64', 'image/jpeg');

    $mail->Subject = "Átállás Figyelmeztető: ".date('Y-m-d H:i');
        
    $body = '<center><img src="cid:my-image" alt="Katek" /></center>';
    $body.= '<h1 style="text-align: center;">Figyelem!</h1>';
    $body.= '<hr/>';
    $body.= "<div style='text-align:center;'>";
    
    $body .= "<p>Az <b>SMT LINE {$line}</b> soron, jelenleg gyártás alatt lévő: <b>{$db}</b> db - <b>{$product_name}</b> ({$product_code}) termék hamarosan elkészül!</p>";
    $body .= "<h2 style='color:green'>Töltse be a következő terméket!</h2>";
    
    $body .= "</div>";

    $mail->Body = $body;
    $mail->Send();
    /*if(!$mail->Send()) {
        echo 'Message was not sent!<br/>';
        echo 'Mailer error: ' . $mail->ErrorInfo;
    }else{
        echo 'Az üzenet sikeresen elküldve!';
    }*/
?>
