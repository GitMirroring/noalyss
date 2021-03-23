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
 * @brief form to enter a new Forecast
 */
$cn=Dossier::connect();
global $g_user;
$exercice=$g_user->get_exercice();
$t_periode=new Periode($cn);
list($per_max, $per_min)=$t_periode->get_limit($exercice);


$fc_name=new IText("f_name");
$fc_name->id="fc_name";
$is_start=new ISelect("p_start");
$is_start->value=$cn->make_array("select p_id,p_start from parm_periode order by p_start");
$is_start->selected=$per_max->p_id;
$is_end=new ISelect("p_end");
$is_end->selected=$per_min->p_id;
$is_end->value=$cn->make_array("select p_id,p_end from parm_periode order by p_end");
?>
<div id="forecast_new_div" class="inner_box" style="display: none;width:30rem;">

    <?= HtmlInput::title_box(_("Nouvelle prévision"), "forecast_new_div", "hide") ?>
    <form method="POST" style="padding:0px">
        <?php
        echo HtmlInput::hidden("sa", "new");
        echo HtmlInput::array_to_hidden(['f_id', 'ac', 'gDossier'], $_REQUEST);
        ?>

        <div class="form-group row">
            <div class="col">

                <label for="fc_name" class="form-group"><?= _("Nom") ?></label>
            </div>
            <div class="col">
                <input type="text" class="input_text" name="f_name" placeholder="<?= _("Nom") ?>">
            </div>

        </div>

        <div class="form-group row">
            <div class="col">

                <label for="p_start" class="form-group"><?= _("Début") ?></label>
            </div>
            <div class="col">

                <?= $is_start->input(); ?>

            </div>
        </div>

        <div class="form-group row">
            <div class="col">

                <label for="p_end" class="form-group"><?= _("Fin") ?></label>
            </div>
            <div class="col">

                <?= $is_end->input() ?>
            </div>
        </div>
        <input type="submit" class="smallbutton" value="<?= _("Sauver") ?>">
        <?= HtmlInput::button_hide("forecast_new_div") ?>

    </form>
</div>
