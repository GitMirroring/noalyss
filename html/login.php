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
// Copyright Author Dany De Bontridder danydb@aevalys.eu
require_once '../include/constant.php';

require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
MaintenanceMode("block.html");
/*! \file
 * \brief Login page
 */


// Verif if User and Pass match DB
    // if no, then redirect to the login page
$rep=new Database();

if (defined('MULTI') && MULTI == 0)
		$version = $rep->get_value('select val from repo_version');
	else
		$version = $rep->get_value('select val from version');

$http=new HttpInput();

if (  isset ($_POST["p_user"] ) )
{
    $http=new HttpInput();
    
    // clean OLD session 
    Noalyss_user::clean_session($http->post("p_user"));
    $User=new Noalyss_user($rep);
    $User->Check(false,'LOGIN');

    /*
     * Check repository version
     */

    if ($version != DBVERSIONREPO)
    {
        echo html_page_start();
        echo h1(_("Version base de donneés incorrecte"));
        echo span(_('Un instant svp'));
        echo alert(_('Version de base de données incorrectes, vous devez mettre à jour'));
        echo "<META HTTP-EQUIV=\"REFRESH\" content=\"3;url=admin-noalyss.php??action=upgrade&sb=database\">";
        exit();
    }
    if (defined('NOALYSS_CAPTCHA') && NOALYSS_CAPTCHA==true) 
    {
          include("securimage/securimage.php");
          $img = new Securimage();
          $valid = $img->check($_POST['captcha_code']);
          if ( $valid == false )
          {
          echo alert(_('Code invalide'));
          
          header("Location: ".NOALYSS_URL."/index.php");
          exit();
        }
      }
   
      // if auth method = 1 send an email with number but only for PC access
      if ($User->get_access_mode() =='PC' && $User->get_authent_method() == 1) {
        // send an email and get the uuid of the request
          $uuid=$User->send_code_otp ();
          
          // var $backurl (string url) url before being disconnected
          $backurl=(isset($_POST['backurl']))?$_POST['backurl']:"";
        
        //  display form to enter digit from email
          $User->input_otp($uuid,$backurl);
        // end 
        return;
      }

      // if auth method = 2 , only for PC access ask code from freeOTP
      if ($User->get_access_mode() =='PC' && $User->get_authent_method() ==2 ) {
          // var $backurl (string url) url before being disconnected
          $backurl=(isset($_POST['backurl']))?$_POST['backurl']:"";
        // display form to enter digit from email
          $User->input_otp(url:$backurl);
        // end 
        return;
      }  
      
      if ($User->get_access_mode()=='PC')
      {
          // retrieve the previous locationforce the nocache
        $backurl=NOALYSS_URL.'/user_login.php?v='.microtime(true);
         if ( isset ($_POST['backurl'])) {
          $backurl=urldecode($_POST['backurl']);
          // check that backurl is valid
          $backurl=preg_replace('/^.*\?/','',$backurl);
          $backurl=NOALYSS_URL."/do.php?$backurl";
        }
        header("Location: $backurl");
        exit();
      } else {
           header("Location: ".NOALYSS_URL."/mobile.php");
           exit();
      }
}
else
{
    $rep=new Database();

    /*
     * Check repository version
     */

    if ( $version != DBVERSIONREPO)
    {
          echo html_page_start();
          echo h1(_("Version base de donneés incorrecte"));
          echo span(_('Un instant svp'));
          echo alert(_('Version de base de données incorrectes, vous devez mettre à jour'));
	    echo "<META HTTP-EQUIV=\"REFRESH\" content=\"3;url=admin-noalyss.php?action=upgrade&sb=database\">";
	    exit();

      }
    $User=new Noalyss_user($rep);
      /**
       * OTP is asked and authentication method is via OTP
       */
    if (isset($_POST['to_validate']) || $User->get_authent_method() != 0) {
        
        // remove also old one 
        $rep->exec_sql("delete from otp_send_secret where os_valid_time < now()");
        try {
            $request = $http->post("rq", "string", "");
            $vrf_code = $http->post("vrf_code");

            // if code was sent by email
            if ($request != "") {
                // find the row concerning this request
                $os_id = $rep->get_value("select os_id from otp_send_secret 
                    where os_request=$1
                    and use_id=$2
                    ",
                        [$request, $User->id]);

                if ($os_id == "") {
                    echo "Désolé, votre code a expiré";
                    echo "<META HTTP-EQUIV=\"REFRESH\" content=\"0;url=index.php?v=".microtime(true)."\">";
                    return;
                }

                $otp_send_secret = new Otp_Send_Secret_SQL($rep, $os_id);
                if (
                           $vrf_code == $otp_send_secret->get('os_code')
                        || $User->check_otp($vrf_code)
                        ) {
                    $User->set_identified();
                    // var $backurl (string url) url before being disconnected
                    $backurl=NOALYSS_URL.'/user_login.php?v='.microtime(true);
                    if ( isset ($_POST['backurl'])) {
                     $backurl=urldecode($_POST['backurl']);
                     // check that backurl is valid
                     $backurl=preg_replace('/^.*\?/','',$backurl);
                     $backurl=NOALYSS_URL."/do.php?$backurl";
                   }
                     header("Location: $backurl");
                     return;
                } else {
                    // var $backurl (string url) url before being disconnected
                   $backurl=(isset($_POST['backurl']))?$_POST['backurl']:"";
                   $User->input_otp(uuid:$request,url:$backurl);
                   return;
                }
            } else {
                // connection avec freeOTP / Google Authenticator
                if ($User->check_otp($vrf_code) == true) {
                    $User->set_identified();
                    // var $backurl (string url) url before being disconnected
                    $backurl=NOALYSS_URL.'/user_login.php?v='.microtime(true);
                   if ( isset ($_POST['backurl'])) {
                    // var $backurl (string url) url before being disconnected
                     $backurl=urldecode($_POST['backurl']);
                     // check that backurl is valid
                     
                     $backurl=preg_replace('/^.*\?/','',$backurl);
                     $backurl=NOALYSS_URL."/do.php?$backurl";
                   }
                   header("Location: $backurl");
                   return;
                } else {
                     // var $backurl (string url) url before being disconnected
                    $backurl=NOALYSS_URL.'/user_login.php?v='.microtime(true);
                    $User->input_otp(url:$backurl);
                    return;
                }
            }
        } catch (Exception $exc) {
            
            record_log($exc);
        }
    }
    $User->Check();

    echo "<META HTTP-EQUIV=\"REFRESH\" content=\"0;url=user_login.php?v=".microtime(true)."\">";
}
html_page_stop();
?>
