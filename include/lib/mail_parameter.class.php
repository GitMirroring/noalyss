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
// Copyright Author Dany De Bontridder danydb@noalyss.eu 21.10.2025

/**
 * @file
 * @brief configuration email and test PHPMAILER
 */
/*
 * CREATE TABLE public.parm_mail_server (
  pe_id int4 GENERATED ALWAYS AS IDENTITY( INCREMENT BY 1 MINVALUE 1 MAXVALUE 2147483647 START 1 CACHE 1 NO CYCLE) NOT NULL, -- pk
  pe_name varchar NULL, -- Config. name
  pe_parameter text NOT NULL, -- key for json
  pe_value text NULL, -- value of the key
  CONSTRAINT param_email_pk PRIMARY KEY (pe_id),
  CONSTRAINT parm_email_unique UNIQUE (pe_name, pe_parameter)
  );
  COMMENT ON TABLE public.parm_mail_server IS 'Parameters for email server';

  -- Column comments

  COMMENT ON COLUMN public.parm_mail_server.pe_id IS 'pk';
  COMMENT ON COLUMN public.parm_mail_server.pe_name IS 'Config. name';
  COMMENT ON COLUMN public.parm_mail_server.pe_parameter IS 'key for json';
  COMMENT ON COLUMN public.parm_mail_server.pe_value IS 'value of the key';
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * @class
 * @brief configuration email and test PHPMAILER.
 * Property : 
 *  - $cn Database conx
 *  - $name  name of the SMTP conx
 *  - $a_value array 
 *     - smtp_user' username 
  - 'smtp_password' password
  - 'smtp_host'    host
  - 'smtp_port'    port
  - 'smtp_replyto' email address to reply
  - 'smtp_from'    email address sender
  - 'smtp_auth'    smtp authorisation (always 1)
  - 'smtp_type'    smtp 
  - 'smtp_secure'  kind of encryption PHPMailer::ENCRYPTION_STARTTLS, or PHPMailer::ENCRYPTION_SMTPS.
  - 'smtp_auth_type'SMTP authentication type. Options are CRAM-MD5, LOGIN, PLAIN, XOAUTH2.
 *  
 */
class Mail_Parameter
{

    const PARAMETER = [
        'smtp_user'
        , 'smtp_password'
        , 'smtp_host'
        , 'smtp_port'
        , 'smtp_replyto'
        , 'smtp_from'
        , 'smtp_auth'
        , 'smtp_type'
        , 'smtp_secure'
        , 'smtp_auth_type'
    ];

    protected $a_value; //!< $a_value array of PARAMETER=>Value

//    private $char_set; //!<  string character set of message

    function __construct(
            protected \Database $cn
            , protected $name
    )
    {
        
        $this->load();
    }

    /**
     * @brief set the value in the array $a_value thkx the key
     * @param $key (string) key must be in Mail_Parameter::PARAMETER
     * @param $value (string) value
     * @return Mail_Parameter
     * @throws \Exception if the key doesn't exist
     */
    function __set($key, $value)
    {
        if (in_array($key, Mail_Parameter::PARAMETER) == false)
        {
            throw new \Exception("MP72 : unknown $key");
        }
        $this->a_value[$key] = $value;
        return $this;
    }

    /**
     * @brief get the value in the array $a_value thkx the key
     * @param $key (string) key must be in Mail_Parameter::PARAMETER
     * @param $value (string) value
     * @return a string or null if not found
     * @throws \Exception if the key doesn't exist
     */
    function __get($key)
    {
        if (in_array($key, Mail_Parameter::PARAMETER) == false)
        {
            throw new \Exception("MP79 : unknown $key");
        }
        if (isset($this->a_value[$key]))
        {
            return $this->a_value[$key];
        }
        return null;
    }

    /**
     * @brief save into the database
     */
    function save()
    {
        foreach (self::PARAMETER as $key)
        {
            $id = $this->cn->get_value("select pe_id from parm_mail_server 
                where pe_parameter=$1
                and pe_name=$2",
                    [$key, $this->name]);
            if ($id == "")
            {
                $record = new \Parm_Mail_Server_SQL($this->cn);
                $record->set("pe_name", $this->name);
                $record->set("pe_parameter", $key);
                $record->set("pe_value", $this->a_value[$key]);
                $record->insert();
            } else
            {
                $record = new \Parm_Mail_Server_SQL($this->cn, $id);
                $record->set("pe_value", $this->a_value[$key]);
                $record->update();
            }
        }
    }

    /**
     * @brief load from the database
     */
    function load()
    {
        $a_value = array_column(
                $this->cn->get_array("select pe_parameter , pe_value from 
                        public.parm_mail_server 
                        where 
                        pe_name=$1"
                        , [$this->name])
                , "pe_value","pe_parameter"
        );
        
        foreach (self::PARAMETER as $key)
        {
            $this->a_value[$key] = $a_value[$key] ?? "";
        }
    }

    function get($key)
    {
        return $this->$key;
    }

    function set($key, $value): Mail_Parameter
    {
        $this->$key = $value;
        return $this;
    }

    /**
     * @brief display a form to input the parameter
     * 
     */
    function input()
    {
        
        include NOALYSS_TEMPLATE . "/mail_parameter-input.php";
    }
    /**
     * @brief retrieve information from GET
     */
    function from_get()
    {
        $http = new \HttpInput();
        $this->smtp_user = $http->get('smtp_user');
        $this->smtp_password = $http->get('smtp_password');
        $this->smtp_host = $http->get('smtp_host');
        $this->smtp_port = $http->get('smtp_port');
        $this->smtp_replyto = $http->get('smtp_replyto');
        $this->smtp_from = $http->get('smtp_from');
        $this->smtp_type= $http->get('smtp_type');
        $this->smtp_secure = $http->get('smtp_secure');
        $this->smtp_auth_type = $http->get('smtp_auth_type');
        $this->smtp_auth=1;
        
    }
    /**
     * @brief retrieve information from POST
     */
    function from_post()
    {
        $http = new \HttpInput();
        $this->smtp_user = $http->post('smtp_user');
        $this->smtp_password = $http->post('smtp_password');
        $this->smtp_host = $http->post('smtp_host');
        $this->smtp_port = $http->post('smtp_port');
        $this->smtp_replyto = $http->post('smtp_replyto');
        $this->smtp_from = $http->post('smtp_from');
        $this->smtp_type= $http->post('smtp_type');
        $this->smtp_secure = $http->post('smtp_secure');
        $this->smtp_auth_type = $http->post('smtp_auth_type');
        $this->smtp_auth=1;
        
    }
    public static function Factory(\Database $cnx,$reply_to="", $blind_copy="")
    {
        $mail_parameter=new Mail_Parameter($cnx,MAIL_SETTING_NOALYSS);
        if ( $mail_parameter->smtp_type=="sendmail") {
            return new \Sendmail($mail_parameter->smtp_replyto,$mail_parameter->smtp_replyto);
        } elseif ($mail_parameter->smtp_type=="smtp") {
            $phpmail= new SMTPMail($mail_parameter);
            return $phpmail;
        }
        
        throw new \Exception("MP212 MAIL NOT CONFIGURED",212);
        
    }
}
