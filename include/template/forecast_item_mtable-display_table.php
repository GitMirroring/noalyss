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
 * \see Forecast_Item_MTable
 */

echo HtmlInput::filter_table("tb" . $this->object_name, '0,1,2,3,4', 1);
?>
<table id="<?= "tb" . $this->object_name ?>" class="result">
    <thead>
    <tr>

        <th>
            <?= _("Catégorie") ?>
        </th>
        <th>
            <?= _("Texte") ?>
        </th>
        <th>
            <?= _("Formule") ?>
        </th>
        <th>
            <?= _("Montant") ?>
        </th>
        <th>
            <?= _("Période") ?>
        </th>
        <th></th>
    </tr>
    </thead>
    <tbdoy>
        <?php
        $nb_row = count($a_row);
        for ($i = 0; $i < $nb_row; $i++):
            $class = ($i % 2 == 0) ? "even" : "odd";
            $this->display_row($a_row[$i]);
        endfor; // end for i
        ?>
    </tbdoy>
</table>
<?php
$this->create_js_script();
?>
<?php
echo HtmlInput::button_action(" " . _("Ajout"),
    sprintf("%s.input('-1','%s')",
        $this->object_name,
        $this->object_name), "xx", "smallbutton", BUTTONADD);
?>
<script>alternate_row_color('<?="tb" . $this->object_name?>')</script>
