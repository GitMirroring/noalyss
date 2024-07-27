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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 26/07/24
/*! 
 * \file
 * \brief display result of tax detail
 *
 */

global $data;
echo HtmlInput::filter_table("detail_tva_id", "0,1,2,3,4,5,6,7,8,9,10,11", 1);
$aTotaux=array();
foreach (array('amount','vat_amount','amount_nd_tva','amount_nd_recup','autoreverse') as $i_tva) :
    $aTotaux[$i_tva]=0;
endforeach;
?>
<table class="result" id="detail_tva_id">
    <tr>
        <th>Date</th>
        <th>Pièce</th>
        <th>Fiche</th>
        <th>Poste</th>
        <th class="num">Base</th>
        <th class="text-center">code TVA</th>
        <th class="num">Taux</th>
        <th class="num">Montant TVA</th>
        <th class="num">Non déductible</th>
        <th class="num">récupérable par impôt</th>

        <th class="num">Autoliquidation</th>
    </tr>
<?php
$idx=0; bcscale(4);
foreach ($data as $item):
    $idx++;
    $class=($idx%2==0)?'even':'odd';
    $receipt_number=($item['jr_pj_number']=="")?$item['jr_internal']:$item['jr_pj_number'];
    $control=bcmul($item['tva_rate'],$item['j_montant'],4);
    $delta_control=bcsub($control,$item['vat_amount']);
 //   $delta_control=bcsub($delta_control,$item['qp_nd_tva_recup']);
  //  $delta_control=bcsub($delta_control,$item['qp_nd_tva']);
  /*  $delta_control=bcsub($delta_control,$item['qp_dep_priv']);*/
    $delta_control=round($delta_control,2);
    $w_amount="";
    if ( $delta_control != 0 ) {
        $w_amount=sprintf('<span tabindex="-1" style="color:red" class="icon">&#xe80e; diff. TVA calculée %s</span>',$delta_control);
    }
?>
<tr class="<?=$class?>">
    <td><?=$item['str_date']?></td>
    <td><?=HtmlInput::detail_op($item["jr_id"], $receipt_number)?></td>
    <td><?=$item['j_qcode']?></td>
    <td><?=$item['j_poste']?>
        <?=$w_amount?>
    </td>
    <td class="num"><?=nbm($item['j_montant'],2)?></td>
    <td class="text-center"><?=$item['tva_code']?></td>
    <td class="num"><?=nbm($item['tva_rate'],2)?></td>
    <td class="num"><?=nbm($item['vat_amount'],2)?></td>
    <td class="num"><?=nbm($item['qp_nd_tva'],2)?></td>
    <td class="num"><?=nbm($item['qp_nd_tva_recup'],2)?></td>
    <td class="num"><?=nbm($item['qp_vat_sided'],2)?></td>
    <?php
    $aTotaux['amount']=bcadd($aTotaux['amount'],$item['j_montant'],2);
    $aTotaux['vat_amount']=bcadd($aTotaux['vat_amount'],$item['vat_amount'],2);
    $aTotaux['amount_nd_tva']=bcadd($aTotaux['amount_nd_tva'],$item['qp_nd_tva'],2);
    $aTotaux['amount_nd_recup']=bcadd($aTotaux['amount_nd_recup'],$item['qp_nd_tva_recup'],2);

    $aTotaux['autoreverse']=bcadd($aTotaux['autoreverse'],$item['qp_vat_sided'],2);
    ?>
</tr>
<?php
endforeach;
?>
<tfoot>
<tr class="highlight">
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td class="num"><?=nbm($aTotaux['amount'],2)?></td>
    <td></td>
    <td></td>
    <td class="num"><?=nbm($aTotaux['vat_amount'],2)?></td>
    <td class="num"><?=nbm($aTotaux['amount_nd_tva'],2)?></td>
    <td class="num"><?=nbm($aTotaux['amount_nd_recup'],2)?></td>

    <td class="num"><?=nbm($aTotaux['autoreverse'],2)?></td>

</tr>
</tfoot>

</table>
