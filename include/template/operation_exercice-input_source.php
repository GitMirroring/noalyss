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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 6/01/24

/*! 
 * \file
 * \brief input the source of the opening / closing operation
 */

\Noalyss\Dbg::echo_file(__FILE__);

global $cn;
global $g_user;
global $g_parameter;

//-------------------------------------------------------------------------------------------
//
// Show opening
//-------------------------------------------------------------------------------------------

?>
<div class="content">

    <div class="row">
        <div class="col-lg">
            <FORM method="POST">

                <div class="" id="opening_div">
                    <h2 class="h-section"><?= _("Ouverture") ?></h2>
                    <div class="text-muted">
                        <?= _("Ouverture des comptes pour l'exercice qui débute pour les comptes 0 à 5.") ?>
                        <?= ("Choisissez l'exercice clôturé (exercice N-1) du dossier à reporter pour les a-nouveaux (exercice N)") ?>
                    </div>
                    <?php
                    /**************************************************************************
                     * Needed information :
                     *        -  Date of the first day of exercice
                     *        -  Folder
                     *        -  Exercice N-1
                     *************************************************************************/
                    $date = new IDate("date_opening");
                    $avail = $g_user->get_available_folder();


                    $array = array();
                    $i = 0;
                    foreach ($avail as $r) {
                        $array[$i]['value'] = $r['dos_id'];
                        $array[$i]['label'] = sprintf("%s %s (%s)",$r['dos_id'],$r['dos_name'],substr($r['dos_description']??"",0,50));
                        $i++;
                    }
                    $sAvail = new ISelect('dos_id');
                    $sAvail->id = "dos_id";
                    $sAvail->selected = Dossier::id();
                    $sAvail->value = $array;
                    $sAvail->javascript = sprintf('onchange="operation_exercice.update_periode(%d)"',Dossier::id());

                    $exercice = $cn->make_array("select distinct p_exercice,p_exercice_label from parm_periode order by p_exercice desc");
                    $sExercice = new ISelect("exercice");
                    $sExercice->id = "select_exercice_id";
                    $sExercice->value = $exercice;

                    echo HtmlInput::array_to_hidden(["gDossier","ac"],$_REQUEST);
                    echo HtmlInput::hidden("sa","opening");
                    ?>
                    <div class="form-group">
                        <label for="dos_id"><?= _("Depuis le dossier") ?></label>
                        <?= $sAvail->input() ?>
                    </div>
                    <div class="form-group">
                        <label for="dos_id"><?= _("Exercice N-1") ?></label>
                        <?= $sExercice->input() ?>
                    </div>
                    <?= \HtmlInput::submit("ope_submit", _("Valider")) ?>
                </div>
            </FORM>
<?php

//-------------------------------------------------------------------------------------------
// Show closing
//-------------------------------------------------------------------------------------------

?>
        </div>
        <div class="col-lg">

            <FORM method="POST">
                <div class="" id="closing_div" style="display:grid">
                    <h2 class="h-section">Clôture</h2>
                    <div class="text-muted">Clôture de l'exercice pour les comptes 6 à 7</div>

                </div>
                <?php
                echo HtmlInput::array_to_hidden(["gDossier","ac"],$_REQUEST);
                echo HtmlInput::hidden("sa","closing");
                $cl_exercice = $cn->make_array("select distinct p_exercice,p_exercice_label from parm_periode order by p_exercice desc");
                $sclExercice = new ISelect("exercice_cl");
                $sclExercice->id = "select_exercice_cl_id";
                $sclExercice->value = $cl_exercice;
                ?>
                <div class="form-group">
                    <label for="exercice_cl"><?= _("Exercice à clôturer") ?></label>
                    <?php echo $sclExercice->input(); ?>
                </div>
                <?= \HtmlInput::submit("ope_submit", _("Valider")) ?>
            </FORM>
        </div>

    </div>