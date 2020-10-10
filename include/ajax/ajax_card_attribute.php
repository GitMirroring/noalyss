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
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief answer to card_attr_inc.php
 */
global $g_user;
// security
$g_user->can_request("CFGATCARD");
require_once NOALYSS_INCLUDE."/lib/inplace_switch.class.php";
require_once NOALYSS_INCLUDE.'/class/card_attribut_mtable.class.php';

$http=new HttpInput();

try
{
    $action=$http->request("action", "string");
}
catch (Exception $ex)
{
    record_log("ACA01".$ex->getMessage().$ex->getTraceAsString());
}

if ($action=="enable_search")
{
    $value=$http->request("value");
    $ad_id=$http->request("ad_id");
    $name=$http->request("name");
    if ($value==1)
    {
        $cn->exec_sql("update attr_def set ad_search_followup=0 where ad_id=$1", [$ad_id]);
        $value=0;
    }
    else
    {
        $cn->exec_sql("update attr_def set ad_search_followup=1 where ad_id=$1", [$ad_id]);
        $value=1;
    }
    $ic=new Inplace_Switch($name, $value);
    $ic->set_callback("ajax_misc.php");
    $ic->add_json_param("op", "card");
    $ic->add_json_param("gDossier", Dossier::id());
    $ic->add_json_param("op2", "attribute");
    $ic->add_json_param("action", "enable_search");
    $ic->add_json_param("ad_id", $ad_id);
    $ic->add_json_param("ctl", $ad_id);
    echo $ic->input();
}
else
{
    $obj=new Fiche_Attr($cn);
    $mtable=new Card_Attribut_MTable($obj);
    $mtable->add_json_param("op", "card");
    $mtable->add_json_param("op2", "attribute");
    $mtable->set_callback("ajax_misc.php");
    $mtable->set_object_name($http->request("ctl"));
    $ad_id=$http->request("p_id");
    $mtable->get_table()->set_parameter("id", $ad_id);
    switch ($action)
    {
        case "input":
            $mtable->set_pk($ad_id);
            if ( $ad_id < 0 ) $mtable->get_table()->set("ad_search_followup",0);
            $mtable->send_header();
            echo $mtable->ajax_input()->saveXML();

            break;
        case "save":
            $mtable->set_pk($http->request("p_id"));
            $mtable->send_header();
            echo $mtable->ajax_save()->saveXML();


            break;
        case "delete":
            $mtable->set_pk($http->request("p_id"));
            $mtable->send_header();
            echo $mtable->ajax_delete()->saveXML();
            break;
        default:
            break;
    }
}
?>
