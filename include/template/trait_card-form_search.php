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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file
 * @brief answer to an inplace object
 */
// var $g_user \Noalyss_User connected user
global $g_user;
// var $type_card = FICHE_TYPE_CLIENT, FICHE_TYPE_FOURNISSEUR,...
// var $cn Dossier connect to the current folder
$cn = Dossier::connect();

$http = new HttpInput();
?>
<form method="get" action="<?php echo $url; ?>">

    <h2 class="h-section"><?= sprintf("%s %s", _("Exercice"), $g_user->get_exercice()) ?></h2>

    <div style="display:flex">
        <div>
<?php
$a = $http->get("query", "string", "");
echo _("Cherche ") . HtmlInput::filter_table_form("tiers_tb", '0,1,2', 1, "query", $a);

$choice_cat = $http->request("choice_cat", "string", 1);
?>
        </div>
            <?php
            if ($choice_cat == 1) {
                $sel_card = new ISelect('cat');
                $sel_card->value = $cn->make_array('select fd_id, fd_label from fiche_def 
                 where  frd_id= $1 
                 order by fd_label ', 1, [$type_card]);
                $sel_card->selected = $http->get("cat", "number", -1);
                $sel_card->javascript = ' onchange="waiting_box();submit(this);"';
                echo '<div>';
                echo _('Catégorie :') . $sel_card->input();
                echo '</div>';
            } else {
                $cat = $http->request('cat', "string", '');
                echo HtmlInput::hidden("cat", $cat);
                echo HtmlInput::hidden('choice_cat', 0);
            }
            $nooperation = new ICheckBox('noop');
            $nooperation->selected = (isset($_GET['noop'])) ? true : false;
            echo Dossier::hidden();
            ?>
        <div>
        <?= _('Inclure ceux sans opération cette année') . $nooperation->input() ?>
        </div>
        <div>
            <input type="submit" class="button" name="submit_query" value="<?php echo _('recherche') ?>">
            <input type="hidden" name="ac" value="<?php echo $http->request('ac') ?>">
        </div>
    </div>
</form>

