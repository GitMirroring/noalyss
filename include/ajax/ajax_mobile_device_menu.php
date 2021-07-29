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
 * @brief callback script for mobile_device_mtable
 * @see Mobile_Device_MTable
 */
$http=new HttpInput();
try
{
    $table=$http->request('table');
    $action=$http->request('action');
    $p_id=$http->request('p_id', "number");
    $ctl_id=$http->request('ctl');
    $profile_id=$http->request("profile_id","number");
}
catch (Exception $e)
{
    echo $e->getMessage();
    return;
}

$object= Mobile_Device_MTable::build($p_id,$profile_id);
$object->set_profile_id($profile_id);
$object->set_object_name($ctl_id);
$object->send_header();
switch ($action)
{
    case "input":
        echo $object->ajax_input()->saveXML();
        break;

    case "save":
        echo $object->ajax_save()->saveXML();
        break;

    case "delete":
        echo $object->ajax_delete()->saveXML();
        break;

    default:
        
        break;
}