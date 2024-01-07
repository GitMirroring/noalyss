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
 * \brief propose to transfer
 */
Noalyss\Dbg::echo_file(__FILE__);
$cn=Dossier::connect();
?>
<?=HtmlInput::button_action(_("Transfert"),"$('operation_exercice_transfer_info').update();$('oe_transfer_div').show()")?>
<div class="inner_box" id="oe_transfer_div" style="display:none;position: fixed;top:25%;overflow-scrolling: auto">
    <?= HtmlInput::title_box(_("Transfert à la comptabilité"), "oe_transfer_div") ?>
    <form method="POST" id="operation_exercice_transfer_frm" onsubmit="operation_exercice.transfer();return false;">
        <?php
        echo HtmlInput::array_to_hidden(array("ac", "gDossier"), $_REQUEST);
        echo HtmlInput::hidden("op", "operation_exercice+transfer");
        echo HtmlInput::hidden("oe_id", $this->operation_exercice_sql->oe_id);
        // show the ledger

        $ledger = new Acc_Ledger($cn, 0);
        $wLedger = $ledger->select_ledger('ODS', 2, FALSE);
        if ($wLedger == null)
            throw new Exception(_('Pas de journal disponible'));

        echo _("Journal destination"), $wLedger->input();

        ?>
        <div id="operation_exercice_transfer_info" ></div>
        <ul class="aligned-block">
            <li>
                <?= \HtmlInput::submit("save", _("Sauve")) ?>
            </li>
            <li>
                <?= \HtmlInput::button_hide("oe_transfer_div") ?>
            </li>

        </ul>
    </form>
</div>
