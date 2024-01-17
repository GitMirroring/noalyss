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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 11/01/24
/*! 
 * \file
 * \brief display draft  OPERATION_EXERCICE
 */
$http = new HttpInput();
$cn=Dossier::connect();
$a_operation = $cn->get_array("select oe_id
    ,to_char(oe_date,'DD.MM.YY') str_date
    ,to_char(oe_transfer_date,'DD.MM.YY') str_transfer_date
    ,to_char(tech_date,'DD.MM.YY HH24:MI') draft_date
    ,jr_internal
    ,tech_user
    ,oe_text 
    ,oe_type
 from operation_exercice order by oe_id desc");

$aUrl = ["ac" => $http->request("ac"), "gDossier" => Dossier::id()];
$checkbox=new ICheckBox("operation_list[]");
$checkbox->set_range("operation_range");
?>
<h2><?=_("Liste des opérations")?></h2>
<form method="POST">
    <?php
    echo \HtmlInput::array_to_hidden(["gDossier", "sa"], $_REQUEST);
    echo \HtmlInput::hidden("sa", "remove");
    ?>
<table class="result">
    <tr>
        <th>
            <?= _("Date brouillon") ?>
        </th>
        <th>
            <?= _("Date opération") ?>
        </th>
        <th>
            <?= _("Description") ?>
        </th>
        <th>
            <?= _("Utilisateur") ?>
        </th>
        <th>

            <?= _("Date transfert") ?>
        </th>
        <th><?=_("opération")?></th>
        <th>

        </th>
    </tr>
    <?php
    foreach ($a_operation as $operation) {
        $aUrl['operation_exercice_id'] = $operation['oe_id'];
        $aUrl['sa'] = $operation['oe_type'];
        $url = NOALYSS_URL . "/do.php?" . http_build_query($aUrl);
        $checkbox->value=$operation['oe_id'];
        $detail="";
        if ( ! empty ($operation['jr_internal'])) {
            $jr_id=$cn->get_value("select jr_id from jrn where jr_internal =$1",[$operation['jr_internal']]);
            if (!empty($jr_id))
                $detail=\HtmlInput::detail_op($jr_id,$operation['jr_internal']);
        }
        ?>
<tr>
    <td class=""><?=$operation['draft_date']?></td>
    <td class=""><?=$operation['str_date']?></td>
    <td class=""><?=$operation['oe_text']?></td>
    <td class=""><?=$operation['tech_user']?></td>
    <td class=""><?=$operation['str_transfer_date']?></td>
    <td class=""><?=$detail?></td>
    <td class=""><?=\Icon_Action::detail_anchor(uniqid(), $url)?></td>
    <td><?=$checkbox->input()?></td>
</tr>
        <?php
    }

    ?>
</table>
    <?=\HtmlInput::submit("remove",_("Efface la sélection"))?>
</form>
<?php
echo ICheckBox::javascript_set_range("operation_range");

