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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 7/01/24
/*! 
 * \file
 * \brief input data for one row of OPERATION_EXERCICE_DETAIL, inherited data $operation_detail_sql
 *
 * \see Operation_Exercice::input_row
 */
Noalyss\Dbg::echo_file(__FILE__);

$data = $operation_detail_sql->to_array();

if (DEBUGNOALYSS > 1) echo \Noalyss\Dbg::hidden_info("data", $data);

$poste = new \IPoste("oe_poste");
$poste->value = $data['oed_poste'];
$poste->id = 'oe_poste';
$poste->set_attribute('gDossier', Dossier::id());
$poste->set_attribute('jrn', 0);
$poste->set_attribute('account', 'oe_poste');
$label_id= uniqid('label');
$poste->set_attribute('label',$label_id);

$poste->dbl_click_history();


$card = new \ICard("qcode");
$card->value = $data['oed_qcode'];
$card->set_dblclick("fill_ipopcard(this);");
$card->set_attribute('label', "label");
$card->set_function('fill_data');
$card->javascript=sprintf(' onchange="fill_data_onchange(\'%s\');" ',    $card->name);

$label = new IText("label", $data['oed_label']);
$label->id=$label_id;
$label->size = 80;

$amount = new INum("amount", $data['oed_amount']);

$checkbox = new ICheckBox("debit");

$checkbox->value = 't';
$checkbox->selected =($data['oed_debit'] == 't')?true:false;

$checkbox->javascript='onclick="display_dcside(this)"';

echo \HtmlInput::title_box("", "operation_exercice_bx");
?>
<div>
    <form onsubmit="operation_exercice.save_row();return false;" id="operation_exercice_input_row_frm" method="POST">
        <?php
        echo Dossier::hidden();
        echo HtmlInput::hidden("ac", "OPCL");
        echo HtmlInput::hidden("row_id", $operation_detail_sql->oed_id);
        echo HtmlInput::hidden("op", 'operation_exercice+save_row');
        echo HtmlInput::hidden("oe_id", $operation_detail_sql->oe_id);
        ?>
        <div class="form-group">
            <label for="oe_poste"><?= _("Poste") ?></label>
            <?= $poste->input() ?>
        </div>
        <div class="form-group">
            <label for="qcode"><?= _("Fiche") ?></label>
            <?= $card->input() ?>
            <?= $card->search() ?>
        </div>
        <div class="form-group">
            <label for="oed_label"><?= _("Libellé") ?></label>
            <?= $label->input() ?>
        </div>
        <div class="form-group">
            <label for="amount"><?= _("Montant") ?></label>
            <?= $amount->input() ?>

            <?= $checkbox->input() ?>
            <label for="debit">
                <span id="txtdebit">
                    <?=($data['oed_debit']=="f")?_("Crédit"):_("Débit")?>
                </span>

            </label>
        </div>
        <ul class="aligned-block">
            <li>
                <?= \HtmlInput::submit("save", _("Sauve")) ?>
            </li>
            <li>
                <?= \HtmlInput::button_close("operation_exercice_bx") ?>
            </li>
            <li>
                <?=\HtmlInput::button_action(_("Effacer"), sprintf("operation_exercice.delete_row('%s')", $data['oed_id']))?>
            </li>
        </ul>

    </form>
</div>

