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

// Copyright Author Dany De Bontridder danydb@noalyss.eu

/**
 * \file
 * \brief display, add, delete and modify forecast_item, All rows are in $a_row
 * \see Forecast_Item_MTable::input
 */
if ( $this->count_category() == 0 ) {
    echo span(_("Sans catégorie il n'est pas possible d'ajouter de nouveaux éléments"),'class="notice"');
    return;
}

$cn = $object->cn;
$forecast = new Forecast_SQL($cn, $this->get_forecast_id());
$forecast->load();
$str_name = $forecast->getp('f_name');
$str_start = $forecast->getp('f_start_date');
$str_end = $forecast->getp('f_end_date');

$aPeriode = $cn->make_array("select p_id,to_char(p_start,'MM.YYYY') as label from parm_periode
                                   where p_start >= (select p_start from parm_periode where p_id=$str_start)
                                    and p_end <= (select p_end from parm_periode where p_id=$str_end)
                                    order by p_start");
$aPeriode[] = array('value' => 0, 'label' => 'Mensuel');
$category = new ISelect("fc_id");
$category->value = $cn->make_array("select fc_id,fc_desc from 
                          forecast_category 
            where f_id=$1 order by 2",
    0, [$this->get_forecast_id()]);
$category->selected = $object->getp("fc_id");

$amount = new INum("fi_amount");
$amount->value = $object->getp("fi_amount");
$amount->value=($amount->value=="")?0:$amount->value;

$forecast_text = new IText("fi_text");
$forecast_text->value = $object->getp("fi_text");

/* Accounting*/
$account = new IPoste('fi_account');
$account->nb_row=3;
$account->extra=' style = "margin-left:0px;width:100%;" class="input_text"';
$account->set_attribute('account', 'fi_account');
$account->set_attribute('bracket', 1);
$account->set_attribute('no_overwrite', 1);
$account->set_attribute('noquery', 1);
$account->value = $object->getp("fi_account");
$account->size="80rem";
/*Quick Code */
$qc = new ICard("fi_card");
// If double click call the javascript fill_ipopcard
$qc->set_dblclick("fill_ipopcard(this);");

// name of the field to update with the name of the card
$qc->set_attribute('label', 'an_label');

// Type of card : all
$qc->set_attribute('typecard', 'all');
$qc->set_attribute('jrn', 0);
$qc->extra = 'all';

// when value selected in the autcomplete
$qc->set_function('fill_data');
if ($object->getp("fi_card") != "") {

    $qc->value = $cn->get_value("select ad_value 
    from fiche_detail 
    where
          ad_id=23 and f_id = $1", [$object->getp("fi_card")]);
}

$isDebit=new ISelect("fi_debit");
$isDebit->value=array(
        array("label"=>_("Crédit"),"value"=>'C'),
        array("label"=>_("Débit"),"value"=>'D')
    );
$isDebit->selected=$object->getp("fi_debit");

$isPeriode = new ISelect('fi_pid');
$isPeriode->value = $aPeriode;
$isPeriode->selected = $object->getp("fi_pid");
?>
<table style="width:50rem">
    <tr>
        <td><?= _("Categorie") ?></td>
        <td><?= $category->input(); ?></td>
    </tr>
    
    <tr>
        <td>
            <?= _("Periode") ?>
        </td>
        <td>
            <?= $isPeriode->input() ?>
        </td>
    </tr>
    
    <tr>
        <td><?= _("Intitulé") ?></td>
        <td><?= $forecast_text->input(); ?></td>
    </tr>
    <tr>
        <td>
            <?= _("Fiche") ?>
        </td>
        <td>
            <?= $qc->input(); ?><?=$qc->search()?>
            <span id="an_label"></span>
        </td>
    </tr>  
    <tr>
        <td><?= _("Positif") ?><?= Icon_Action::tips(_("Ne concerne que les fiches"))?></td>
        <td><?=$isDebit->input(); ?></td>
    </tr>
    <tr>
        <td>
            <?= _("Formule") ?>
           ( <a href="https://wiki.noalyss.eu/doku.php?id=tutoriaux:les_rapports#un_mot_d_explication" target="_blank">
                Aide</a> )
            
        </td>
        <td>
            <?= $account->input() ?>
        </td>
    </tr>
    <tr>
        <td><?= _("Montant") ?></td>
        <td><?= $amount->input(); ?></td>
    </tr>

</table>
