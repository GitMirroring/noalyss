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
 * @brief 
 */
global $g_user;
$g_user->can_request('CFGTAG');

/*
 * Received parameter
 * gDossier
 * act : action to perform
 * tag_group : tag_group.tg_id
 * tag=tags.t_id
 * 
 */
try
{
    $act=$http->request("act");
}
catch (Exception $e)
{

    record_log(__FILE__.$e->getMessage()." ".$e->getTraceAsString());
    echo $e->getMessage()." ".$e->getTraceAsString();
}
$obj=new Tag_Group_SQL($cn);
$obj_manage=new Tag_Group_MTable($obj);
$obj_manage->set_callback("ajax_misc.php");
$obj_manage->add_json_param("op", "tag_group");
if ($act=="add")
{
    $tag_group_id=$http->request("tag_group");
    $tag=$http->request("tag");
    $obj_manage->set_pk($tag_group_id);
    // add tag to group tag
    // protect against duplicate
    if ($cn->get_value("select count (*) from  jnt_tag_group_tag where tag_id = $1 and tag_group_id = $2",
                    [$tag, $tag_group_id])==0)
    {
        $cn->exec_sql("insert into jnt_tag_group_tag(tag_id,tag_group_id) values ($1,$2)", [$tag, $tag_group_id]);
    }
}
elseif ($act=="remove")
{
    $tag=$http->request("jt_tag");
    $tag_group_id=$cn->get_value("select tag_group_id from jnt_tag_group_tag where jt_id = $1",[$tag]);
    $obj_manage->set_pk($tag_group_id);
    // remove tag from tag_group
    $cn->exec_sql("delete from jnt_tag_group_tag where jt_id=$1", [$tag]);
}
$obj_manage->input_tag();
