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
 * @brief Show the option of a contact, 
 * parameters received 
 *      - $fiche_id 
 *      - $ag_id 
 *      - $a_option array
 *               - $a_option.cor_id
 *               - $a_option.cor_label
 *               - $a_option.cor_type
 *               - $a_option.ap_id (-1 if option for this contact doesn't exist)
 * @see Card_Multiple
 */
?>
<h3 class="info" style="margin:1px">
    <?=$aIdentity['name']." ".$aIdentity['first_name']." ".$aIdentity['qcode']?>
</h2>
<form method="POST" onsubmit="save_linked_card_option(this);return false">
    <input type="hidden" name="op" value="card">
    <input type="hidden" name="op2" value="save_card_option">
    <input type="hidden" name="ctl" value="notused">
    <input type="hidden" name="ag_id" value="<?=$ag_id?>">
    <input type="hidden" name="f_id" value="<?=$fiche_id?>">
    <input type="hidden" name="action_person_id" value="<?=$p_action_person_id?>">
    <?= Dossier::hidden() ?>
    <?php
    $nb_option=count($a_option);
    for ($i=0; $i<$nb_option; $i++):
        switch ($a_option[$i]['cor_type'])
        {
            case 0:
                $input=new IText("ap_value[]");
                break;
            case 2:
                $input=new IDate("ap_value[]");
                break;
            case 1:
                $input=new INum("ap_value[]");
                break;
            case 3:
                $input=new ISelect("ap_value[]");
                $input->value=array();
                // for select , we need to take the list
                if (!empty($a_option[$i]['cor_value_select']))
                {
                    $a_select=explode("|", $a_option[$i]['cor_value_select']);
                    if (is_array($a_select))
                    {
                        $nb=count($a_select);
                        $array=[];
                        for ($e=0; $e<$nb; $e++)
                        {
                            $a["label"]=$a_select[$e];
                            $a["value"]=$a_select[$e];
                            $array[$e]=$a;
                        }
                        $input->value=$array;
                        $input->selected = $a_option[$i]['ap_value']  ;
                    }
                }
                break;
            case 4:
                $input=new InputSwitch("ap_value[]");
                if ( $a_option[$i]['ap_value'] =="") {
                    $a_option[$i]['ap_value']=0;
                }
                break;
                
        }
       $input->set_value ($a_option[$i]['ap_value']);
        echo '<span style="display:table-row">';
        echo '<span style="display:table-cell">';
        echo $a_option[$i]['cor_label'];
        echo HtmlInput::hidden("f_id",$fiche_id);
        echo HtmlInput::hidden("ap_id[]",$a_option[$i]['ap_id']);
        echo HtmlInput::hidden("cor_id[]",$a_option[$i]['cor_id']);
        echo '</span>';
        echo '<span style="display:table-cell">';
        echo $input->input();
        echo '</span>';
        echo '</span>';
        
    endfor;
    
    ?>
    <ul class="aligned-block">
        <li>
            <?=HtmlInput::button_close("d_linked_card_option")?>
        </li>
        <li>
            <?=HtmlInput::submit("save",_("Sauver"))?>
        </li>
        
    </ul>
        
        
</form>