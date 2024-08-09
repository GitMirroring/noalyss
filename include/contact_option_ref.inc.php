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
echo '<div class="content">';
/**
 * @file
 * @brief add option for contact
 * @see contact_option_list
 * @see action_document_type_mtable::input()
 */
$http=new HttpInput();
try
{
    $action=$http->request("sa", "string", "list");
}
catch (Exception $ex)
{
    record_log(__FILE__." no p_id");
    return;
}
$cn=Dossier::connect();
echo    '<p class="info">'.
        '<a href="https://wiki.noalyss.eu/doku.php?id=menu:cfgaction" target="_blank">'.
        _("Aide : voir C0ACT").
        '</a>'.
        '</p>';
// document_option id from document_option where do_code='contact_multiple' and document_type_id=p_id
$obj=new Contact_option_ref_SQL($cn);

$contact_mtable=new Contact_Option_Ref_MTable($obj);

$contact_mtable->set_callback("ajax_misc.php");
$contact_mtable->set_object_name("s_contact_option");
$contact_mtable->set_dialog_box("db_contact_option");
$contact_mtable->add_json_param("op", "contact_option_list");

$contact_mtable->create_js_script();
$contact_mtable->display_table();


echo '</div>';