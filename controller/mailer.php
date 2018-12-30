<?php
/**
 * Copyright (c) 2011-present, Kogao Software, Inc. All rights reserved.
 * <www.kogaoscript.com>
 */

class Mailer{

    var $mail;
    var $global = array(
        "host"  => "mail.gmx.net",
        "user"  => "mail@gmx.ch",
        "pass"  => "password",
        "port"  => "587",
        "from"  => "from-mail@gmx.ch",
        "name"  => "Kogao Framework",
        "live"  => "error-message@mail.com"
    );

    /**
     * SendMessage constructor.
     * @param $settings
     */

    public function __construct($settings)
    {

        require('mailer/PHPMailerAutoload.php');

        $this->mail = new PHPMailer();

        $this->mail->isSMTP();
        $this->mail->SMTPDebug      = false;
        $this->mail->Debugoutput    = 'html';
        $this->mail->Host           = $this->global['host'];
        $this->mail->Port           = $this->global['port'];
        $this->mail->SMTPAuth       = true;
        $this->mail->Username       = $this->global['user'];
        $this->mail->Password       = $this->global['pass'];

        try{

            $this->mail->isHTML(true);

            $this->mail->setFrom( $this->global['from'], $this->global['name'] );
            $this->mail->addReplyTo( $this->global['from'], $this->global['name'] );

            $this->mail->addAddress( $settings['email'], $settings['name'] );

            $this->mail->Subject = $settings['subject'];
            $this->mail->msgHTML = $settings['mail_html'];
            $this->mail->Body    = $settings['mail_html'];
            $this->mail->AltBody = $settings['mail_body'];

            if( $this->mail->send() ){

                $this->mail->ClearAllRecipients();
                $this->mail->ClearCCs();
                $this->mail->ClearBCCs();

            }else{
                //Send Standard Mail to Admin
                mail(
                    $this->global['live'], "Problem with mailing (controller/mailer.php):", $this->mail->ErrorInfo, "From: Info <".$this->global['from'].">"
                );
            }



        } catch (phpmailerException $e) {
            echo $e->errorMessage();
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }

}
