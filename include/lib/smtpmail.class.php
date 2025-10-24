<?php

/*
 *   This file is part of NOALYSS.
 *
 *   NOALYSS is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   NOALYSS is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// Copyright Author Dany De Bontridder danydb@noalyss.eu 21.10.2025

namespace Noalyss;

/**
 * @file
 * @brief
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * @class SMTPMail
 * @brief
 */
class SMTPMail
{

    protected $phpmailer;

    /**
     * @param $reply_to
     * @param $blind_copy
     */
    public function __construct(Mail_Parameter $mail_parameter)
    {
        $this->phpmailer = new PHPMailer;
        $this->phpmailer->CharSet = PHPMailer::CHARSET_UTF8;

        //$this->phpmailer->SMTPDebug = SMTP::DEBUG_CONNECTION;
        $this->phpmailer->SMTPDebug = SMTP::DEBUG_OFF;
        $this->phpmailer->isSMTP(true);
        $this->phpmailer->XMailer = "noalyss-email";
        $this->phpmailer->Host = $mail_parameter->smtp_host;
        $this->phpmailer->Port = $mail_parameter->smtp_port;
        $this->phpmailer->Username = $mail_parameter->smtp_user;
        $this->phpmailer->Password = $mail_parameter->smtp_password;
        $this->phpmailer->SMTPAuth = ($mail_parameter->smtp_auth == 1) ? true : false;
        $this->phpmailer->SMTPSecure = $mail_parameter->smtp_secure;
        $this->phpmailer->setFrom($mail_parameter->smtp_from);
        $this->phpmailer->SMTPSecure = $mail_parameter->smtp_secure;
        $this->phpmailer->setFrom($mail_parameter->smtp_from);
        //  $this->phpmailer->addReplyTo($mail_parameter->smtp_replyto);
        //  $this->blind_copy = $blind_copy;
         $this->phpmailer->isHTML(true);
    }

    public function getPhpmailer()
    {
        return $this->phpmailer;
    }
    public function set_from($from)
    {
        $this->phpmailer->setFrom($from);
    }
    public function setPhpmailer($phpmailer): PHPMailer
    {
        $this->phpmailer = $phpmailer;
        return $this;
    }

    function set_format($format): SMTPMail
    {
        if (strtolower($format) == 'html')
        {
            $this->phpmailer->isHTML(true);
            return $this;
        }
        $this->phpmailer->isHTML(false);
        return $this;
    }

    /**
     * @param mixed $blind_copy
     */
    public function setBlindCopy($blind_copy): SMTPMail
    {
        $this->phpmailer->addBCC($this->blind_copy);
        return $this;
    }

    /**
     * @param mixed $reply_to
     */
    public function setReplyTo($reply_to): SMTPMail
    {
        $this->phpmailer->addReplyTo($reply_to);
        return $this;
    }

    function add_supplemental_header()
    {
        
    }

    function __toString()
    {
        
    }

    function mailto($email): SMTPMail
    {
        $a_email = explode(',', $email);
        foreach ($a_email as $item)
        {
            $this->phpmailer->addAddress($item);
        }
        return $this;
    }

    function set_subject($subject): SMTPMail
    {
        $this->phpmailer->Subject = $subject;
        return $this;
    }

    function set_message($msg): SMTPMail
    {
        $this->phpmailer->Body = $msg;
        $this->phpmailer->AltBody = \strip_tags($msg);
        return $this;
    }

    function add_file(\FileToSend $filename): SMTPMail
    {
        $this->phpmailer->addAttachment($filename->full_name);
        return $this;
    }

    function compose(): SMTPMail
    {
        $this->phpmailer->preSend();
        return $this;
    }

    function send(): SMTPMail
    {
        $this->phpmailer->send();
        return $this;
    }
}
