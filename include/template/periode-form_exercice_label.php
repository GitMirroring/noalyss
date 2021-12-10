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
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief Form to  modify an existing label
 */
?>
<?=HtmlInput::title_box(_("Modifie un libellé d'exercice"),'exercice_label_div','hide')?>
<form method="POST" id="exercice_label_frm" >
    <?php
        echo HtmlInput::array_to_hidden(["ac","gDossier"], $_REQUEST);
    ?>
    <p>
        <?=_("Exercice")?>
        <?php
        $ex=new Exercice($cn);
        echo $ex->select("p_exercice")->input();
        ?>
    </p>
    
    <p>
    <label for="p_exercice_label">
        <?=_("Libellé")?>
    </label>
    <?php
        $exercice_label=new IText("p_exercice_label");
        echo $exercice_label->input();
    ?>
    </p>
   <ul class="aligned-block">
    <li>
        <?php
        echo HtmlInput::submit("mod_exercice_label_bt", _("Modifie"));
       ?>
    </li>
    <li>
        <?php
        echo HtmlInput::button_hide('exercice_label_div');
       ?>
    </li>
    </ul>
</form>    
