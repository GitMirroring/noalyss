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

\Noalyss\Dbg::echo_file(__FILE__);
$sup_parameter=HtmlInput::array_to_string(["sc","sb","f_id","qcode"], $_REQUEST,"&amp;");
$cn=Dossier::connect();
/**
 * @file
 * @brief display a button for adding an followup action
 */
global $g_user;
if ( ! empty ($g_user->get_writable_profile())) 
{
    echo HtmlInput::button(uniqid(), _("Ajout action"),
        'onclick="document.getElementById(\'action_add_d\').style.display=\'block\';"');
}
?>
<div id="action_add_d" class="inner_box" style="position:fixed;min-width:25rem;display:none;top:10rem;">
    <?php echo HtmlInput::title_box(_("Choississez une action"), "action_add_d"); ?>
    <div style="text-align: center">
<?=\Noalyss\Dbg::echo_file(__FILE__)?>
        <form method="get" >

            <?php
            echo HtmlInput::array_to_hidden(["ac", "sc", "sb", "f_id", "qcode", "gDossier"], $_REQUEST);
            if ( ! empty ($pa_param)) {
                foreach ($pa_param as $key=> $value)
                {
                    echo HtmlInput::hidden($key,$value);

                }
            }
            ?>
            
            <?php
            $action=new ISelect("action_type");
            $action->style='style="width:95%"';
            $action->value=$cn->make_array("select dt_id,dt_value from document_type order by 2");
            $action->selected=$action->value[0]['value'];
            $action->rowsize=10;
            echo $action->input();
            ?>
            <ul class="aligned-block">

                <li>
                    <?php echo HtmlInput::submit("add_action", _("Ajout")); ?>
                </li>
                <li>
                    <?php echo HtmlInput::button_hide("action_add_d") ?>
                </li>
            </ul>
        </form>
    </div>
</div>
<?php
include "show-all-variable.php";
