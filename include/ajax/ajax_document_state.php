<?php

/*
 *   This file is part of NOALYSS.
 *
 *   Noalyss is free software; you can redistribute it and/or modify
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

if (!defined('ALLOWED'))     die('Appel direct ne sont pas permis');

require_once NOALYSS_INCLUDE."/class/document_state_mtable.php";
global $g_user;

if ( $g_user->check_module('CFGDOCST') == 0 ) {
    record_log("forbidden : CFGDOCST ".__FILE__);
    exit();
}


/**
 * @file
 * @brief Manage Document_State
 */
try {
    $table=$http->request('table');
    $action=$http->request('action');
    $p_id=$http->request('p_id', "number");
    $ctl_id=$http->request('ctl');

} catch(Exception $e) {
    echo $e->getMessage();
    return;
}


$document_state_sql=new Document_State_SQL($cn,$p_id);
$mtable=new Document_State_MTable($document_state_sql);
$mtable->build();
$mtable->set_object_name($ctl_id);
if ($action=="input")
{

    header('Content-type: text/xml; charset=UTF-8');
    echo $mtable->ajax_input()->saveXML();
    return;
}
elseif ($action=="save")
{
    $xml=$mtable->ajax_save();
    header('Content-type: text/xml; charset=UTF-8');
    echo $xml->saveXML();
}
elseif ($action=="delete")
{
   return;
}