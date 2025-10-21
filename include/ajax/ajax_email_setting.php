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


/**
 * @file
 * @brief email setting see email_setting.inc.php (C0ML)
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Noalyss\Mail_Parameter;
use Noalyss\SMTPMail;
/**
 * test email
 */
try {
    $op2=$http->request("op2");
} catch (Exception $exc) {
    \record_log($exc);
    return;
}

//------------------------------------------------
// Test SMTP Parameter
//------------------------------------------------
if ( $op2 == 'parameter_test_smtp')
{
    try {
        $mail_test=$http->get("email_test");
        $mail_parameter=new Mail_Parameter(
                    $cn
                    ,"TEST");
        $mail_parameter->from_get();
        $mail=new SMTPMail($mail_parameter);
        $e=$mail->getPhpmailer();
        $e->SMTPDebug=SMTP::DEBUG_CONNECTION;
        $mail->mailto($mail_test);
        $mail->set_subject(_("Test envoi email").date("d-m-Y H:i"));
        $mail->set_message(_("Test de votre configuration"));
        $mail->compose();
        $mail->send();

    } catch (Exception $exc) {
        echo $exc->getTraceAsString();
    }
    return;
}
//------------------------------------------------
// save smtp configuration
//------------------------------------------------
if ( $op2 == "save_config_smtp")
{
    try {
        $mail_parameter=new Mail_Parameter(
                $cn
                , MAIL_SETTING_NOALYSS
                );
        $mail_parameter->from_post();
        $mail_parameter->save();
        echo 'OK';
    } catch (Exception $exc) {
        \record_log($exc);
        echo 'NOK';
    }
    return;
}


throw new \Exception( "NOK INVALID ACTION" );