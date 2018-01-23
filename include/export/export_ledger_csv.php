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
/* ! 
 * \file
 * \brief Send a ledger in CSV format , receives 
 *    - jrn_id id of the ledger
 *    - p_simple L list , D detailled, A accounting, E extended
 *    - from periode
 *    - to periode
 */
if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
include_once NOALYSS_INCLUDE."/lib/ac_common.php";
require_once NOALYSS_INCLUDE.'/class/noalyss_parameter_folder.class.php';
require_once NOALYSS_INCLUDE.'/class/acc_ledger_sold.class.php';
require_once NOALYSS_INCLUDE.'/class/acc_ledger_purchase.class.php';
require_once NOALYSS_INCLUDE.'/class/dossier.class.php';
$gDossier=dossier::id();

require_once NOALYSS_INCLUDE.'/lib/database.class.php';
require_once NOALYSS_INCLUDE.'/class/acc_ledger.class.php';
require_once NOALYSS_INCLUDE.'/lib/noalyss_csv.class.php';

require_once NOALYSS_INCLUDE.'/lib/http_input.class.php';
$http=new HttpInput();

$export=new Noalyss_Csv(_('journal'));

$export->send_header();


/*
 * Variable from $_GET
 */
try
{
    $get_jrn=$http->get('jrn_id', "number");
    $get_option=$http->get('p_simple', "string");
    $get_from_periode=$http->get('from_periode', 'number');
    $get_to_periode=$http->get('to_periode', 'number');
}
catch (Exception $exc)
{
    echo $exc->getMessage();
    error_log($exc->getTraceAsString());
    throw $exc;
}

require_once NOALYSS_INCLUDE.'/class/user.class.php';
$g_user->Check();
$g_user->check_dossier($gDossier);

//----------------------------------------------------------------------------
// $get_jrn == 0 when request for all ledger, in that case, we must filter
// the legder with the security in Acc_Ledger::get_row
//----------------------------------------------------------------------------
if ($get_jrn!=0&&$g_user->check_jrn($get_jrn)=='X')
{
    NoAccess();
    exit();
}
global $g_user;
/**
 * for all ledgers
 */
