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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 18/08/24
/*! 
 * \file
 * \brief display invoice for supplier or customer to pay, late or for today
 */
if ( empty($array)) {
    echo _("Aucune facture");
    return;
}
$p=0;
foreach ($array as $item):
    /**
     * item : jr_id, jr_internal,jr_date , jr_comment, jr_pj_number,jr_montant,jr_ech
     */
    $ech=\DateTime::createFromFormat("Y-m-d", $item['jr_ech']);

    $today=new \DateTime();
    if ( $aParam['time_limit'] == 'P' && $today->format('ymd')>$ech->format('ymd') )
        continue;
    $p++;
    $class=($p&1)?' odd ':'even';
?>
<div class="row <?=$class?>">
    <div class="col-2">
        <?=$ech->format('d.m.y')?>
    </div>
    <div class="col-2">
        <?=\HtmlInput::detail_op($item['jr_id'],$item['jr_pj_number'])?>
    </div>
    <div class="col">
        <?=h($item['jr_comment'])?>
        <?=h($item['jr_date'])?>
    </div>
    <div class="col-2" style="text-align: right">
        <?=nbm($item['jr_montant'],2)?>
    </div>
</div>

<?php
endforeach;
if ($p == 0) {
    echo _("Aucune facture");
    return;
}
?>

