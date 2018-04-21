<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

if (!defined('ALLOWED'))     die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief input for currency_mtable
 * @see Currency_MTable
 */
?>
<table>
    <tr>
        <td>
            ISO
        </td>
        <td>
            <?php echo $cr_code_iso->input(); ?>
        </td>
    </tr>
    <tr>
        <td>
            <?php echo _("Nom")?>
        </td>
        <td>
            <?php echo $cr_name->input(); ?>
        </td>
    </tr>
</table>    

<table class="result">
    <tr>
    <th>
        <?php echo _("Date");?>
    </th>
    <th>
        <?php echo _("Valeur")?>
    </th>
    </tr>
    <?php
        $nb_histo=count($a_histo);
        for ($i=0;$i<$nb_histo;$i++):
    ?>
    <tr>
        <td>
            <?php echo $a_histo[$i]['str_from']?>
        </td>
        <td>
            <?php echo $a_histo[$i]['ch_value']?>
        </td>
    </tr>
    <?php    endfor; ?>
  <tr>
        <td>
            <?php echo _("Date"); ?>
        </td>
        <td>
            <?php echo $new_rate_date->input();?>
        </td>
    </tr>
    <tr>
      <td>
            <?php echo _("Valeur"); ?>
        </td>
        <td>
            <?php echo $new_rate_value->input();?>
        </td>
    </tr>
</table>
