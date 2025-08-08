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
    
    private $authenticator;
    
    function __construct() {
        $options = new AuthenticatorOptions;
        $options->secret_length = 32;
        $options->algorithm = AuthenticatorInterface::ALGO_SHA512;
        $options->digits=6;
        $this->authenticator = new Authenticator($options);
    }
    /**
     * @brief build a secret key and returns it
     * @return string random string of 32 char
     */
    function build_secret() {
 
        $secret = $this->authenticator->createSecret();
        return $secret;
        
    }
   
    /**
     * @brief compute a code for auth. for the user passed in parameter
     * @param $secret (string)  secret stored in AC_USER
     */
    function compute_code($secret)
    {
      
        $this->authenticator->setSecret($secret);
        return $this->authenticator->code();
    }
    public function get_authenticator() {
        return $this->authenticator;
    }

    public function set_authenticator($authenticator) {
        $this->authenticator = $authenticator;
        return $this;
    }


}
