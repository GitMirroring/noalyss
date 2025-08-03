<?php

namespace Noalyss;

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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file
 * @brief  class using OTP
 * 
 */

/**
 * @brief generate OTP 
 */
use chillerlan\Authenticator\{
    Authenticator,
    AuthenticatorOptions
};
use chillerlan\Authenticator\Authenticators\AuthenticatorInterface;

class OTP {

    /**
     * @brief build a secret key and returns it
     * @return string random string of 32 
     */
    function build_secret() {
        $options = new AuthenticatorOptions;
        $options->secret_length = 32;
        $options->algorithm = AuthenticatorInterface::ALGO_SHA512;
        $options->digits=6;
        $authenticator = new Authenticator($options);
        // create a secret (stored somewhere in a *safe* place on the server. safe... hahaha jk)
        $secret = $authenticator->createSecret();
        return $secret;
        
    }
    /**
     * @brief send an email with link to the user
     * @param $user (\Noalyss_User )
     */
    function send_mail(\Noalyss_User $user) {
        $mail=new \Sendmail();
        $mail->set_from(ADMIN_WEB);
        $mail->mailto($user->getEmail());
        $mail->set_subject(_("NOALYSS : Double authentification lien pour freeOTP"));
        $noalyss_url=NOALYSS_URL;
        $uuid=guidv4();
        $id=$user->getId();
        /**
         * save in DB first
         */
       $message="Bonjour,

    Afin de pouvoir utiliser la double authentification avec freeOTP, pourriez-vous
    suivre ce lien et scanner le QRCode avec votre application android freeOTP.
               
    Ce lien ne sera actif que 24 heures.
   
   
   {$noalyss_url}/index.php?otp={$uuid}
   
   Merci d'utiliser NOALYSS
   
Cordialement,

Noalyss team
";
        try {
            $repository=new \Database();
            $otp_send_secret_sql=new \Otp_Send_Secret_SQL($repository);
            $otp_send_secret_sql->set('use_id',$id)
                    ->set('os_request',$uuid);
            $otp_send_secret_sql->save();
            $mail->set_message($message);
            $mail->compose();
            $mail->send();
            
        } catch (Exception $ex) {

        }
    }
    
}
