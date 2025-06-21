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

/*!
 * \file
 * \brief print the all the operation reconciled or not, with or without the same amount
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

global $g_user;
$http=new HttpInput();

$aledger=$g_user->get_ledger('ALL',3);
echo '<div class="noprint">';
echo '<div class="content">';
$rjrn='';

$choice=$http->get("choice","string",0); 
$r_jrn=$http->get("r_jrn","string","");
echo '<form method="GET">';
echo dossier::hidden().HtmlInput::hidden('ac',$http->request('ac')).HtmlInput::hidden('type','rec');
echo _('Filtre par journal');
HtmlInput::button_choice_ledger(array('div'=>'','type'=>'ALL','all_type'=>1));
echo '<br/>';
/*
 * Limit by date, default current exercice
 */
$error=0;
$dstart=new IDate('p_start');
$dend=new IDate('p_end');
list($start,$end)=$g_user->get_limit_current_exercice();

try {
    $dstart->value=$http->request('p_start','date',$start);

    $dend->value=$http->request('p_end','date',$end);

} catch (\Exception $e) {
    echo_warning('Date invalide');
    $error=1;

}

echo "Opérations entre ".$dstart->input()." jusque ".$dend->input();

$select=new ISelect("choice");
$select->transform([
        0=>_('Opérations rapprochées')
        ,1=>_('Opérations rapprochées avec des montants différents')
        ,2=>_('Opérations rapprochées avec des montants identiques')
        ,3=>_('Opérations non rapprochées')
    ]
);
$select->selected=$choice;
echo $select->input();
echo '<p>';
echo HtmlInput::submit('vis',_('Visualisation'));
echo '</p>';
echo '</form>';
echo '<hr>';
echo '</div>';
echo '</div>';
echo '<div class="content">';
if ( ! isset($_GET['vis'])) return;
if ($error == 1) return;
$acc_reconciliation=new Acc_Reconciliation($cn);
$acc_reconciliation->a_jrn=$r_jrn;
$acc_reconciliation->start_day=$dstart->value;
$acc_reconciliation->end_day=$dend->value;
$acc_reconciliation->prepare_query_detail_quant();
$array=$acc_reconciliation->get_data($choice);

$gDossier=Dossier::id();
?>
<form method="get" action="export.php">
    <?php echo HtmlInput::get_to_hidden(array('ac','gDossier','p_end','p_start','choice','r_jrn'));
    echo HtmlInput::hidden('act','CSV:Reconciliation');
    echo HtmlInput::submit("csv_bt", "Export CSV");
    ?>
</form>
<?php
require_once NOALYSS_TEMPLATE.'/impress_reconciliation.php';
return;