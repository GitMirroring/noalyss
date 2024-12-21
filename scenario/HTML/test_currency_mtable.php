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
 * @brief 
 * @param type $name Descriptionara
 */
//@description:Test the class Currency_MTable , ajax and javascript


$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
$_REQUEST=array_merge($_GET, $_POST);
require_once NOALYSS_INCLUDE."/class/currency_mtable.class.php";
require_once NOALYSS_INCLUDE."/lib/manage_table_sql.class.php";
require_once NOALYSS_INCLUDE.'/database/v_currency_last_value_sql.class.php';

$is_ajax=$http->request("TestAjaxFile", "string", "-");

$currency=new V_Currency_Last_Value_SQL($cn);

if ($is_ajax!="-")
{
    $p_id=$http->request('p_id', "number");
    $currency->set_pk_value($p_id);
    $currency->load();
}

$currency_table=new Currency_MTable($currency);

$currency_table->set_callback("ajax_test.php");
$currency_table->add_json_param("TestAjaxFile", __FILE__);
if ($is_ajax!="-")
{
    $table=$http->request('table');
    $action=$http->request('action');
    $p_id=$http->request('p_id', "number");
    $ctl_id=$http->request('ctl');
    /*
     * we're in ajax part
     */
    if ($action=="input")
    {
        $currency_table->set_object_name($ctl_id);
        header('Content-type: text/xml; charset=UTF-8');
        echo $currency_table->ajax_input()->saveXML();
        return;
    }
    elseif ($action=="save")
    {
        $currency_table->set_object_name($ctl_id);
        header('Content-type: text/xml; charset=UTF-8');
        echo $currency_table->ajax_save()->saveXML();
        return;
    }
    elseif ($action=="delete")
    {
        $currency_table->set_object_name($ctl_id);
        header('Content-type: text/xml; charset=UTF-8');
        echo $currency_table->ajax_delete()->saveXML();
    }
    return;
}

$currency_table->create_js_script();
$currency_table->display_table();


