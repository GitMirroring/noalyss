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

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

// check right
if ($g_user->check_action(PARCATDOC)==0)
{

    record_log("cfgaction01 security ");
    return;
}

require_once NOALYSS_INCLUDE."/class/action_document_type_mtable.class.php";

$http=new HttpInput();


/**
 * @file
 * @brief call for document_type in follow up
 */
try
{
    $table=$http->request('table');
    $ctl_id=$http->request('ctl');
    $action=$http->request('action');
    $p_id=$http->request('p_id', "number");
}
catch (Exception $ex)
{
    echo $e->getMessage();
    return;
}

$doc_type=new Document_type_SQL($cn,$p_id);
$action_document_type=new Action_Document_Type_MTable($doc_type);

$action_document_type->set_callback("ajax_misc.php");
$action_document_type->add_json_param("op", "cfgaction");
$action_document_type->set_object_name($ctl_id);



if ($action=="input")
{
   $action_document_type->set_order(["dt_id", "dt_value","dt_prefix"]);

    header('Content-type: text/xml; charset=UTF-8');
    echo $action_document_type->ajax_input()->saveXML();
    return;
}
elseif ($action=="save")
{
    $action_document_type->set_order(["dt_id", "dt_value","dt_prefix"]);
    $xml=$action_document_type->ajax_save();
    header('Content-type: text/xml; charset=UTF-8');
    echo $xml->saveXML();
}
elseif ($action=="delete")
{
    $xml=$action_document_type->ajax_delete();
    header('Content-type: text/xml; charset=UTF-8');
    echo $xml->saveXML();
}
