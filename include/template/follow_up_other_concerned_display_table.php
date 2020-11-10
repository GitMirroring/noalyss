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
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief  display a table
 */
$aColumn = $this->get_option();
$r='0,1,2';
 $nb_column=count($aColumn);
for ( $e=0;$e<$nb_column;$e++) { 
    $r.=','.($e+3);
    
}
?>
<?=HtmlInput::filter_table("action_concerned_list_tb1", $r, 1);?>
<table id="action_concerned_list_tb1" class="result">
    <tr>
       
        <th>
            <?=_("Code")?>
        </th>
        <th>
            <?=_("Nom")?>
        </th>
        <th>
            <?=_("Prénom")?>
        </th>
        <?php 
       
        for ($o=0;$o< $nb_column;$o++){
            echo th($aColumn[$o]["cor_label"]);
        }
        ?>
    <?php
        $aOther=$this->db->get_array("
            select (select ad_value from fiche_detail fd1 where fd1.f_id=ap.f_id and ad_id=23) as qcode ,
            f_id
            ,ap.ap_id
                from action_person ap 
                join action_gestion on (ap.ag_id=action_gestion.ag_id)
                where ap.ag_id=$1 order by 1",[$this->ag_id]);
        $nb_other=count($aOther);
        for ($x=0;$x<$nb_other;$x++) {
            
            echo $this->display_row($aOther[$x]['f_id'],$x,$aColumn);
        }
    ?>
    </tr>
</table>