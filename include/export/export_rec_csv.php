<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt

/**
 * @file
 * @brief Export to CSV the operations asked in impress_rec.inc.php
 * variable set $g_user,$cn
 * @see impress_rec.inc.php
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
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
    record_log($exc);
    return;
}

// -------------------------
// Create object and export
$acc_reconciliation=new Acc_Reconciliation($cn);
$acc_reconciliation->a_jrn=$r_jrn;
$acc_reconciliation->start_day=$p_start;
$acc_reconciliation->end_day=$p_end;

$array=$acc_reconciliation->export_csv($choice);