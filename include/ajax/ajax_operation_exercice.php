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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 6/01/24
/*! 
 * \file
 * \brief display the possible value of exercice
 */
global $http, $g_user;
if ($g_user->check_module("OPCL") == 0) throw new \Exception(EXC_FORBIDDEN);

//-------------------------------------------------------------------------------------------
// Update the periode of the selected folder
//-------------------------------------------------------------------------------------------
if ($op == "operation_exercice+update_periode") {

    try {
        $folder_id = $http->get("folder", "number");
        if ($g_user->check_dossier($folder_id, true) == 'X') throw new \Exception(EXC_FORBIDDEN);

    } catch (\Exception $e) {

        return;
    }

    $conx = new Database($folder_id);
    $periode = $conx->make_array("select distinct p_exercice,p_exercice from parm_periode order by p_exercice desc");
    $sExercice = new ISelect("exercice");
    $sExercice->id = "select_exercice_id";
    $sExercice->value = $periode;
    echo $sExercice->input();
    return;
}
//-------------------------------------------------------------------------------------------
// Display a detail operation_exercice to modify or add id
//-------------------------------------------------------------------------------------------
if ($op == "operation_exercice+modify_row") {
    try {
        $row_id = $http->post("row_id", "number", -1);
        $oe_id = $http->post("oe_id", "number");
    } catch (\Exception $e) {
        echo $e->getMessage();
        return;
    }
    $operation_detail_sql = new Operation_Exercice_Detail_SQL($cn, $row_id);
    $operation_detail_sql->oe_id = $oe_id;
    if ($row_id == -1) $operation_detail_sql->oed_debit = 'f';
    Operation_Exercice::input_row($operation_detail_sql);

    return;
}
//-------------------------------------------------------------------------------------------
// save a row
//-------------------------------------------------------------------------------------------
if ($op == "operation_exercice+save_row") {
    try {
        $row_id = $http->post("row_id", "number");
        $oe_id = $http->post("oe_id", "number");
        $oe_poste = $http->post("oe_poste", "text");
        $qcode = $http->post("qcode", "text");
        $label = $http->post("label", "text");
        $amount = $http->post("amount", "number");
        $debit = $http->post("debit", "text", 'f');
    } catch (\Exception $e) {
        echo $e->getMessage();
        return;
    }
    $operation_detail_sql = new Operation_Exercice_Detail_SQL($cn, $row_id);

    $operation_detail_sql->setp("oed_poste", $oe_poste)
        ->setp("oed_qcode", $qcode)
        ->setp("oed_label", $label)
        ->setp("oed_amount", $amount)
        ->setp("oe_id", $oe_id)
        ->setp("oed_debit", $debit);
    try {
        $operation_detail_sql->save();
        $data = $operation_detail_sql->to_array();

        ob_start();
        $operation_exercice = new Operation_Exercice();
        if ($row_id == -1) {
            $operation_exercice->display_row($data, true);
        } else {

            $operation_exercice->display_row($data, false);
        }
        $str = ob_get_contents();
        ob_end_clean();

        $answer = [];
        $answer["status"] = "OK";
        $answer['row_id'] = $operation_detail_sql->oed_id;
        $answer['oe_id'] = $operation_detail_sql->oe_id;
        $answer['content'] = $str;
        echo json_response($answer);
    } catch (\Exception $e) {
        echo $e->getMessage();
        return;
    }
}
//-------------------------------------------------------------------------------------------
// Compute the balance, we receive only the OED_ID , then we must find out what was the complete
// OPERATION_EXERCICE
//-------------------------------------------------------------------------------------------
if ($op == "operation_exercice+display_total") {
    try {
        $row_id = $http->get("row_id", "number");
        $operation_detail_sql = new Operation_Exercice_Detail_SQL($cn, $row_id);
        if ($operation_detail_sql->getp("oe_id") == "") {
            throw new Exception("AOE-113:not existing line");
        }
        $operation_exercice = new Operation_Exercice($operation_detail_sql->oe_id);
        $operation_exercice->display_total(false);

    } catch (\Exception $e) {
        echo $e->getMessage();
        return;
    }
}
//-------------------------------------------------------------------------------------------
//delete a row  OPERATION_EXERCICE
//-------------------------------------------------------------------------------------------
if ($op == "operation_exercice+delete_row") {
    try {
        $row_id = $http->get("row_id", "number");
        $operation_detail_sql = new Operation_Exercice_Detail_SQL($cn, $row_id);
        $oe_id = $operation_detail_sql->oe_id;
        $other_row = $cn->get_value("select oed_id from operation_exercice_detail where oe_id=$1 limit 1 ",
            [$oe_id]);

        $operation_detail_sql->delete();
        echo json_response(array("status" => "OK", "row_id" => $other_row));
    } catch (\Exception $e) {
        echo $e->getMessage();
        return;
    }
}
//-------------------------------------------------------------------------------------------
// Change the date
//-------------------------------------------------------------------------------------------
if ($op == "operation_exercice+date") {
    try {
        $oe_id = $http->request("oe_id", "number");
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
    $input = $http->request("input");
    $action = $http->request("ieaction", "string", "display");
    $ajax_date = Inplace_Edit::build($input);
    $ajax_date->add_json_param("op", "operation_exercice+date");
    $ajax_date->add_json_param("gDossier", Dossier::id());
    $ajax_date->add_json_param("oe_id", $oe_id);
    $ajax_date->set_callback("ajax_misc.php");

    if ($action == "display") {

        echo $ajax_date->ajax_input();
    }
    if ($action == "ok") {
        $value = $http->request("value");
        $cn->exec_sql("update operation_exercice set oe_date=to_date($1,'DD.MM.YYYY') where oe_id=$2", [$value, $oe_id]);
        $ajax_date->set_value($value);
        echo $ajax_date->value();
    }
    if ($action == "cancel") {

        echo $ajax_date->value();
    }
    return;
}
//-------------------------------------------------------------------------------------------
// Change the text
//-------------------------------------------------------------------------------------------
if ($op == "operation_exercice+text") {
    try {
        $oe_id = $http->request("oe_id", "number");
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
    $input = $http->request("input");
    $action = $http->request("ieaction", "string", "display");
    $ajax_text = Inplace_Edit::build($input);
    $ajax_text->add_json_param("op", "operation_exercice+text");
    $ajax_text->add_json_param("gDossier", Dossier::id());
    $ajax_text->add_json_param("oe_id", $oe_id);
    $ajax_text->set_callback("ajax_misc.php");

    if ($action == "display") {

        echo $ajax_text->ajax_input();
    }
    if ($action == "ok") {
        $value = $http->request("value");
        $cn->exec_sql("update operation_exercice set oe_text=$1 where oe_id=$2", [$value, $oe_id]);
        $ajax_text->set_value($value);
        echo $ajax_text->value();
    }
    if ($action == "cancel") {

        echo $ajax_text->value();
    }
    return;
}
//-------------------------------------------------------------------------------------------
// Check and transfer to accountancy if OK
//-------------------------------------------------------------------------------------------
if ($op == "operation_exercice+transfer") {
    try {
        $operation_exercice_id = $http->request("oe_id", "number");
        $ledger_id = $http->request('p_jrn', "number");
        if ($g_user->check_jrn($ledger_id) == "X") throw new \Exception (EXC_FORBIDDEN);
    } catch (\Exception $e) {
        echo $e->getMessage();
        return;
    }
    // first , check that everything is ok
    $operation_exercice = new Operation_Exercice($operation_exercice_id);

    global $oe_result, $oe_status;
    $answer = array();
    $operation_exercice->submit_transfer($ledger_id);
    $answer['status'] = $oe_status;
    if ($answer['status'] == 'NOK') {
        $answer['content'] = span($oe_result, 'class=warning');
    }else {
        $answer['content'] = span($oe_result);

    }
    echo json_response($answer);
    return;
}