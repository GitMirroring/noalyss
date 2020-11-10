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
require_once NOALYSS_INCLUDE."/class/follow_up_other_concerned.class.php";
/**
 * @file
 * @brief save option of card into action_person_option
 */

try {
    $ap_value=$http->post("ap_value","array",[]);
    $ap_id=$http->post("ap_id","array",[]);
    $cor_id=$http->post("cor_id","array",[]);
    $fiche_id=$http->post("f_id");
    $ag_id=$http->post("ag_id");
    $action_person_id=$http->post("action_person_id");
} catch (Exception $ex) {
     record_log("ASCO01".$ex->getMessage().$ex->getTraceAsString());
     return;
}

/// If cannot write we stop it
if ( ! $g_user->can_write_action($ag_id)) {
    record_log("ASCO02 Security ");
}

$nb=count($ap_value);
// nothing to save
if ($nb == 0) return;

for ($i=0;$i<$nb; $i++) {

        $cn->exec_sql("UPDATE public.action_person_option SET ap_value=$1 WHERE ap_id=$2",
                [$ap_value[$i],$ap_id[$i]]);
}
$follow=new Follow_Up_Other_Concerned($cn,$ag_id);
$aColumn = $follow->get_option();
echo $follow->display_row($fiche_id, 0, $aColumn);