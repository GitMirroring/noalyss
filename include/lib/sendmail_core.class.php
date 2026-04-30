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
// Copyright Author Dany De Bontridder danydb@noalyss.eu

/**
 *@file
 *@brief API for sending email 
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
/**
 *@class Sendmail_Core
 *@brief API for sending email
 */

/**
 * Description of Sendmail
 *
 * @author dany
 */

class Sendmail_Core
{

    protected $mailto;
    protected $afile;
    protected $subject;
    protected $message;
    protected $from;
    protected $header; //!< unused
    protected $format; //!< PLAIN or HTML, message format
    
    protected $supplemental_header; //!< $supplemental_header(string) supplemental header to add
    protected $supplemental_param; //!< $supplemental_param (string)  5th parameter for mail() 
                            // for postfix, it should be "-f {$this->from}" for the Return-Path
    protected $phpmailer;   //!< PHPMailer object;
    protected $blind_copy;   //!< email of blind copy (list of emails separated by comma)
    protected $reply_to; //!< reply to 
                                   
    function __construct()
    {
        $this->format='PLAIN';
        $this->supplemental_header="";
        $this->supplemental_param=MAIL_EXTRA_PARAM;
        $this->phpmailer = new PHPMailer;
        $this->phpmailer->CharSet = PHPMailer::CHARSET_UTF8;

        //$this->phpmailer->SMTPDebug = SMTP::DEBUG_CLIENT;
        $this->phpmailer->SMTPDebug = SMTP::DEBUG_OFF;
        $this->phpmailer->isSendmail(true);
        $this->phpmailer->XMailer = "noalyss-email";
        $this->phpmailer->Host = "localhost";
        $this->phpmailer->Port = "25";
        $this->phpmailer->Username = "";
        $this->phpmailer->Password = "";
        $this->phpmailer->SMTPAuth = false;
        $this->afile=[];
        
    }
    public function getSupplemental_param()
    {
        return $this->supplemental_param;
    }

    public function setSupplemental_param($supplemental_param)
    {
        $this->supplemental_param = $supplemental_param;
        return $this;
    }
    public function getPhpmailer()
    {
        return $this->phpmailer;
    }

    public function setPhpmailer($phpmailer)
    {
        $this->phpmailer = $phpmailer;
        return $this;
    }

        public function getSupplemental_header()
    {
        return $this->supplemental_header;
    }

    public function setSupplemental_header($supplemental_header)
    {
        $this->supplemental_header = $supplemental_header;
        return $this;
    }

    public function get_format() {
        return $this->format;
    }
    /**
     * @brief format is either HTML or PLAIN
     * @param type $format HTML or PLAIN
     * @return Sendmail_Core
     */
    public function set_format($format) {
        if ( in_array($format,['PLAIN','HTML'] ) == false) {
            throw new \Exception('SC64 : unknow format ');
        }
        $this->format = $format;
        return $this;
    }

        /**
     * set the from
     * @param $p_from has the form name <info@phpcompta.eu>
     */
    function set_from($p_from)
    {
        $this->from = $p_from;
    }

    /**
     * 
     * @param $p_subject set the subject
     */
    function set_subject($p_subject)
    {
        $this->subject = $p_subject;
    }

    /**
     * set the recipient
     * @param type $p_mailto has the form name <email@email.com>
     */
    function mailto($p_mailto)
    {
        $this->mailto = $p_mailto;
    }

    /**
     * @brief body of the message (utf8)
     * @param type $p_message
     */
    function set_message($p_message)
    {
        // $this->message =wordwrap($p_message,70,"\r\n");
        $this->message =$p_message;
    }

    /**
     *@brief  Add file to the message
     * @param FileToSend $file file to add to the message
     */
    function add_file(FileToSend $file)
    {
        $this->afile[] = $file;
    }

    /**
     * @brief  verify that the message is ready to go
     * @throws Exception
     */
    function verify()
    {
        $array = explode(",", "from,subject,mailto,message");
        for ($i = 0; $i < count($array); $i++)
        {
            $name = $array[$i];
            if (trim($this->$name??"") == "")
            {
                throw new Exception( sprintf(_("%s est vide"),$name),EXC_INVALID);
            }
        }
    }
    
    /**
     * 
     * @brief Function to override if supplemental header are needed
     * @return string
     */
    function add_supplemental_header()
    {
        return $this->supplemental_header;
    }
    /**
    *@brief  create the message before sending
    */
    function compose()
    {
        $this->phpmailer->setFrom($this->from);
        
        if ( $this->format == "HTML")
        {
            $this->phpmailer->Body = $this->message;
            $this->phpmailer->AltBody = \strip_tags($this->message);
            $this->phpmailer->isHTML(true);     
        }
        else
        {
            $this->phpmailer->Body = $this->message;
            $this->phpmailer->isHTML(false);         
        }
        $this->phpmailer->Subject = $this->subject;
        $nb_file=count($this->afile);
        for ($i=0;$i<$nb_file;$i++)
        {
            $this->phpmailer->addAttachment($this->afile[$i]->full_name);
        }
        
        $a_email = explode(',', $this->mailto);
        foreach ($a_email as $item)
        {
            $this->phpmailer->addAddress($item);
        }
        $a_blind_copy=explode(",",$this->blind_copy??"");
        $nb_blind_copy=count($a_blind_copy);
        for ($i=0;$i<$nb_blind_copy;$i++)
        {
             $this->phpmailer->addBCC($a_blind_copy[$i]);
        }
        if ( ! empty ($this->reply_to)) {
            $this->phpmailer->addReplyTo($this->reply_to);
        }
        $this->phpmailer->preSend();
       
    }

    /**
     *@brief  Send email
     * @throws Exception
     */
    function send()
    {
       $this->phpmailer->send();
    }
}
