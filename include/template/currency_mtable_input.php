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
            <?php   $error=$this->get_error("cr_code_iso");
                echo HtmlInput::errorbulle($error);
            ?>
        </td>
        <td>
            <?php echo $cr_code_iso->input(); ?>
        </td>
    </tr>
    <tr>
        <td>
            <?php echo _("Nom");
            $error=$this->get_error("cr_name");
            echo HtmlInput::errorbulle($error);
            ?>
        </td>
        <td>
            <?php echo $cr_name->input(); ?>
        </td>
    </tr>
</table>    
<?php
    // Variable
    $dossier_id=Dossier::id();
    
?>
<table class="result" id="currency_rate_table">
    <tr>
    <th>
        <?php echo _("Date");
        
        ?>
    </th>
    <th>
        <?php echo _("Valeur");
        
            ?>
    </th>
    <th>
        
    </th>
    </tr>
    <?php
        $nb_histo=count($a_histo);
        for ($i=0;$i<$nb_histo;$i++):
            $class=($i%2==0)?"even":"odd";
    ?>
    <tr class="<?=$class?>" id="<?php printf('currency_rate_%d',$a_histo[$i]['id'])?>">
        <td>
            <?php echo $a_histo[$i]['str_from']?>
        </td>
        <td>
            <?php echo $a_histo[$i]['ch_value']?>
        </td>
        <td>
            <?php
            // Delete the histo
            $js=sprintf("CurrencyRateDelete('%s','%s')",$dossier_id,$a_histo[$i]['id']);
            echo Icon_Action::trash($a_histo[$i]['id'], $js);
            ?>
        </td>
    </tr>
    <?php    endfor; ?>
  <tr>
        <td>
            <?php echo _("Date"); 
             $error=$this->get_error("str_from");
            echo HtmlInput::errorbulle($error);
            ?>
        </td>
        <td>
            <?php echo $new_rate_date->input();?>
        </td>
    </tr>
    <tr>
      <td>
            <?php echo _("Valeur");  
            $error=$this->get_error("ch_value");
            echo HtmlInput::errorbulle($error);?>
        </td>
        <td>
            <?php echo $new_rate_value->input();?>
        </td>
    </tr>
</table>
