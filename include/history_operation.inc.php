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

// Copyright Author Dany De Bontridder danydb@aevalys.eu

/**
 * \file
 * \brief display history of accountant , and let search into
 *
 */
echo js_include("operation_payment.js");
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
global $g_user,$cn,$http;
$p_array = $_GET;
$ledger_type=$http->get("ledger_type","string", 'ALL');

switch($ledger_type)
{
    case 'ACH':
            $ask_pay=1;
            $p_array['ledger_type']='ACH';
            break;
    case 'ODS':
            $ask_pay=0;
            $p_array['ledger_type']='ODS';
            break;
    case 'ALL':
            $ask_pay=0;
            $p_array['ledger_type']='ALL';
            break;
    case 'VEN':
            $ask_pay=1;
            $p_array['ledger_type']='VEN';
            break;
    case 'FIN':
            $ask_pay=0;
            $p_array['ledger_type']='FIN';
            break;
    default:
        throw new \Exception("HO58 : ledger_type unknown");

}
$Ledger=new Acc_Ledger_Search($p_array['ledger_type'],0,'search_op');
echo '<div class="content">';
// Check privilege
$p_jrn=$http->request("p_jrn", "string",-1);
if (isset($_REQUEST['p_jrn']) &&
		$g_user->check_jrn($p_jrn) == 'X')
{

	NoAccess();
	exit - 1;
}

$Ledger->id = $p_jrn;

$msg="";
/* by default we should use the default period */
if (!isset($p_array['date_start']))
{
	$period = $g_user->get_periode();
	$per = new Periode($cn, $period);
	list($date_start, $date_end) = $per->get_date_limit();
	$p_array['date_start'] = $date_start;
	$p_array['date_end'] = $date_end;
	$msg='<h2 class="h-section" class="">'.sprintf(_("Période %s au %s "),$date_start,$date_end).'</h2>';
}
else
{
    $date_start=$http->get("date_start","string","");
    $date_end=$http->get("date_end","string","");
    $msg='<h2 class="h-section" class="">'.sprintf(_("Période %s au %s "),$date_start,$date_end) .'</h2>';

}
/*  compute the sql stmt */
list($sql, $where) = $Ledger->build_search_sql($p_array);
$max_line = $cn->count_sql($sql);

$step = $_SESSION[SESSION_KEY.'g_pagesize'];
try
{
    $page = $http->get('page','string',1); 
    $offset =$http->get('offset','string',0); 
} catch (\Exception $e)
{
    $page=1;$offset=0;
}


$bar = navigation_bar($offset, $max_line, $step, $page);

echo $msg;
echo $Ledger->button_propose_filter();
echo HtmlInput::filter_table('history_operation_t', '0,1,2,3,4,5,6,7', 1);
echo $Ledger->display_search_form();
echo $bar;
echo '<form method="GET" id="fpaida" class="print">';
echo HtmlInput::hidden("ac", $http->request('ac'));
echo HtmlInput::hidden('ledger_type',$ledger_type);
echo dossier::hidden();

list($count, $html) = $Ledger->list_operation($sql, $offset, $ask_pay);


echo $html;
echo $bar;
$r = HtmlInput::get_to_hidden(array('search_opnb_jrn',
				    'operation_filter',
    'search_opqcode',
    'l', 
    'date_start', 
    'date_end', 
    'date_paid_start',
    'date_paid_end',
    'desc', 
    'amount_min', 
    'amount_max', 
    'qcode', 
    'accounting', 
    'unpaid', 
    'gDossier', 
    'ledger_type', 
    'p_action',
    'search_opr_jrn'));
if (isset($_GET['r_jrn']))
{
    $a_rjn=$http->get('r_jrn','array');
    foreach ($a_rjn as $k => $v) {
      if (isNumber($v))  $r.=HtmlInput::hidden('r_jrn[' . $k . ']', $v);
    }
}
if (isset($_GET['search_opr_jrn']))
{
     $a_search_opr_jrn=$http->get('search_opr_jrn');
    foreach ($a_search_opr_jrn as $k => $v)
          if (isNumber($v)) $r.=HtmlInput::hidden('r_jrn[' . $k . ']', $v);
}
echo $r;

echo '</form>';
/*
 * Export to csv
 */
$r = HtmlInput::get_to_hidden(array('l', 'date_paid_start','date_paid_end',
				    'date_start', 'date_end', 'desc', 'amount_min', 'amount_max', 'qcode','operation_filter',
				    'accounting', 'unpaid', 'gDossier', 'ledger_type', 
                                    'p_action','search_optag_option','p_currency_code','tva_id_search'));
if (isset($_GET['search_opr_jrn']))
{
    foreach ($a_search_opr_jrn as $k => $v)
       if (isNumber($v))  $r.=HtmlInput::hidden('r_jrn[' . $k . ']', $v);
}
$r.=HtmlInput::hidden("tag_option",$http->request("search_optag_option","string",0));
if (isset($_GET['r_jrn']))
{
	foreach ($a_rjn as $k => $v)
	if (isNumber($v)) 	$r.=HtmlInput::hidden('r_jrn[' . $k . ']', $v);
}
if (isset($_GET['search_optag'])) {
    $http=new HttpInput();
    $aTag=$http->get("search_optag","array");
  foreach ($aTag as $k=>$v) {
      // Protect : check that $k and $v are numeric
    if (isNumber($k)&&isNumber($v)) {
        $r.=HtmlInput::hidden('tag[]',$v);
    }
  }
}
echo '<form action="export.php" method="get">';
echo $r;
echo HtmlInput::hidden('act', 'CSV:histo');
echo HtmlInput::submit('viewsearch', _('Export vers CSV'));
$qcode=$http->get("search_opqcode","string","");
echo HtmlInput::hidden('qcode',trim($qcode));
echo '</form>';

echo '</div>';
if ( $ask_pay){
?>
<script>
    var operation_payment=new Operation_Payment("paid_operation_ck",'<?=Dossier::id()?>','<?=$http->request("ac")?>');
    operation_payment.activate_checkbox_range()
</script>
<?php
}