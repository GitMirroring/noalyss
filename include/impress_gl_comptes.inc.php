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
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
/*! \file
 * \brief Print account (html or pdf)
 *        file included from user_impress
 *
 * some variable are already defined $cn, $g_user ...
 *
 */
//-----------------------------------------------------
// Show the jrn and date
//-----------------------------------------------------
global $g_user,$http;
//-----------------------------------------------------
// Form
//-----------------------------------------------------
echo '<div class="content">';

echo '<FORM action="?" METHOD="GET" onsubmit="waiting_box();return true;">';
echo HtmlInput::hidden('ac',$http->request('ac'));
echo HtmlInput::hidden('type','gl_comptes');
echo dossier::hidden();
echo '<TABLE><TR>';

$cn=Dossier::connect();
$periode=new Periode($cn);
$a=$periode->get_limit($g_user->get_exercice());
// $a is an array
$first_day=$a[0]->first_day();
$last_day=$a[1]->last_day();

// filter on period
$date_from=new IDate('from_periode');
$date_to=new IDate('to_periode');
$year=$g_user->get_exercice();
$date_from->value=(isset($_REQUEST['from_periode'])&& isDate($_REQUEST['from_periode'])!=0)?$_REQUEST['from_periode']:$first_day;
$date_to->value=(isset($_REQUEST['to_periode']) && isDate($_REQUEST['to_periode']) !=0  )?$_REQUEST['to_periode']:$last_day;
echo td(_('Depuis').$date_from->input());
echo td(_('Jusque ').$date_to->input());

$letter=new ICheckbox('letter');
$letter->selected=(isset($_REQUEST['letter']))?true:false;

$from_poste=new IPoste('from_poste');
$from_poste->value=$http->request('from_poste',"string",'');
$from_poste->set_attribute('account','from_poste');

$to_poste=new IPoste('to_poste');
$to_poste->value=$http->request('to_poste',"string",'');
$to_poste->set_attribute('account','to_poste');

$solded=new ICheckbox('solded');
$solded->selected=(isset($_REQUEST['solded']))?true:false;

echo '<tr>';
echo td(_('Depuis le poste')).td($from_poste->input());
echo '</tr>';

echo '<tr>';
echo td(_("Jusqu'au poste")).td($to_poste->input());
echo '</tr>';

echo '<tr>';
echo td(_('Uniquement les opérations non lettrées'));
echo td($letter->input());
echo '</tr>';

echo '<tr>';
echo td(_('Uniquement les comptes non soldés'));
echo td($solded->input());
echo '</tr>';


//
echo '</TABLE>';
print HtmlInput::submit('bt_html',_('Visualisation'));

echo '</FORM>';
echo '<hr>';
echo '</div>';

