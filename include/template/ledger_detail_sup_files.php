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
 * @brief show supplemental files and let you add one
   @var $jr_id (int) JRN.JR_ID (inherited) 
// @var $cn (Database) current conx
// @var $gDossier (int) folder id
 */
?>
<div id="supplemental_doc_div<?php echo $div;?>" class="myfieldset noprint" style="display:<?php echo $a_tab['supplemental_doc_div']['display']?>">
    <div id="supplement_div_list<?=$div?>">
<?php
/**
 * show existing supplemental files for this operation
 */


$q=new Jrn_Sup_Document_SQL($cn);
$a_row=$q->collect_objects(" where jr_id=$1 order by js_cbc_id", [$jr_id]);
$javascript=sprintf("Supplement_Document.input_file('%s','%s','%s')",
        $gDossier,
        $div,
        $jr_id);
if ( count($a_row) > 0)
{
    foreach ($a_row as $item) {
        $export="export.php?";
        $script="Supplement_Document.delete_document('$gDossier','$div','{$item->js_id}','$jr_id')";
        $rowid=sprintf("row_js_%s_%s",$div,$item->js_id);
        // @var $download (url) to send file
        $download="export.php?". http_build_query(
                            [
                                "act"=>"RAW:suppl-document"
                                ,"js_id"=>$item->js_id
                                ,"gDossier"=>$gDossier
                            ]);
    ?>
    <div class="row" id="<?=$rowid?>">
        <div class="col">
            <a href="<?=$download?>" download>      <?=$item->js_filename?></a>
        </div>
        <div class="col">
            <?=$item->js_description?>
        </div>
        <div class="col">
            <?=\Icon_Action::trash(uniqid("sdd"),$script)?>
        </div>
    </div>
    <?php
    }// end foreach $a_row
}// end if count
//download ALL files from this operation
 $download="export.php?". http_build_query(
                            [
                                "act"=>"RAW:suppl-document"
                                ,"js_id"=>0
                                ,"gDossier"=>$gDossier
                                ,'operation_id'=>$jr_id
                            ]);

?>
    <a href="<?=$download?>" download=""><?=_("Télécharger tous les documents")?>
    </a>
    </div>
    <ul class="aligned-block">
        <li>
        <?=\HtmlInput::button_action(_("Ajout fichier"), $javascript);?>
        </li>
    </ul>
</div>