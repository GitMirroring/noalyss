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

/*!\file
 * \brief respond ajax request, the get contains
 *  the value :
 * - c for qcode
 * - t for tva_id -1 if there is no TVA to compute
 * - p for price
 * - q for quantity
 * - n for number of the ctrl
 * - gDossier
 * Must return at least tva, htva and tvac
 */
require_once '../include/constant.php';
require_once  NOALYSS_INCLUDE.'/class/database.class.php';
require_once  NOALYSS_INCLUDE.'/class/acc_compute.class.php';
require_once  NOALYSS_INCLUDE.'/class/dossier.class.php';
require_once  NOALYSS_INCLUDE.'/class/acc_tva.class.php';
require_once NOALYSS_INCLUDE . '/class/noalyss_user.class.php';

require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
MaintenanceMode("block.html");


$http=new HttpInput();
// TVA id or TVA code
$t=$http->get("t");
// string  qcode card
$c=$http->get("c");
// Price
$p=$http->get("p");
// quantity
$q=$http->get("q");
// row number (from 0) used to identify the row
$n=$http->get("n");

$tax_ac_id=$http->request("other_tax_id","number",-1);
// sometime number uses coma instead of dot for dec
$p=noalyss_str_replace(",",".",$p);
$q=noalyss_str_replace(",",".",$q);

$cn=Dossier::connect();
$User=new Noalyss_user($cn);
$User->Check();
/**
 * check if 2FA is completed
 */
if ( ! $User->is_double_identified()) {
   exit();
}
$User->check_dossier(Dossier::id());

// Retrieve the rate of vat, it $t == -1 it means no VAT
if ( $t != -1  )
{
    $tva_rate=Acc_Tva::build($cn, $t);

    /**
     *if the tva_rate->load failed we don't compute tva
     */
    if ( $tva_rate->load() != 0 )
    {
        $tva_rate->set_parameter('rate',0);
    }
}

$total=new Acc_Compute();
bcscale(4);
if ( isNUmber($p) && isNumber($q)) {
    $amount=round(bcmul($p,$q,4),2);
} else {
    $amount = 0;
}
$total->set_parameter('amount',$amount);
$other_tax_amount=0;
if ( $tax_ac_id !=-1) {
    $other_tax=new Acc_Other_Tax_SQL($cn,$tax_ac_id);
    $other_tax_amount=round(bcmul($amount,$other_tax->getp("ac_rate"),4)/100,2);
}
if ( $t != -1   )
{
    $total->set_parameter('amount_vat_rate',$tva_rate->get_parameter('rate'));
    $total->compute_vat();
    if ($tva_rate->get_parameter('both_side')== 1) $total->set_parameter('amount_vat', 0);
    $tvac=($tva_rate->get_parameter('rate') == 0 || $tva_rate->get_parameter('both_side')== 1) ? $amount : bcadd($total->get_parameter('amount_vat'),$amount);

    header("Content-type: text/html; charset: utf8",true);
    $result=["ctl"=>$n,"htva"=>$amount,"tva"=>$total->get_parameter("amount_vat"),"tvac"=>$tvac,
        "other_tax"=>$other_tax_amount];
    echo json_encode($result);
}
else
{
    /* there is no vat to compute */
    header("Content-type: text/html; charset: utf8",true);
    $result=["ctl"=>$n,"htva"=>$amount,"tva"=>"NA","tvac"=>$amount,        "other_tax"=>$other_tax_amount];
    echo json_encode($result);
}
?>

