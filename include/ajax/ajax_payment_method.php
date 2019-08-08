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
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief Manage the payement method, existing variable (from ajax_misc.php)
 *     - $http
 *     - $gDossier
 *     - $cn
 *  
 * @see Payment_Method_SQL
 * @see Payment_Method_MTable
 * 
 */
require_once NOALYSS_INCLUDE."/database/payment_method_sql.class.php";
require_once NOALYSS_INCLUDE."/class/payment_method_mtable.class.php";

$id=$http->request("p_id", "number", -1);
$action=$http->request("action");
$ctl=$http->request("ctl");

$payment_method_sql=new Payment_method_SQL($cn, $id);
$payment_method_mtable=new Payment_Method_MTable($payment_method_sql);

$payment_method_mtable->add_json_param("op", "payment_method");
$payment_method_mtable->set_callback("ajax_misc.php");
$payment_method_mtable->set_object_name($ctl);

// Display a form for modifying or adding an element
if ($action=="input")
{
    $payment_method_mtable->send_header();
    echo $payment_method_mtable->ajax_input()->saveXML();
    return;
}
// Save it
elseif ($action=="save")
{
    $xml=$payment_method_mtable->ajax_save();
    $payment_method_mtable->send_header();
    echo $xml->saveXML();
    return;
}
// delete the row
elseif ($action=="delete")
{
    $payment_method_mtable->send_header();
    echo $payment_method_mtable->ajax_delete()->saveXML();
    return;
}