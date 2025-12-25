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
 *  Copyright (2002-2022) Author Dany De Bontridder <danydb@noalyss.eu>
 */
global $g_user;
if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

if  ( $g_user->check_module("OTAX") == 0) {
    header('Content-type: text/xml; charset=UTF-8');
    echo Manage_Table_SQL::ajax_error(_('Accès non autorisé'))->saveXML();
    record_log("security OTAX");
    return;
}

try {
    $table=$http->request('table');
    $action=$http->request('action');
    $p_id=$http->request('p_id', "number");
    $ctl_id=$http->request('ctl');

} catch(Exception $e) {
    echo $e->getMessage();
    return;
}

$acc_other_tax=Acc_Other_Tax_MTable::build($p_id);

if ($action == "input") {
    $acc_other_tax->send_header();
    echo $acc_other_tax->ajax_input()->saveXML();
} elseif ($action=="save") {

   $acc_other_tax->send_header();
    echo $acc_other_tax->ajax_save()->saveXML();
}elseif ($action=="delete") {
    $acc_other_tax->send_header();
    echo $acc_other_tax->ajax_delete()->saveXML();

}else {
    die("error");
}