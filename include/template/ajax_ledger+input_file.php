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
 * @brief display form to add supplemental files to an operation
 * var $gDossier (int) dossier
 * var $jr_id   (int)   jrn.jr_id operation
 * var $cn  (Database) database cnx
 * var $dgbox name of the dgbox
 * var $div is the DIV
 * supplement_div_list<?=$div?> list of files to update after uploading
 */

$dgbox=$http->request("dgbox");
$form_id="save_file{$div}";
$progress="progress{$div}";
$max_post_size=convert_ini_unit(ini_get("post_max_size"));

echo \HtmlInput::title_box(_("Ajout de fichier"), $dgbox);
?>
<div class="p-1">
    

<p>
    <?=_("Ajouter des fichiers à cette opération")?>
</p>
<FORM method="POST" enctype="multipart/form-data" onsubmit="return false" id="<?=$form_id?>">
    <input type="hidden" id="MAX_FILE_SIZE" name="MAX_FILE_SIZE" value="<?= MAX_FILE_SIZE ?>">
    <input type="hidden" id="post_max_size" name="post_max_size" value="<?= $max_post_size ?>">
    <?=\HtmlInput::hidden("jr_id",$jr_id)?>
    <?=\HtmlInput::hidden("gDossier",$gDossier)?>
    <?=\HtmlInput::hidden("op","ledger")?>
    <?=\HtmlInput::hidden("act","save_file")?>
    <?=\HtmlInput::hidden("div",$div)?>
    <?=\HtmlInput::hidden("dgbox",$dgbox)?>
    <input type="FILE" name="document_supplemental[]" id="doc_sup" multiple>
    <div id="feedback<?=$div?>"></div>
    <ul class="aligned-block">
        <li>
             <input type='SUBMIT' class="smallbutton" name="upload" value="<?=_("Sauve")?>" onclick="return Supplement_Document.save_file('<?=$form_id?>');">
        </li>
        <li>
            <?=\HtmlInput::button_close($dgbox)?>
        </li>
    </ul>
</FORM>
<progress style="height:auto;width: 100%;appearance: none;" id="progress_upload1b" max="100" value="0"></progress>
</div>