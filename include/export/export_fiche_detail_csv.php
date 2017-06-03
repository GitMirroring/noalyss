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
 * \brief Send the poste list in csv
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
include_once("lib/ac_common.php");
require_once NOALYSS_INCLUDE.'/lib/class_database.php';
require_once NOALYSS_INCLUDE.'/class/class_fiche.php';
require_once NOALYSS_INCLUDE.'/lib/class_noalyss_csv.php';
require_once NOALYSS_INCLUDE.'/lib/class_http_input.php';
$http=new HttpInput();

$f_id=$http->request("f_id", "number");
$from_periode=$http->get("from_periode");
$to_periode=$http->get("to_periode");
$ople=$http->get("ople");

require_once NOALYSS_INCLUDE.'/class/class_dossier.php';
$gDossier=dossier::id();

/* Admin. Dossier */
$cn=Dossier::connect();



$Fiche=new Fiche($cn,$f_id);
$qcode=$Fiche->get_quick_code();

$export=new Noalyss_Csv(_('fiche_').$qcode);
$export->send_header();

$Fiche->getName();
list($array,$tot_deb,$tot_cred)=$Fiche->get_row_date(
                                    $from_periode,
                                    $to_periode,
                                    $ople
                                );
if ( count($Fiche->row ) == 0 )
{
    echo _("Aucune donnée");
    return;
}


if ( ! isset ($_REQUEST['oper_detail']))
{
    $title=array();
    $title=array("Qcode",
                "Date",
                "n° pièce",
                "Code interne",
                "Code journal",
                "Nom journal",
                "Description",
                "Débit",
                "Crédit",
                "Prog.",
                "Let."   );
    $export->write_header($title);
    $progress=0;
    $current_exercice="";
    $tot_deb=0;$tot_cred=0; 
    bcscale(2);
    foreach ( $Fiche->row as $op )
    {
        /*
             * separation per exercice
             */
            if ( $current_exercice == "") $current_exercice=$op['p_exercice'];
            
            if ( $current_exercice != $op['p_exercice']) {
                $solde_type=($tot_deb>$tot_cred)?"solde débiteur":"solde créditeur";
                $diff=abs($tot_deb-$tot_cred);
                $export->add("");
                $export->add("");
                $export->add("");
                $export->add(_('total'));
                $export->add($current_exercice);
                $export->add($solde_type);
                $export->add($tot_deb,"number");
                $export->add($tot_cred,"number");
                $export->add($diff,"number");
                /*
                * reset total and current_exercice
                */
                $progress=0;
                $current_exercice=$op['p_exercice'];
                $tot_deb=0;$tot_cred=0;   
                 $export->write();
            }
        $diff=bcsub($op['deb_montant'],$op['cred_montant']);
        $progress=bcadd($progress,$diff);
        $tot_deb=bcadd($tot_deb,$op['deb_montant']);
        $tot_cred=bcadd($tot_cred,$op['cred_montant']);
        $export->add($op['j_qcode']);
        $export->add($op['j_date_fmt']);
        $export->add($op['jr_pj_number']);
        $export->add($op['jr_internal']);
        $export->add($op['jrn_def_code']);
        $export->add($op['jrn_def_name']);
        $export->add($op['description']);
        $export->add($op['deb_montant'],"number");
        $export->add($op['cred_montant'],"number");
        $export->add(abs($progress),"number");
        if ($op['letter'] !=-1){
            $export->add(strtoupper(base_convert($op['letter'],10,36)));
        } else {
            $export->add("");
        }
            
        $export->write();

    }
}
else
{
    $title=array("Poste","Qcode","date","ref","internal",
    "Description","Montant","D/C");

    $export->write_header($title);

    foreach ( $Fiche->row as $op )
    {
        $acc=new Acc_Operation($cn);
        $acc->jr_id=$op['jr_id'];
        $result= $acc->get_jrnx_detail();

        foreach ( $result as $r)
        {
            $export->add($r['j_poste']);
            $export->add($r['j_qcode']);
            $export->add($r['jr_date']);
            $export->add($op['jr_pj_number']);
            $export->add($r['jr_internal']);
            $export->add($r['description']);
            $export->add($r['j_montant'],"number");
            $export->add($r['debit']);
            $export->write();

        }



    }
}
$solde_type=($tot_deb>$tot_cred)?"solde débiteur":"solde créditeur";
$solde_type=($tot_cred == $tot_deb)?" solde = ":$solde_type;
$diff=abs($tot_deb-$tot_cred);
$export->add("");
$export->add("");
$export->add("");
$export->add(_("totaux"));
$export->add("");
$export->add($solde_type);
$export->add($diff,"number");
$export->add($tot_deb,"number");
$export->add($tot_cred,"number");

$export->write();
exit;
?>