//-----------------------------------------------------
// If print is asked
// First time in html
// after in pdf or cvs
//-----------------------------------------------------
if ( isset( $_REQUEST['bt_html'] ) )
{
    if ( DEBUGNOALYSS > 1 ) \Noalyss\Dbg::timer_start();

  echo '<div class="content">';
    echo Acc_Account_Ledger::HtmlTableHeader("gl_comptes");
    echo '</div>';
    try {
        $from_periode=$http->request("from_periode","date");
        $to_periode=$http->request("to_periode","date");

    } catch (Exception $e) {
        echo alert(_('Date malformée, désolée'));
        return;
    }


    $a_accounting=Acc_Account_Ledger::get_used_accounting($from_periode,$to_periode,$from_poste->value,$to_poste->value);

    if ( sizeof($a_accounting) == 0 )
    {
        echo_warning(_("Aucune donnée"));
        return;
    }

    echo '<div class="content">';


    echo '<table class="result">';
	$l=(isset($_REQUEST['letter']))?2:0;
	$s=(isset($_REQUEST['solded']))?1:0;
    
    
    foreach ($a_accounting as $accounting_id )
    {
        $acc_account_ledger=new Acc_Account_Ledger ($cn, $accounting_id['pcm_val']);

        $acc_account_ledger->get_row_date( $from_periode, $to_periode,$l,$s);
        if ( empty($acc_account_ledger->row))
        {
            continue;
        }
        

        echo '<tr >
        <td colspan="8" style="width:auto">
        <h2 class="">'. $accounting_id['pcm_val'].' '.h($accounting_id['pcm_lib']).'</h2>
        </td>
        </tr>';

        echo '<tr>
        <td>Date</td>
        <td>R&eacute;f&eacute;rence</td>
        <td>Libell&eacute;</td>
        <td>Pi&egrave;ce</td>
        <td>Type</td>
        <td align="right">D&eacute;bit</td>
        <td align="right">Cr&eacute;dit</td>
        <td align="right">Solde</td>
        <td align="right">Let.</td>
        </tr>';

        $solde = 0.0;
        $solde_d = 0.0;
        $solde_c = 0.0;
	bcscale(2);
	$i=0;
        $current_exercice="";

        foreach ($acc_account_ledger->row as $detail)
        {
            /*
             * separation per exercice
             */
            if ( $current_exercice == "") $current_exercice=$detail['p_exercice'];
            
            if ( $current_exercice != $detail['p_exercice']) {
                echo '<tr class="highlight">
               <td>'.$current_exercice.'</td>
               <td>'.''.'</td>
               <td>'._("Total du compte").$accounting_id['pcm_val'].'</td>
               <td>'.''.'</td>'.td("").
               '<td align="right">'.($solde_d  > 0 ? nbm( $solde_d)  : '').'</td>
               <td align="right">'.($solde_c  > 0 ? nbm( $solde_c)  : '').'</td>
               <td align="right">'.nbm( abs($solde_c-$solde_d)).'</td>
               <td>';
               if ($solde_c > $solde_d ) echo _("Crédit");
               if ($solde_c < $solde_d )  echo _("Débit");
               if ($solde_c == $solde_d )  echo  " ";

             echo '</td>'.
               '</tr>';
             /*
              * reset total and current_exercice
              */
                $current_exercice=$detail['p_exercice'];
                $solde = 0.0;
                $solde_d = 0.0;
                $solde_c = 0.0;

            }
            
            
            if ($detail['cred_montant'] > 0)
            {
	      $solde=bcsub($solde, $detail['cred_montant']);
	      $solde_c=bcadd($solde_c,$detail['cred_montant']);
            }
            if ($detail['deb_montant'] > 0)
            {
	      $solde   = bcadd($solde,$detail['deb_montant']);
	      $solde_d = bcadd($solde_d,$detail['deb_montant']);
            }
			$side="&nbsp;".$acc_account_ledger->get_amount_side($solde);
	    $letter="";
		$html_let="";
		if ($detail['letter'] > 0) {
			$letter=strtoupper(base_convert($detail['letter'],10,36));
			$html_let = HtmlInput::show_reconcile("", $letter);
		}
		$i++;
		if (($i % 2 ) == 0) $class="odd"; else $class="even";
            echo '<tr name="tr_'.$letter.'_" class="'.$class.'">
            <td>'.$detail['j_date_fmt'].'</td>
            <td>'.HtmlInput::detail_op($detail['jr_id'],$detail['jr_internal']).'</td>
            <td>'.$detail['description'].'</td>
            <td>'.$detail['jr_pj_number'].'</td>
            <td>'.$detail['jr_optype'].'</td>
            <td align="right">'.($detail['deb_montant']  > 0 ? nbm($detail['deb_montant'])  : '').'</td>
            <td align="right">'.($detail['cred_montant'] > 0 ? nbm($detail['cred_montant']) : '').'</td>
            <td align="right">'.nbm(abs($solde)).$side.'</td>
            <td  style="text-align:right;color:red">'.$html_let.'</td>
            </tr>';
        }
        echo '<tr class="highlight">
        <td>'.$current_exercice.'</td>
        <td>'.''.'</td>
        <td>'.'<b>'.'Total du compte '.$accounting_id['pcm_val'].'</b>'.'</td>
        <td>'.''.'</td>'.td("").
        '<td align="right">'.'<b>'.($solde_d  > 0 ? nbm( $solde_d)  : '').'</b>'.'</td>
        <td align="right">'.'<b>'.($solde_c  > 0 ? nbm( $solde_c)  : '').'</b>'.'</td>
        <td align="right">'.'<b>'.nbm( abs($solde_c-$solde_d)).'</b>'.'</td>
        <td>';
	if ($solde_c > $solde_d ) echo "Crédit";
	if ($solde_c < $solde_d )  echo "Débit";
	if ($solde_c == $solde_d )  echo "=";

      echo '</td>'.
        '</tr>';
    }
    echo '</table>';
    echo Acc_Account_Ledger::HtmlTableHeader("gl_comptes");
    echo "</div>";
    if ( DEBUGNOALYSS > 1 ) \Noalyss\Dbg::timer_show();
    return;
}
?>
