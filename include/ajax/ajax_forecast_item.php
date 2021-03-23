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
// Copyright Author Dany De Bontridder danydb@noalyss.eu
/**
 *@file
 *@brief Manage the forecast item
 */

if (!defined('ALLOWED'))     die('Appel direct ne sont pas permis');

require_once NOALYSS_INCLUDE."/class/forecast_item_mtable.class.php";
global $g_user;

if ($g_user->check_module("FORECAST")==0) die();

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
$forecast_item =  Forecast_Item_MTable::build($p_id);
$forecast_item->set_object_name($ctl_id);
if ($action=="input")
{
    $forecast_item->send_header();
    $forecast_item->set_forecast_id($http->request("forecast_id"));
    echo $forecast_item->ajax_input()->saveXML();
    return;
}elseif ($action == "save") {
    $forecast_item->send_header();
    echo $forecast_item->ajax_save()->saveXML();
    return;
} elseif ($action == "delete") {
    $forecast_item->send_header();
    echo $forecast_item->ajax_delete()->saveXML();
    return;
}