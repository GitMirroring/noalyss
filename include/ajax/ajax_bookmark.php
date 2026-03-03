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

/**
 * @file
 * @brief user's bookmark
 */
/**
 * @var $cn Database inherited
 * @var $g_user User_Noalyss inherited
 */
if ( ! defined ('ALLOWED')) die('Appel direct ne sont pas permis');
echo HtmlInput::title_box(_("Favoris"), "bookmark_div");
if (! isset($_GET['ac'])) {
    /*
     * find default module
     */
    $_GET['ac']= find_default_module();
}
//-------------------------------------------------------------------------------------------
//
// Add bookmark
//-------------------------------------------------------------------------------------------
if (isset($_GET['bookmark_add'])){
    $count=$cn->get_value("select count(*) from bookmark"
            . " where b_action=$1 and login=$2",
            array($_GET['ac'],$g_user->login)
            );
    // Add bookmark only if absent
    if ( $count == 0 ){
        $cn->exec_sql("insert into bookmark(b_action,login) values($1,$2)",
            array($_GET['ac'],$g_user->login));
    } else {
        $js="error_message('"._("Ce favori a déjà été ajouté")."');";
        echo create_script($js);
    }     
}
//-------------------------------------------------------------------------------------------
// remove bookmark
//
//-------------------------------------------------------------------------------------------
if (isset($_GET['bookmark_delete']) && isset ($_GET['book'])){
    $a_book=$_GET['book'];
    for ($e=0;$e<count($a_book);$e++)
    {
        $cn->exec_sql("delete from bookmark where b_id=$1 and login=$2",
            array($a_book[$e],$g_user->login));
    }
}

$bookmark_sql="select distinct b_id,b_action,b_order,me_code,me_description, javascript"
        . " from bookmark "
        . "join v_menu_description_favori on (code=b_action or b_action=me_code)"
        . "where "
        . "login=$1 order by me_code";
$a_bookmark=$cn->get_array($bookmark_sql,array($g_user->login));
$url="do.php?gDossier=".Dossier::id();
//-------------------------------------------------------------------------------------------
// Display content
//-------------------------------------------------------------------------------------------
?>
<div class="content">
    <?=\HtmlInput::filter_table("bookmark_tb","1,2",0)?>
<form id="bookmark_del_frm" method="get" onsubmit="bookmark.remove();return false">
<?php    echo HtmlInput::array_to_hidden(array("gDossier",'ac'), $_REQUEST); ?>

    <table id="bookmark_tb" class="result">
        <?php for ($i=0;$i<count($a_bookmark);$i++): ?>
        <?php
        /*
         * Display only the last ac
         */
        $a_code=  explode('/',$a_bookmark[$i]['b_action']);
        $idx=count($a_code);
        $code=$a_code[$idx-1];
        ?>
        <tr class="<?php echo (($i%2)==0?'odd':'even')?>">
            <td>
                <?php
                    $ch=new ICheckBox('book[]');
                    $ch->value=$a_bookmark[$i]['b_id'];
                    echo $ch->input();
                ?>
            </td>
            <td>
                <a class='mtitle' style='text-decoration: underline' href="<?php echo $url."&ac=".$a_bookmark[$i]['b_action']; ?>">
                <?php echo $code  ?>
                </a>
            </td>
            <td>
                <?php echo $a_bookmark[$i]['me_description'] ?>
            </td>
        </tr>
        <?php endfor; ?>
    </table>
<?php
if ( count($a_bookmark) > 0) :
    echo HtmlInput::submit("bookmark_delete",_("Supprimez favoris sélectionnés"),"","smallbutton"); 
endif;
    ?>
</form>
<form id="bookmark_frm" method="get" onsubmit="bookmark.save();return false">
<?php
echo _("Menu actuel")." : ".hb($_GET['ac']);
echo HtmlInput::array_to_hidden(array("gDossier","ac"), $_REQUEST); 
?>

    <ul class="aligned-block ">
        <li>
            <?=\HtmlInput::button_hide("bookmark_div")?>
        </li>
        <li>
            <?php echo HtmlInput::submit("bookmark_add", _("Ajoutez le menu  actuel à vos favoris"),"","smallbutton"); ?>
            
        </li>
    </ul>
</form>
</div>
