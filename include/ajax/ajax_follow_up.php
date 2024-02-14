<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
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
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
/**
 * @file
 * @brief Update description on file
 */
global $http,$cn;
$op=$http->request('op');
global $g_user;

/*
 * Ajax for modifying the description , does not support ITextarea + enrich text
 *
 */
if ($op=='update_comment_followUp')
{
    $input=$http->request('input');
    $action=$http->request('ieaction', 'string', 'display');
    $d_id=$http->request('d_id', "number");

    // Build inplace input
    $inplace_description=Inplace_Edit::build($input);
    $inplace_description->set_callback("ajax_misc.php");
    $inplace_description->add_json_param("d_id", $d_id);
    $inplace_description->add_json_param("gDossier", Dossier::id());
    $inplace_description->add_json_param("op", "update_comment_followUp");
    switch ($action)
    {
        case 'display':
            echo $inplace_description->ajax_input();

            break;
        case 'ok':
            if ($g_user->check_action(VIEWDOC)==1)
            {
                $value=$http->request('value');
                $doc=new Document($cn, $d_id);
                $doc->get();
                if ($g_user->can_write_action($doc->ag_id))
                {
                    // retrieve the document
                    $doc->update_description(strip_tags($value));
                }
                $inplace_description->set_value($value);
            }
            
            echo $inplace_description->value();
            break;
        case 'cancel':
            echo $inplace_description->value();
            break;
        default:
            throw new Exception(__FILE__.':'.__LINE__.'Invalide value');
            break;
    }
    return;
}


// Modify followup 
if ($op == 'followup_comment_oneedit') {
     $input=$http->request('input');
    $action=$http->request('ieaction', 'string', 'display');
    $agc_id=$http->request('agc_id', "number");
    $ag_id=$http->request('ag_id', "number");
    global $g_user;
    // check comment is the comment of this ag_id
    $ctl_ag_id=$cn->get_value("select ag_id from action_gestion_comment where agc_id=$1",[$agc_id]);
    if ( $agc_id != -1 && $ctl_ag_id != $ag_id) {
        record_log("FLP02 ag_id [$ag_id] <> ctl_ag_id [$ctl_ag_id]");
        return;
    }
    // Build inplace input
    $inplace_description=Inplace_Edit::build($input);
    $inplace_description->set_callback("ajax_misc.php");
    
    $inplace_description->add_json_param("ag_id", $ag_id);
    $inplace_description->add_json_param("gDossier", Dossier::id());
    $inplace_description->add_json_param("op", "followup_comment_oneedit");
    switch ($action)
    {
        case 'display':
            $inplace_description->add_json_param("agc_id", $agc_id);
            echo $inplace_description->ajax_input();

            break;
        case 'ok':
            if ($g_user->check_action(VIEWDOC)==1)
            {
                $value=$http->request('value');
                if ($g_user->can_write_action($ag_id))
                {
                    // retrieve the document
                    if ( $agc_id==-1) {
                      $agc_id=  $cn->get_value("insert into action_gestion_comment(ag_id,agc_comment,agc_comment_raw,tech_user)
                                values ($1,$2,$3,$4) returning agc_id" ,[$ag_id,strip_tags($value),$value,$g_user->login]);
                    } else {
                          $cn->exec_sql("update action_gestion_comment set agc_comment=$1,tech_user=$2 ,
                                  agc_comment_raw=$3
                                where agc_id=$4
                                " ,[strip_tags($value),$g_user->login,$value,$agc_id]);
                    }
                    
                }
                $inplace_description->add_json_param("agc_id", $agc_id);
                $inplace_description->set_value($value);
            }
            echo $inplace_description->value();
            break;
        case 'cancel':
            $inplace_description->add_json_param("agc_id", $agc_id);
            echo '<pre>';
            echo $inplace_description->value();
            echo '</pre>';
            break;
        default:
            throw new Exception(__FILE__.':'.__LINE__.'Invalide value');
            break;
    }
    return;
}

/**************************************************************************
 * See list of follow-up evebt
 *************************************************************************/
if ($op =="view_followup_card")
{
    $div=$http->get("div");
    $card=new Fiche($cn,$http->get("f_id","number"));
    echo HtmlInput::title_box("Suivi ".h($card->strAttribut(ATTR_DEF_NAME)),$div);
    $query=Follow_Up::create_query($cn,["qcode"=>$card->strAttribut(ATTR_DEF_QUICKCODE)]);
    $followup=new Follow_Up($cn);
    echo $followup->view_list($query);
    echo \HtmlInput::button_close($div);
    return;
}