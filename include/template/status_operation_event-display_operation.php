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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 14/04/23
/*! 
 * \file
 * \brief 
 */

?>
<?php
echo HtmlInput::title_box($p_title, $this->dialog_box_id,"close","","y")
?>

        <?php if (count($p_array)>0) :?>
            <table class="result">
                <tr>
                    <th><?php echo _('Date')?></th>
                    <th><?php echo _('Code Interne')?></th>
                    <th><?php echo _('Pièce')?></th>
                    <th><?php echo _('Description')?></th>
                    <th>
                        <?php echo _('Montant')?>
                    </th>

                </tr>
                <?php
                for ($i=0;$i<count($p_array);$i++):
                    ?>
                    <tr class="<?php echo (($i%2)==0)?'odd':'even';?>">
                        <td>
                            <?php echo smaller_date(format_date($p_array[$i]['jr_date']) );?>
                        </td>
                        <td>
                            <?php echo HtmlInput::detail_op($p_array[$i]['jr_id'], $p_array[$i]['jr_internal']) ?>
                        </td>
                        <td>
                            <?php echo h($p_array[$i]['jr_pj_number'])?>
                        </td>
                        <td>
                            <?php echo h($p_array[$i]['jr_comment']) ?>
                        </td>
                        <td>
                            <?php echo nbm($p_array[$i]['jr_montant']) ?>
                        </td>
                    </tr>
                <?php
                endfor;
                ?>
            </table>

        <?php else: ?>
            <h2 class="notice"><?php echo _('Aucune donnée')?></h2>
        <?php
        endif;
        ?>
            <ul class="aligned-block">
                <li>
                    <?=\HtmlInput::button_action(_("Rafraîchir"),sprintf("event_display_detail('%s','%s')",Dossier::id(),$p_what),uniqid(),"smallbutton")?>
                </li>
                <li>
                    <?php echo HtmlInput::button_close($this->dialog_box_id)?>

                </li>
            </ul>
