<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt

/**
 * Export to CSV the operations asked in impress_rec.inc.php
 * variable set $g_user,$cn
 * @see impress_rec.inc.php
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

require_once NOALYSS_INCLUDE.'/class/class_acc_reconciliation.php';
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
require_once NOALYSS_INCLUDE.'/lib/class_database.php';
require_once NOALYSS_INCLUDE.'/class/class_dossier.php';
require_once NOALYSS_INCLUDE.'/lib/class_noalyss_csv.php';
require_once NOALYSS_INCLUDE.'/lib/class_http_input.php';
$http=new HttpInput();
try
{
    $choice=$http->get("choice");
    $p_start=$http->get("p_start");
    $p_end=$http->get("p_end");
    $r_jrn=$http->get("r_jrn","string","");
   
}
catch (Exception $exc)
{
    error_log($exc->getTraceAsString());
    return;
}

// -------------------------
// Create object and export
$acc_reconciliation=new Acc_Reconciliation($cn);
$acc_reconciliation->a_jrn=$r_jrn;
$acc_reconciliation->start_day=$p_start;
$acc_reconciliation->end_day=$p_end;

$array=$acc_reconciliation->export_csv($choice);