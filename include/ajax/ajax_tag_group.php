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
require_once NOALYSS_INCLUDE."/class/tag_group_mtable.class.php";
/**
 * @file
 * @brief Manage the group of tags
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
$obj=new Tag_Group_SQL($cn,$p_id);
$obj_manage=new Tag_Group_MTable($obj);
$obj_manage->set_callback("ajax_misc.php");
$obj_manage->add_json_param("op", "tag_group");
$obj_manage->set_object_name($ctl_id);


if ($action=="input")
{
   
    header('Content-type: text/xml; charset=UTF-8');
    echo $obj_manage->ajax_input()->saveXML();
    return;
}
elseif ($action=="save")
{
    $xml=$obj_manage->ajax_save();
    header('Content-type: text/xml; charset=UTF-8');
    echo $xml->saveXML();
}
elseif ($action=="delete")
{
    $xml=$obj_manage->ajax_delete();
    header('Content-type: text/xml; charset=UTF-8');
    echo $xml->saveXML();
}