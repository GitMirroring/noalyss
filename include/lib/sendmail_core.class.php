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
    protected $content;
    protected $header;

    function __construct()
    {
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
        return '';
    }
    /**
    *@brief  create the message before sending
    */
    function compose()
    {
        $this->verify();
	    $this->header="";
	    $this->content="";

        // a random hash will be necessary to send mixed content
        $separator = md5(time());

        // carriage return type (we use a PHP end of line constant)
        $eol = PHP_EOL;

        // main header (multipart mandatory)
        $this->header = "From: " . $this->from . $eol;
        $this->header .= "MIME-Version: 1.0" . $eol;
        $this->header .= $this->add_supplemental_header();
        $this->header .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\""  ;

        // message
        $this->content .= "--" . $separator . $eol;
        $this->content .= "Content-Type: text/plain; charset=\"utf-8\"" . $eol;
        $this->content .= "Content-Transfer-Encoding: 7bit" . $eol.$eol ;
        $this->content .= $this->message . $eol ;
        if ( ! empty($this->afile ) )
        {
            // attachment
            for ($i = 0; $i < count($this->afile); $i++)
            {
                
                $file = $this->afile[$i];
                $file_size = filesize($file->full_name);
                $handle = fopen($file->full_name, "r");
                if ( $handle == false ){ 
                    \record_log("SC159 ".var_export($file,true));
                    throw new Exception ('SC159 email not send file not added'.$file->full_name);
                }
                $content = fread($handle, $file_size);
                fclose($handle);
                $content = chunk_split(base64_encode($content));
                $this->content .= "--" . $separator . $eol;
                $this->content .= "Content-Type: " . $file->type . "; name=\"" . $file->filename . "\"" . $eol;
                $this->content .= "Content-Disposition: attachment; filename=\"" . $file->filename . "\"" . $eol;
                $this->content .= "Content-Transfer-Encoding: base64" . $eol;
                $this->content.=$eol;
                $this->content .= $content . $eol ;
            }
        }
        if ( empty ($this->afile) ) $this->content.=$eol;

        $this->content .= "--" . $separator . "--";
    }

    /**
     *@brief  Send email
     * @throws Exception
     */
    function send()
    {
        try {
            $this->verify();

        } catch (Exception $e) {
            throw $e;
        }

        if (!mail($this->mailto, $this->subject, $this->content,$this->header))
        {
            throw new Exception('send failed');
        }
    }
}
