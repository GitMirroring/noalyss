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

/*! \file
 * \brief create GL comptes as CSV.
 * Argument $_GET
 @code
 * Array
(
    [gDossier] => 10104
    [bt_csv] => Export CSV
    [act] => CSV:glcompte
    [type] => poste
    [p_action] => impress
    [from_periode] => 01.01.2016
    [to_periode] => 31.12.2016
    [from_poste] => 
    [to_poste] => 
)
@endcode
 */

if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
include_once NOALYSS_INCLUDE.'/class/acc_account_ledger.class.php';
include_once NOALYSS_INCLUDE.'/lib/ac_common.php';
include_once NOALYSS_INCLUDE.'/lib/impress.class.php';
$http=new HttpInput();
$from_periode = $http->get("from_periode","date");
$to_periode = $http->get("to_periode","date");
$from_poste = $http->get("from_poste");
$to_poste = $http->get("to_poste");

$gDossier=dossier::id();
/* Security */
$cn=Dossier::connect();

$export=new Noalyss_Csv(_('grandlivre'));
$accounting_item_id=$http->get('poste_id',"string","");
$export->send_header();

$a_accounting=Acc_Account_Ledger::get_used_accounting($from_periode,$to_periode,$from_poste,$to_poste);

if ( count($a_accounting) == 0 )
{
    echo _('Aucun résultat');
    printf("\n");
    exit;
}

// Header
$header = array( _("Date"), _("Référence"), _("Libellé"), _("Pièce"),_("Lettrage"),_("Type"), _("Débit"), _("Crédit"), _("Solde") );

$l=(isset($_GET['letter']))?2:0;
$s=(isset($_REQUEST['solded']))?1:0;

foreach ($a_accounting as $accounting_item)
{


  $acc_account_ledger=new Acc_Account_Ledger($cn,$accounting_item['pcm_val']);

  $array1=$acc_account_ledger->get_row_date($from_periode,$to_periode,$l,$s);
  // don't print empty account
  if ( count($array1) == 0 )
    {
      continue;
    }
  $array=$array1[0];
  $tot_deb=$array1[1];
  $tot_cred=$array1[2];

    // don't print empty account
    if ( count($array) == 0 )
    {
        continue;
    }

    $export->add(sprintf("%s - %s ",$accounting_item['pcm_val'],$accounting_item['pcm_lib']));
    $export->write();
    $export->write_header($header);

    $solde = 0.0;
    $solde_d = 0.0;
    $solde_c = 0.0;
    bcscale(2);
    $current_exercice="";
    foreach ($acc_account_ledger->row as $detail)
    {

        /*
               [0] => 1 [jr_id] => 1
               [1] => 01.02.2009 [j_date_fmt] => 01.02.2009
               [2] => 2009-02-01 [j_date] => 2009-02-01
               [3] => 0 [deb_montant] => 0
               [4] => 12211.9100 [cred_montant] => 12211.9100
               [5] => Ecriture douverture [description] => Ecriture douverture
               [6] => Opération Diverses [jrn_name] => Opération Diverses
               [7] => f [j_debit] => f
               [8] => 17OD-01-1 [jr_internal] => 17OD-01-1
               [9] => ODS1 [jr_pj_number] => ODS1 ) 1
         */
/*
             * separation per exercice
             */
        if ( $current_exercice == "") $current_exercice=$detail['p_exercice'];

        if ( $current_exercice != $detail['p_exercice']) {
            $export->add("");
            $export->add($current_exercice);
            $export->add("");
            $export->add("");
            $export->add(_('Total')." ".$acc_account_ledger->id);
            if ( $solde_d > 0 ) {
                $export->add($solde_d,"number");
            } else {
                $export->add("");
            }
            if ( $solde_c > 0 ) {
                $export->add($solde_c,"number");
            } else {
                $export->add("");
            }

            $export->add(abs($solde_c-$solde_d),"number");
            $export->add(($solde_c > $solde_d ? 'C' : 'D'));
            $export->write();
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
            $solde   =bcsub($solde,$detail['cred_montant']);
            $solde_c =bcadd($solde_c, $detail['cred_montant']);
        }
        if ($detail['deb_montant'] > 0)
        {
            $solde    =bcadd($solde, $detail['deb_montant']);
            $solde_d = bcadd($solde_d,$detail['deb_montant']);
        }

        $export->add($detail['j_date_fmt']);
        $export->add($detail['jr_internal']);
        $export->add($detail['description']);
        $export->add($detail['jr_pj_number']);
        if ($detail['letter'] == -1) { $export->add(""); } 
        else { $export->add($detail['letter']);}
        $export->add($detail['jr_optype']);
        if ($detail['deb_montant']  > 0 ) 
            $export->add($detail['deb_montant'],"number");
        else
            $export->add("");
        
        if ($detail['cred_montant'] > 0 )
            $export->add($detail['cred_montant'],"number");
        else
            $export->add("");
        $export->add(abs($solde),"number");
	$export->add($acc_account_ledger->get_amount_side($solde),"text");
        $export->write();

    }


    $export->add("");
    $export->add($current_exercice);
    $export->add("");
    $export->add("");
    $export->add(_('Total').$acc_account_ledger->id);
    if ($solde_d  > 0 ) $export->add($solde_d,"number"); else $export->add("");
    if ($solde_c  > 0 ) $export->add($solde_c,"number"); else $export->add("");
    $export->add(abs($solde_c-$solde_d),"number");
    $export->add(($solde_c > $solde_d ? 'C' : 'D'));
    $export->write();
}

exit;

?>
