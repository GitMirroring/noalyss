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
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief manage the simple report definition
 */
$http=new HttpInput();
$sa=$http->request('sa', "string", "acc_report_mtable");
if ($sa =="acc_report_mtable")
{

    try
    {
        $ctl=$http->request('ctl');
        $id=$http->request("p_id", "number");
        $action=$http->request("action");
        $form_def=$http->request("form_def", "number");
    }
    catch (Exception $ex)
    {
        die("invalid data");
    }


    $acc_report=Acc_Report_MTable::build($id, $form_def);
    $acc_report->set_object_name($ctl);

    if ($action=='input')
    {
        $acc_report->send_header();
        echo $acc_report->ajax_input()->saveXML();
        return;
    }
    elseif ($action=='save')
    {
        $acc_report->send_header();
        echo $acc_report->ajax_save()->saveXML();
        return;
    }
    elseif ($action=="delete")
    {
        $acc_report->send_header();
        echo $acc_report->ajax_delete()->saveXML();
        return;
    }
}elseif ($sa == "change_name")
{   
    try
    {
        $id=$http->request("p_id");
        $input=$http->request("input");
        $action=$http->request("ieaction","string","display");
    }
    catch (Exception $exc)
    {
        echo $exc->getMessage();
        
    }
    if ( $action == "display" ) {
        $iName=Inplace_Edit::build($input);
        $iName->set_callback("ajax_misc.php");
        $iName->add_json_param("op", "report_definition");
        $iName->add_json_param("sa", "change_name");
         $iName->add_json_param("gDossier", Dossier::id());
        $iName->add_json_param("p_id", $id);
        echo $iName->ajax_input();
    } elseif ($action == "ok") {
        $iName=Inplace_Edit::build($input);
        $iName->set_callback("ajax_misc.php");
        $iName->add_json_param("op", "report_definition");
        $iName->add_json_param("sa", "change_name");
        $iName->add_json_param("p_id", $id);
        $iName->add_json_param("gDossier", Dossier::id());
        $iName->set_value($http->request("value"));
        echo $iName->value();
      
        $cn->exec_sql("update form_definition set fr_label=$1 where fr_id=$2",[
                $http->request("value"),
                $id]);
    } elseif ($action == 'cancel'){
         $iName=Inplace_Edit::build($input);
        $iName->set_callback("ajax_misc.php");
        $iName->add_json_param("op", "report_definition");
        $iName->add_json_param("sa", "change_name");
        $iName->add_json_param("p_id", $id);
        $iName->add_json_param("gDossier", Dossier::id());
        echo $iName->value();
        
    }

    
}