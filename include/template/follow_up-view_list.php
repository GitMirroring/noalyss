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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 13/02/24
/*! 
 * \file
 * \brief display list of followup
 */
global $cn;
$cn=$this->db;
$array=$cn->get_array($sql);
if (empty ($array)) {
    echo_warning("Aucun suivi ");
    return;
}
$idx=0;
echo \HtmlInput::filter_table("view_list_tb", "0,1,2,3,4,5", 1);
?>

<table class="result" id="view_list_tb">
    <thead>

    <tr>
        <th>Date</th>
        <th>Référence</th>
        <th>Titre</th>
        <th>QCODE</th>
        <th>Dernier commentaire</th>
        <th>Etat</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php
        foreach($array as $item) {
            $even=($idx%2==0)?'class="odd"':'class="even"';
            $idx++;
            ?>
            <tr <?=$even?>>
                <td><?=$item['my_date']?></td>
                <td>
                    <?php
                    echo \HtmlInput::detail_action($item['ag_id'],                     $item['ag_ref']);


                    ?>

                </td>
                <td><?=h($item['ag_title'])?></td>
                <td><?=h($item['qcode'])?></td>
                <td><?=$item['last_comment_date_str']?></td>
                <td><?=h($item['s_value'])?></td>
                <td>

                <?php
                if ($item['tags']!="") {
                    $aColor = explode(",", $item["tags_color"]);
                    $aTags = explode(",", $item["tags"]);
                    $nb_tag = count($aTags);
                    for ($x = 0; $x < $nb_tag; $x++) {
                        printf('<span style="font-size:75%%;padding:1px;border-color:transparent" class="tagcell tagcell-color%s">%s</span>', $aColor[$x], $aTags[$x]);
                        printf("&nbsp;");
                    } // end loop $x

                }
                ?>
                </td>
            </tr>
    <?php
        }

    ?>
    </tbody>
</table>
