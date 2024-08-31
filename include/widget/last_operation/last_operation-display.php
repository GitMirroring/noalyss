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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 11/08/24
/*! 
 * \file
 * \brief 
 */
?>
<table class="result">
    <?php
    $Ledger = new \Acc_Ledger($cn, 0);
    $last_ledger = array();
    $last_ledger = $Ledger->get_last(20);

    for ($i = 0; $i < count($last_ledger); $i++):
        $class = ($i % 2 == 0) ? ' class="even" ' : ' class="odd" ';
        ?>
        <tr <?php echo $class ?>>
            <td class="box">
                <?php echo \smaller_date($last_ledger[$i]['jr_date_fmt']) ?>
            </td>
            <td class="box">
                <?php echo $last_ledger[$i]['jr_pj_number'] ?>

            </td>
            <td class="box">
                <?php echo h(mb_substr($last_ledger[$i]['jr_comment'] ?? "", 0, 40, 'UTF-8')) ?>
            </td>
            <td class="box">
                <?php echo \HtmlInput::detail_op($last_ledger[$i]['jr_id'], $last_ledger[$i]['jr_internal']) ?>
            </td>
            <td class="num box">
                <?php echo nbm($last_ledger[$i]['jr_montant']) ?>
            </td>

        </tr>
    <?php endfor; ?>
</table>