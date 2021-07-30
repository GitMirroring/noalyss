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

/*!
 *  \file
 * \brief Send email for Noalyss after checking if it is possible : if cannot be sent if the limit of max email
 * is reached,
 * @see sendmail_core
 */
/*!
 *  \class Sendmail
 * \brief Send email for Noalyss after checking if it is possible : if cannot be sent if the limit of max email
 * is reached,
 * @see sendmail_core
 */

class Sendmail extends  Sendmail_Core
{
    /**
     * @brief Send the message
     * @throws Exception
     */
    function send()
    {
        if ( $this->can_send() == false )            throw new Exception(_('Email non envoyé'),EMAIL_LIMIT);

        if (!mail($this->mailto, $this->subject, $this->content,$this->header))
        {
            throw new Exception('send failed');
        }
        // Increment email amount
        $repo =new Database();
        $date=date('Ymd');
        $http=new HttpInput();
        $dossier=$http->request("gDossier","string", -1);
        $this->increment_mail($repo,$dossier,$date);
    }
    /**
     * @brief Check if email can be sent from a folder
     * @return boolean
     */
    function can_send() {
        /**
         * if send from a dossier , then  check limit of this dossier
         * otherwise send true
         */
        $http=new HttpInput();
        $dossier=$http->request("gDossier","string", -1);
        if ($dossier == -1 ) return true;

        /**
         * Compare max value in repo
         */
        $repo =new Database();
        $date=date('Ymd');
        // get max email
        $max_email = $this->get_max_email($repo,$dossier);
        //
        // This folder cannot send email
        if ($max_email == 0 ) return false;
        //
        // This folder has unlimited email
        if ($max_email == -1 ) return true;

        // get email sent today from account_repository
        $email_sent = $this->get_email_sent($repo,$dossier , $date);
        //
        if (   $email_sent >= $max_email) return false;

        return true;
    }
    /**
     *@brief  return max email the folder can send
     * @param $p_repo Database
     * @param $p_dossier_id int
     */
    function get_max_email(Database $p_repo,$p_dossier_id)
    {
        $max_email = $p_repo->get_value("select dos_email from ac_dossier where dos_id=$1",
            array($p_dossier_id));
        return $max_email;
    }
    /**
     * @brief  Return the amount of send emails for the date (YYYYMMDD)
     * @param Database $p_repo
     * @param $p_dossier_id int
     * @param $p_date string YYYYMMDD
     */
    function get_email_sent(Database $p_repo,$p_dossier_id,$p_date)
    {

        $email_sent = $p_repo->get_value ('select de_sent_email from dossier_sent_email where dos_id = $1 and de_date=$2',
            array($p_dossier_id,$p_date));
        return $email_sent;


    }
    /**
     * @brief  Add $p_amount_email to email sent
     * @param $p_repo Database
     * @param $p_dossier int id of the folder (dossier.dos_id)
     * @param $p_date string (YYYYMMDD)
     */
    function increment_mail(Database $p_repo,$p_dossier,$p_date)
    {
        if ( $p_dossier == -1) return ;
        $email_sent = $this->get_email_sent($p_repo,$p_dossier,$p_date);
        if (  $email_sent == 0 ){
            $p_repo->exec_sql("insert into public.dossier_sent_email(de_sent_email,dos_id,de_date) values($1,$2,$3)",
                array(1,$p_dossier,$p_date));
            return;
        } else {
            // update + sp_emaoun_email
            $p_repo->exec_sql("update dossier_sent_email set de_sent_email=de_sent_email+1 where dos_id=$1 and de_date=$2",
                array($p_dossier,$p_date));
        }
    }

}