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
 * \brief send the account list in PDF
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
require_once NOALYSS_INCLUDE.'/header_print.php';
global $g_parameter;

$http=new HttpInput();

$poste_id=$http->request("poste_id");
$from_periode=$http->request("from_periode");
$to_periode=$http->request("to_periode");
$ople=$http->request("ople");
$poste_fille=$http->request("poste_fille","string",-1);
$gDossier=dossier::id();

/* Security */
$cn=Dossier::connect();

if (  $poste_fille <> -1)
{ //choisit de voir tous les postes
    $a_poste=$cn->get_array("select pcm_val from tmp_pcmn where pcm_val::text like $1||'%' order by pcm_val::text",array($poste_id));
}
else
{
    $a_poste=$cn->get_array("select pcm_val from tmp_pcmn where pcm_val::text = $1 ",array($poste_id));
}
tracedebug("exportpostedetail",$a_poste,"a_poste");
$ret="";

$pdf=new PDF($cn);
$pdf->setDossierInfo(sprintf(_("  Période : %s %s"),$from_periode,$to_periode));
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAuthor('NOALYSS');
$pdf->setTitle(_("Détail poste comptable"),true);


if ( count($a_poste) == 0 )
{
    $pdf->Output('poste.pdf','D');
    exit;
}
$size=array(13,25,13,65,12,20,20,20);
$align=array('L','C','C','L','R','R','R','R');
 $operation=new Acc_Operation($cn);

