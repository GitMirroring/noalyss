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

/**
 * @file
 * @brief called from company.inc.php , add or modify extra parameter 
 */
global $g_user;

if ($g_user->check_module("COMPANY")==0) die();

require_once NOALYSS_INCLUDE.'/class/parameter_extra_mtable.class.php';

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
$parameter_extra =Parameter_Extra_MTable::build($p_id);
$parameter_extra->set_object_name($ctl_id);
if ($action=="input")
{
    $parameter_extra->send_header();
    echo $parameter_extra->ajax_input()->saveXML();
    return;
}elseif ($action == "save") {
    $parameter_extra->send_header();
    echo $parameter_extra->ajax_save()->saveXML();
    return;
} elseif ($action == "delete") {
    $parameter_extra->send_header();
    echo $parameter_extra->ajax_delete()->saveXML();
    return;
}