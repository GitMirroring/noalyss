<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2016) Author Dany De Bontridder <dany@alchimerys.be>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');


require_once NOALYSS_INCLUDE.'/lib/itext.class.php';
require_once NOALYSS_INCLUDE.'/lib/iselect.class.php';
require_once NOALYSS_INCLUDE.'/lib/inum.class.php';
require_once NOALYSS_INCLUDE.'/lib/inplace_edit.class.php';

/**
 * @file
 * @brief Manage the security of a ledger , from CFGSEC module
 * 
 */

$n_dossier_id=Dossier::id();
//-----------------------------------------------------------------------------
// Manage the user's access to ledgers
//-----------------------------------------------------------------------------
if ($op=="ledger_access")
{
    $input=$http->request("input");
    $action=$http->request("ieaction", "string", "display");
    $user_id=$http->post("user_id", "numeric");
    $jrn_def_id=$http->post("jrn_def_id", "numeric");
    if ($action=="display")
    {
        $ie_input=Inplace_Edit::build($input);
        $ie_input->set_callback("ajax_misc.php");
        $ie_input->add_json_param("jrn_def_id", $jrn_def_id);
        $ie_input->add_json_param("op", "ledger_access");
        $ie_input->add_json_param("gDossier", $n_dossier_id);
        $ie_input->add_json_param("user_id", $user_id);
        echo $ie_input->ajax_input();
        return;
    }
    if ($action=="ok")
    {
        $value=$http->post("value");
        $ie_input=Inplace_Edit::build($input);
        $ie_input->set_callback("ajax_misc.php");
        $ie_input->add_json_param("jrn_def_id", $jrn_def_id);
        $ie_input->add_json_param("op", "ledger_access");
        $ie_input->add_json_param("gDossier", $n_dossier_id);
        $ie_input->add_json_param("user_id", $user_id);
        $ie_input->set_value($value);
        $sec_User=new User($cn, $user_id);
        $count=$cn->get_value('select count(*) from user_sec_jrn where uj_login=$1 '.
                ' and uj_jrn_id=$2', array($sec_User->login, $jrn_def_id));
        if ($count==0)
        {
            $cn->exec_sql('insert into user_sec_jrn (uj_login,uj_jrn_id,uj_priv)'.
                    ' values ($1,$2,$3)',
                    array($sec_User->login, $jrn_def_id, $value));
        }
        else
        {
            $cn->exec_sql('update user_sec_jrn set uj_priv=$1 where uj_login=$2 and uj_jrn_id=$3',
                    array($value, $sec_User->login, $jrn_def_id));
        }
        echo $ie_input->value();
        return;
    }
    if ($action=="cancel")
    {
        $ie_input=Inplace_Edit::build($input);
        $ie_input->set_callback("ajax_misc.php");
        $ie_input->add_json_param("jrn_def_id", $jrn_def_id);
        $ie_input->add_json_param("op", "ledger_access");
        $ie_input->add_json_param("gDossier", $n_dossier_id);
        $ie_input->add_json_param("user_id", $user_id);
        echo $ie_input->value();
        return;
    }
}
//-----------------------------------------------------------------------------
// Set the user's profile
//-----------------------------------------------------------------------------
if ( $op == "profile") 
{
    $input=$http->request("input");
    $action=$http->request("ieaction", "string", "display");
    $user_id=$http->post("user_id", "numeric");
    $profile_id=$http->post("profile_id","numeric");
    if ($action=="display")
    {
        $ie_input=Inplace_Edit::build($input);
        $ie_input->set_callback("ajax_misc.php");
        $ie_input->add_json_param("profile_id", $profile_id);
        $ie_input->add_json_param("op", "profile");
        $ie_input->add_json_param("gDossier", $n_dossier_id);
        $ie_input->add_json_param("user_id", $user_id);
        echo $ie_input->ajax_input();
        return;
    }
    if ($action=="ok")
    {
        $value=$http->post("value");
	// save profile
        $sec_User=new User($cn,$user_id);
	$sec_User->save_profile($value);
        $ie_input=Inplace_Edit::build($input);
        $ie_input->set_callback("ajax_misc.php");
        $ie_input->add_json_param("op", "profile");
        $ie_input->add_json_param("gDossier", $n_dossier_id);
        $ie_input->add_json_param("user_id", $user_id);
        $ie_input->set_value($value);
        
        echo $ie_input->value();
        return;
    }
    if ($action=="cancel")
    {
        $ie_input=Inplace_Edit::build($input);
        $ie_input->set_callback("ajax_misc.php");
        $ie_input->add_json_param("op", "profile");
        $ie_input->add_json_param("gDossier", $n_dossier_id);
        $ie_input->add_json_param("profile_id", $profile_id);
        $ie_input->add_json_param("user_id", $user_id);
        echo $ie_input->value();
        return;
    }
}
//------------------------------------------------------------------------------
// Update in once all the ledger access for an user
//------------------------------------------------------------------------------
if ( $op == 'ledger_access_all') {
    // Find the login
    $user_id=$http->post("user_id","numeric");
    $access=$http->post("access");
    if ( $access != "W" && $access != "X" && $access !="R") die("Invalid access");
    $sec_User=new User($cn, $user_id);
    // Insert all the existing ledgers to user_sec_jrn 
    $sql="insert into   user_sec_jrn(
			uj_jrn_id,
			uj_login,
			uj_priv
		) select  jrn_def_id,$1,'X'
		from
			jrn_def
		where
                    not exists(select 1
				from
					user_sec_jrn
				where
					uj_jrn_id = jrn_def_id
					and uj_login = $1
			)";
    $cn->exec_sql($sql,array($sec_User->login));
    $cn->exec_sql('update user_sec_jrn set uj_priv=$1 where uj_login=$2',array($access,$sec_User->login));
    return;
}