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

/*!\file
 * \brief save the new predefined operation 
 * included from ajax_misc
 */

if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
require_once NOALYSS_INCLUDE.'/lib/http_input.class.php';
require_once NOALYSS_INCLUDE.'/class/operation_predef_mtable.class.php';
$http=new HttpInput();



try {
    $table=$http->request('table');
    $action=$http->request('action');
    $p_id=$http->request('p_id', "number");
    $ctl_id=$http->request('ctl');

} catch(Exception $e) {
    echo $e->getMessage();
    return;
}
if  ( $g_user->check_module("PREDOP") == 0) die();

$prd_op=new Op_predef_SQL($cn);
$prd_op->set_pk_value($p_id);
$prd_op->load();

$operation_predef_mtable=new Operation_Predef_MTable($prd_op);


$operation_predef_mtable->add_json_param("op","save_predf");
$operation_predef_mtable->set_object_name($ctl_id);
$operation_predef_mtable->set_callback("ajax_misc.php");

if ($action=="input")
{
    header('Content-type: text/xml; charset=UTF-8');
    echo $operation_predef_mtable->ajax_input()->saveXML();
    return;
}
elseif ($action == "save")
{
    $xml=$operation_predef_mtable->ajax_save();
    header('Content-type: text/xml; charset=UTF-8');
    echo $xml->saveXML();
}
elseif ($action == "delete")
{
    $xml=$operation_predef_mtable->ajax_delete();
    header('Content-type: text/xml; charset=UTF-8');
    echo $xml->saveXML();
}