foreach ($a_poste as $poste)
{
    $Poste=new Acc_Account_Ledger($cn,$poste['pcm_val']);

    list($array,$tot_deb,$tot_cred)=$Poste->get_row_date($from_periode,$to_periode,$_GET['ople']);
    // don't print empty account
    if ( count($array) == 0 )
    {
        continue;
    }
    $Libelle=sprintf("(%s) %s ",$Poste->id,$Poste->get_name());
    $pdf->SetFont('DejaVuCond','',10);
    $pdf->write_cell(0,8,$Libelle,1,0,'C');
    $pdf->line_new();

    $type=$Poste->get_type();
    // label  warning if the saldo is incorrect
    $label="";
    if (in_array($type,array('CHA','ACT','PASINV','PROINV')) && $tot_deb<$tot_cred)
    {

        $label=_("Solde créditeur au lieu de débiteur").mb_chr(9888);
    }
    if (in_array($type,array('PRO','PAS','ACTINV','CHAINV')) && $tot_deb>$tot_cred)
    {

        $label=_("Solde débiteur au lieu de créditeur").mb_chr(9888);
    }
    if ( $label !="" ) 
    {
        // warning about side
        $pdf->SetTextColor(255,39,24);
        $pdf->write_cell(0,8,$label,1,0,'C',fill:false);
        $pdf->line_new();
    }
    $pdf->SetTextColor(0);
    $pdf->SetFont('DejaVuCond','',8);
    $l=0;
    $pdf->write_cell($size[$l],8,_('Date'),0,0,'L');
    $l++;
    $pdf->write_cell($size[$l],8,_('Ref'),0,0,'C');
    $l++;
    $pdf->write_cell($size[$l],8,_('Journal'),0,0,'C');
    $l++;
    $pdf->write_multi($size[$l],8,_('Libellé'),0,'L');
    $l++;
    $pdf->write_cell($size[$l],8,_('Let'),0,0,'R');
    $l++;
    $pdf->write_cell($size[$l],8,_('Debit'),0,0,'R');
    $l++;
    $pdf->write_cell($size[$l],8,_('Credit'),0,0,'R');
    $l++;
    $pdf->write_cell($size[$l],8,_('Prog'),0,0,'R');
    $l++;
    $pdf->line_new();
    $tot_deb=0;
    $tot_cred=0;
    $prog=0;
    $current_exercice="";
    bcscale(2);
    for ($e=0;$e<count($array);$e++)
    {
        $row=$array[$e];
         /*
             * separation per exercice
             */
            if ( $current_exercice == "") $current_exercice=$row['p_exercice'];
            
            if ( $current_exercice != $row['p_exercice']) {
                    $str_debit=sprintf("% 12.2f ",$tot_deb);
                    $str_credit=sprintf("% 12.2f ",$tot_cred);
                    $diff_solde=bcsub($tot_deb,$tot_cred);
                    if ( $diff_solde < 0 )
                    {
                        $solde=_(" C ");
                        $diff_solde=bcmul($diff_solde,-1);
                    }
                    else
                    {
                         $solde=_(" D ");
                    }
                    $str_diff_solde=sprintf("%12.2f ",$diff_solde);

                    $pdf->SetFont('DejaVu','B',8);
                    $pdf->write_cell(15,8,_('totaux'),0,0,'L');
                    $pdf->write_cell(15,8,$current_exercice,0,0,'L');
                    $pdf->write_cell(40,8,$solde,0,'L');
                    $pdf->write_cell(40,8,$str_debit,0,0,'R');
                    $pdf->write_cell(40,8,$str_credit,0,0,'R');
                    $pdf->write_cell(40,8,$str_diff_solde,0,0,'R');
                    $pdf->line_new();
                    /*
                    * reset total and current_exercice
                    */
                    $prog=0;
                    $current_exercice=$row['p_exercice'];
                    $tot_deb=0;$tot_cred=0;    
                    $pdf->SetFont('DejaVuCond','',8);
            }
        $l=0;
        $diff=bcsub($row['deb_montant'],$row['cred_montant']);
        $prog=bcadd($prog,$diff);
        $fill=(isset($_GET['oper_detail']))?$pdf->is_fill(0):$pdf->is_fill($e);
        $date=shrink_date($row['j_date_fmt']);
        $pdf->write_cell($size[$l],8,$date,0,0,$align[$l],fill:$fill);
        $l++;
	if ( $row['jr_pj_number'] == '')
	  $pdf->write_cell($size[$l],8,$row['jr_internal'],0,0,$align[$l],fill:$fill);
	else
	  $pdf->write_cell($size[$l],8,$row['jr_pj_number'],0,0,$align[$l],fill:$fill);
        $l++;
        $pdf->write_cell($size[$l],8,mb_substr($row['jrn_def_code'],0,14),0,0,$align[$l],fill:$fill);
        $l++;
        $tiers=$operation->find_tiers($row['jr_id'], $row['j_id'], $row['j_qcode']);
        $description=($tiers=="")?$row["description"]:"[".$tiers."]".$row['description'];
        $pdf->write_multi($size[$l],8,  $description,0,$align[$l],fill:$fill);
        $l++;
        $pdf->write_cell($size[$l],8,(($row['letter']!=-1)?$row['letter']:''),0,0,$align[$l],fill:$fill);
        $l++;
        $pdf->write_cell($size[$l],8,(sprintf('% 12.2f',$row['deb_montant'])),0,0,$align[$l],fill:$fill);
        $l++;
        $pdf->write_cell($size[$l],8,(sprintf('% 12.2f',$row['cred_montant'])),0,0,$align[$l],fill:$fill);
        $l++;
        $solde="=";
        if ( $prog < 0 ) 
            $solde=_('C');
        elseif ($prog > 0)
        {
            $solde=_("D");
        }
        $pdf->write_cell($size[$l],8,(sprintf('% 12.2f %s',abs($prog),$solde)),0,0,$align[$l],fill:$fill);
        $l++;
        $pdf->line_new(8);
        $tot_deb=bcadd($tot_deb,$row['deb_montant']);
        $tot_cred=bcadd($tot_cred,$row['cred_montant']);
        /* -------------------------------------- */
        /* if details are asked we show them here */
        /* -------------------------------------- */
        if ( isset($_GET['oper_detail']))
        {
            $detail=new Acc_Operation($cn);
            $detail->jr_id=$row['jr_id'];
            $a_detail=$detail->get_jrnx_detail();
            for ($f=0;$f<count($a_detail);$f++)
            {
                $l=0;
                $pdf->write_cell($size[$l],6,'',0,0,$align[$l]);
                $l++;
                $pdf->write_cell($size[$l],6,$a_detail[$f]['j_qcode'],0,0,$align[$l]);
                $l++;
                $pdf->write_cell($size[$l],6,$a_detail[$f]['j_poste'],0,0,$align[$l]);
                $l++;
                if ( $a_detail[$f]['j_qcode']=='')
                    $lib=$a_detail[$f]['pcm_lib'];
                else
                {
                    $f_id=$cn->get_value('select f_id from vw_poste_qcode where j_qcode=$1',array($a_detail[$f]['j_qcode'])) ;
                    $lib=$cn->get_value('select ad_value from fiche_detail where ad_id=$1 and f_id=$2',
                                        array(ATTR_DEF_NAME,$f_id));
                }
                $pdf->write_cell($size[$l],6,$lib,0,0,$align[$l]);
                $l++;
                $pdf->write_cell($size[$l],6,(($a_detail[$f]['letter']!=-1)?$a_detail[$f]['letter']:''),0,0,$align[$l]);
                $l++;

                $deb=($a_detail[$f]['debit']=='D')?$a_detail[$f]['j_montant']:'';
                $cred=($a_detail[$f]['debit']=='C')?$a_detail[$f]['j_montant']:'';

                $pdf->write_cell($size[$l],6,(sprintf('% 12.2f',$deb)),0,0,$align[$l]);
                $l++;
                $pdf->write_cell($size[$l],6,(sprintf('% 12.2f',$cred)),0,0,$align[$l]);
                $l++;
                $pdf->line_new();
            }
        }
    }
    $str_debit=sprintf("% 12.2f ",$tot_deb);
    $str_credit=sprintf("% 12.2f ",$tot_cred);
    $diff_solde=bcsub($tot_deb,$tot_cred);
    $solde=" = ";
    if ( $diff_solde < 0 )
    {
        $solde=_(" C ");
        $diff_solde=bcmul($diff_solde,-1);
    }
    elseif ( $diff_solde > 0)
    {
        $solde=_(" D ");
    }
    $str_diff_solde=sprintf("%12.2f ",$diff_solde);

    $pdf->SetFont('DejaVu','B',8);
    $pdf->line_new();
    $pdf->write_cell(160,5,_("Débit"),0,0,'R');
    $pdf->write_cell(30,5,$str_debit,0,0,'R');
    $pdf->line_new();
    $pdf->write_cell(160,5,_('Crédit'),0,0,'R');
    $pdf->write_cell(30,5,$str_credit,0,0,'R');
    $pdf->line_new();
    $pdf->write_cell(160,5,'Solde '.$solde,0,0,'R');
    $pdf->write_cell(30,5,$str_diff_solde,0,0,'R');
    $pdf->line_new();

    // take saldo from 1st day until last
    if ($g_parameter->MY_REPORT=='N') {

        $solde_until_now=$Poste->get_solde_detail("  j_date <= to_date('$to_periode','DD.MM.YYYY')  ");

        $pdf->write_cell(40,5,"Solde global",0,0,'R');
        $pdf->write_cell(40,5,"D : ".nbm($solde_until_now['debit']),0,0,'R');
        $pdf->write_cell(40,5,"C : ".nbm($solde_until_now['credit']),0,0,'R');
        $pdf->write_cell(40,5,"Delta : ".nbm($solde_until_now['solde'])." ".$Poste->get_amount_side($solde_until_now['debit']-$solde_until_now['credit']),0,0,'R');
        $pdf->line_new();

    }
}
$fDate=date('dmy-Hi');
$pdf->Output('poste-'.$fDate.'-'.$poste_id.'.pdf','D');
?>
