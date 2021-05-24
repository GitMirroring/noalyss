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
 * @brief display a form to enter a new Report definition
 */
$http=new HttpInput();
?>
<div id="acc_report_create_div" class="inner_box" style="display:none">
<?=HtmlInput::title_box(_("Nouveau rapport"), "acc_report_create_div","hide")?>
<form method="POST" enctype="multipart/form-data" >
    <?=HtmlInput::hidden("ac", $http->request("ac"))?>
    <?=HtmlInput::hidden("action", "add")?>
    <?=Dossier::hidden()?>
    <label for="fr_name"><?=_("Nom")?></label>
    <input type="text" name="fr_name" autofocus class="input_text" required>
    <?php
        echo '<h3> Importation</h3>';
         $wUpload=new IFile();
        $wUpload->name='report';
        $wUpload->value='report_value';
        echo _('Importer ce rapport').' ';
        echo $wUpload->input();
    ?>
    <ul class="aligned-block">
        <li><?=HtmlInput::submit(uniqid(),_("Sauver"))?></li>
        <li><?=HtmlInput::button_hide("acc_report_create_div")?></li>
    </ul>
</form>
</div>