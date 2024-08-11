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
 * \brief display last follow-up
 */
 ?>
 <table class="result" >
    <?php
    for ($i=0;$i < $len_array;$i++) :
    ?>
        <tr class=" <?php echo ($i%2==0)?'even':'odd'?>">
            <td class="box">
                <?php echo smaller_date($array[$i]['ag_timestamp_fmt']) ;?>
            </td>
            <td class="box">
                <?php echo HtmlInput::detail_action($array[$i]['ag_id'], $array[$i]['ag_ref'], 1)  ?>
            </td>
            <td class="box">
                <?php echo mb_substr(h($array[$i]['quick_code']),0,15)?>
            </td>
            <td class="box cut">
                <?php echo h($array[$i]['ag_title'])?>
            </td>
        </tr>
    <?php
    endfor;
    ?>
    </table>