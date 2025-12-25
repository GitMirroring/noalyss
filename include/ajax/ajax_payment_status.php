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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file
 * @brief set the operation paid of unpaid
 */
if (!defined('ALLOWED'))     die('Appel direct ne sont pas permis');

global $g_user;
try {
    $ac=$http->post("ac");
    $operation_id=$http->post("operation_id");
    $state=$http->post("state");
} catch (Exception $exc) {
    record_log($exc);
    return;
}
$a_module=explode("/",$ac);

// stop if user cannot access this module
if ($g_user->check_module($a_module[count($a_module)-1]) == 0) { return ; }

// var $jr_id must be the JRN.JR_ID
$jr_id=str_replace("rd_paid", "", $operation_id);

// stop if $jr_id is not a number
if (isNumber($jr_id) == 0 ) {
    return;
}

// User can access this operation in writing
$ledger_id=$cn->get_value("select jr_def_id from jrn where jr_id=$1",[$jr_id]);

// stop if user cannot access this ledger
if ( $g_user->get_ledger_access($ledger_id) != "W") { return;}

if ( $state == "true") {
    $cn->exec_sql("update jrn set jr_rapt='paid' where jr_id=$1",[$jr_id]);
    echo 1;
} elseif( $state == "false") {
    $cn->exec_sql("update jrn set jr_rapt=null where jr_id=$1",[$jr_id]);
    echo 0;
} else {
    echo "ERROR : unknown state [$state]";
}