if ($get_jrn==0)
{
    // Find periode 
    $periode=new Periode($cn, $get_from_periode);
    $exercice=$periode->get_exercice($get_from_periode);

    if ($g_user->Admin()==0&&$g_user->is_local_admin()==0&&$g_user->get_status_security_ledger()
            ==1)
    {
        $sql="select jrn_def_id 
                 from jrn_def join jrn_type on jrn_def_type=jrn_type_id
                 join user_sec_jrn on uj_jrn_id=jrn_def_id
                 where
                 uj_login=$1
                 and uj_priv in ('R','W')
                         order by jrn_def_name
                 and ( jrn_enable=1 
                        or 
                        exists (select 1 from jrn where jr_tech_per in (select p_id from parm_periode where p_exercice=$2))
                 ";
        $a_jrn=$cn->get_array($sql, array($g_user->login, $exercice));
    }
    else
    {
        $a_jrn=$cn->get_array("select jrn_def_id
                                 from jrn_def join jrn_type on jrn_def_type=jrn_type_id
                                 where
                                 jrn_enable=1 or exists(select 1 from jrn where jr_tech_per in (select p_id from parm_periode where p_exercice=$1))
                                                         order by jrn_def_name
                                                         ", [$exercice]);
    }
    $a=[];
    $nb_jrn=count($a_jrn);
    for ($i=0;$i< $nb_jrn;$i++){
        $a[]=$a_jrn[$i]['jrn_def_id'];
    }
    $a_jrn=$a;
}
else
{
    $a_jrn=$Jrn->id;
}
$Jrn=new Acc_Ledger($cn, $get_jrn);

$Jrn->get_name();
$jrn_type=$Jrn->get_type();

//
// With Detail per item which is possible only for VEN or ACH
//  For Detailled VAT for ACH or VEN
//  ODS or all ledgers becomes A
//  Extended but no FIN becomes L
// 
if ($get_option=='D'||($jrn_type=='ODS'||$Jrn->id==0)&&$get_option=="E")
{
    if ($jrn_type=='FIN')
    {
        $get_option='L';
    }
    elseif ($jrn_type=='ODS'||$Jrn->id==0)
    {
        $get_option='A';
    }
    else
    {
        switch ($jrn_type)
        {
            case 'VEN':
                $ledger=new Acc_Ledger_Sold($cn, $get_jrn);
                $ret_detail=$ledger->get_detail_sale($get_from_periode,
                        $get_to_periode);
                $a_heading=Acc_Ledger_Sold::heading_detail_sale();

                break;
            case 'ACH':
                $ledger=new Acc_Ledger_Purchase($cn, $get_jrn);
                $ret_detail=$ledger->get_detail_purchase($get_from_periode,
                        $get_to_periode);
                $a_heading=Acc_Ledger_Purchase::heading_detail_purchase();
                break;
            default:
                die(__FILE__.":".__LINE__.'Journal invalide');
                break;
        }
        if ($ret_detail==null)
            return;
        $nb=Database::num_row($ret_detail);
        $title=array();
        foreach ($a_heading as $key=> $value)
        {
            $title[]=$value;
        }
        for ($i=0; $i<$nb; $i++)
        {
            $row=Database::fetch_array($ret_detail, $i);
            if ($i==0)
            {
                $export->write_header($title);
            }
            $a_row=array();
            $type="text";
            for ($j=0; $j<count($row)/2; $j++)
            {
                if ($j>18)
                    $type="number";
                $export->add($row[$j], $type);
            }
            $export->write();
        }
    }
}
//-----------------------------------------------------------------------------
// Detailled printing
// For miscellaneous legder or all ledgers
//-----------------------------------------------------------------------------
if ($get_option=='A')
{

    $acc_ledger_history=new Acc_Ledger_History_Generic($cn, $a_jrn,
            $get_from_periode, $get_to_periode, 'A');
    $acc_ledger_history->export_csv();
    exit;
}
//-----------------------------------------------------------------------------
// Detail printing for ACH or VEN : 1 row resume the situation with VAT, DNA
// for Misc the amount 
// For Financial only the tiers and the sign of the amount
//-----------------------------------------------------------------------------
if ($get_option=="L")
{

//-----------------------------------------------------
    if ($jrn_type=='ODS'||$jrn_type=='FIN'||$jrn_type=='GL')
    {
        if ( $get_jrn==0) {
            $Row=$Jrn->get_rowSimple($get_from_periode, $get_to_periode, $a_jrn);
        }else {
            $Row=$Jrn->get_rowSimple($get_from_periode, $get_to_periode);
        }
        $cn->prepare('reconcile_date_csv',
                'select  * 
                         from 
                           jrn 
                         where 
                           jr_id in 
                               (select 
                                   jra_concerned 
                                   from 
                                   jrn_rapt 
                                   where jr_id = $1 
                                union all 
                                select 
                                jr_id 
                                from jrn_rapt 
                                where jra_concerned=$1)');
        $title=array();
        $title[]=_("operation");
        $title[]=_("Date");
        $title[]=_("N° Pièce");
        $title[]=_("QuickCode");
        $title[]=_("Tiers");
        $title[]=_("commentaire");
        $title[]=_("internal");
        $title[]=_("montant");
        $export->write_header($title);
        foreach ($Row as $line)
        {
            $tiers_id=$Jrn->get_tiers_id($line['jrn_def_type'], $line['jr_id']);
            $fiche_tiers=new Fiche($cn, $tiers_id);
            $tiers=$fiche_tiers->strAttribut(ATTR_DEF_NAME, 0)." ".$fiche_tiers->strAttribut(ATTR_DEF_FIRST_NAME,
                            0);

            $export->add($line['num']);
            $export->add($line['date']);
            $export->add($line['jr_pj_number']);
            $export->add($fiche_tiers->get_quick_code());
            $export->add($tiers);
            $export->add($line['comment']);
            $export->add($line['jr_internal']);
            //	  echo "<TD>".$line['pj'].";";
            // If the ledger is financial :
            // the credit must be negative and written in red
            // Get the jrn type
            if ($line['jrn_def_type']=='FIN')
            {
                $positive=$cn->get_value("select qf_amount from quant_fin  ".
                        " where jr_id=$1", array($line['jr_id']));

                $export->add($positive, "number");
                $export->add("");
            }
            else
            {
                $export->add($line['montant'], "number");
            }
            //------ Add reconcilied operation ---------------
            $ret_reconcile=$cn->execute('reconcile_date_csv',
                    array($line['jr_id']));
            $max=Database::num_row($ret_reconcile);
            if ($max>0)
            {
                for ($e=0; $e<$max; $e++)
                {
                    $row=Database::fetch_array($ret_reconcile, $e);
                    $export->add($row['jr_date']);
                    $export->add($row['jr_internal']);
                    $export->add($row['jr_pj_number']);
                }
            }
            $export->write();
        }
    }

//------------------------------------------------------------------------------
// One line summary with tiers, amount VAT, DNA, tva code ....
// 
//------------------------------------------------------------------------------
    if ($jrn_type=="ACH")
    {
        $acc_ledger_history=new Acc_Ledger_History_Purchase($cn, [$Jrn->id],
                $get_from_periode, $get_to_periode, 'D');
        $acc_ledger_history->export_csv();
    }
    if ($jrn_type=="VEN")
    {
        $acc_ledger_history=new Acc_Ledger_History_Sale($cn, [$Jrn->id],
                $get_from_periode, $get_to_periode, 'D');
        $acc_ledger_history->export_csv();
    }
}
?>
