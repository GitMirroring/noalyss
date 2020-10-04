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
// Copyright Author Dany De Bontridder danydb@aevalys.eu
if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief Manage the tags
 *
 */
require_once NOALYSS_INCLUDE.'/class/tag.class.php';
require_once NOALYSS_INCLUDE.'/lib/single_record.class.php';
require_once NOALYSS_INCLUDE."/lib/html_tab.class.php";
require_once NOALYSS_INCLUDE."/lib/output_html_tab.class.php";
require_once NOALYSS_INCLUDE."/class/tag_group_mtable.class.php";

/* * *****************************************************************************
 *  Tags
 *
 * ***************************************************************************** */
$tabs=new Html_Tab("tg", _("Etiquette"));

ob_start();
$tag=new Tag($cn);
$uos=new Single_Record('tag');
if (isset($_POST['save_tag_sb']))
{
    if (!isset($_POST['remove']))
    {
        try
        {
            $uos->check();
            $tag->save($_POST);
            $uos->save();
        }
        catch (Exception $e)
        {
            alert("déjà sauvé");
        }
    }
    else
    {
        $tag->remove($_POST);
    }
}
?>
<div style="margin-left:5%;width:90%">
    <p class="info">
    <?php echo _("Vous pouvez utiliser ceci comme des étiquettes pour marquer des documents ou 
         comme des dossiers pour rassembler des documents. Un document peut appartenir
         à plusieurs dossiers ou avoir plusieurs étiquettes."); ?>
    </p>
<?php
$tag->show_list();
$js=sprintf("onclick=\"show_tag('%s','%s','%s','p')\"", Dossier::id(), $_REQUEST['ac'], '-1');
echo HtmlInput::button("tag_add", "Ajout d'un tag", $js);
?>
</div>
<?php
$tabs->set_content(ob_get_contents());
ob_end_clean();
/* * *****************************************************************************
 *  Group of Tags
 *
 * ***************************************************************************** */
$tag_group=new Html_Tab("grp_tg", _("Groupe étiquettes"));

$obj=new Tag_Group_SQL($cn);
$obj_manage=new Tag_Group_MTable($obj);
$obj_manage->set_callback("ajax_misc.php");
$obj_manage->add_json_param("op", "tag_group");
ob_start();
$obj_manage->display_table();
$r=ob_get_contents();
$tag_group->set_content($r);
ob_end_clean();


$out=new Output_Html_Tab();
$out->add($tabs);
$out->add($tag_group);
$out->output();
$obj_manage->create_js_script();
?>
<script>
    $('divtg').show();
    $('tabtg').className = 'tabs_selected';
    $('divgrp_tg').hide();
    $('tabgrp_tg').className = 'tabs';
    var o_tagGroup=new TagGroup();
</script>