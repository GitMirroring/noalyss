<?php
/*
 *   This file is part of NOALYSS.
 *    NOALYSS is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    NOALYSS is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with NOALYSS; if not, write to the Free Software
 *    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *    Copyright Author Dany De Bontridder danydb@noalyss.eu
 *
 */


/* !
 * \file
 * \brief Manage the colors and apparence of noalyss
 */
if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
global $g_user;
$http = new HttpInput();

?>
<div class="content">

    <form method="post">
        <?= dossier::hidden(); ?>
        <div class="row">
            <div class="col">
                <h2><?= _("Apparence") ?></h2>
                <?php
                $noalyss_appearance = new Noalyss_Appearance();
                $noalyss_appearance->load();
                if ($noalyss_appearance->from_post()) {
                    if (DEBUGNOALYSS > 1) {
                        echo "save appearance";
                    }
                    echo h2(_("Recharger la page pour voir la changements"),'class="notice"');
                    $noalyss_appearance->save();
                }
                ?>
               <?php
               echo $noalyss_appearance->input_form();
                ?>


            </div>
            <div class="col">
                <h2><?= _("Ecran") ?></h2>
                <img src="image/default-screen.png" width="100%">
            </div>
        </div>
        <div class="row">
            <div class="col">
        <?php
        echo HtmlInput::submit("record_company", _("Sauve"), "", "button");
        ?>
            </div>

        </div>

    </form>
</div>
