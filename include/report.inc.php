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

 */
// Copyright Author Dany De Bontridder danydb@aevalys.eu
/*! \file
 * \brief handle your own report: create or view report
 */
if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
require_once  NOALYSS_INCLUDE.'/lib/ac_common.php';
require_once  NOALYSS_INCLUDE.'/lib/user_menu.php';
require_once NOALYSS_INCLUDE.'/lib/ifile.class.php';
require_once NOALYSS_INCLUDE.'/lib/ibutton.class.php';
require_once NOALYSS_INCLUDE.'/class/acc_report.class.php';
require_once NOALYSS_INCLUDE.'/class/acc_report_mtable.class.php';

require_once NOALYSS_INCLUDE.'/class/dossier.class.php';
require_once NOALYSS_INCLUDE.'/class/database.class.php';
require_once  NOALYSS_INCLUDE.'/class/user.class.php';
require_once NOALYSS_INCLUDE.'/lib/ipopup.class.php';
global $http;

$gDossier=dossier::id();
$str_dossier=dossier::get();
$http=new HttpInput();

/* Admin. Dossier */
$rep=Dossier::connect();


$cn=Dossier::connect();

$rap=new Acc_Report($cn);
$menu=0;
if (isset($_POST["del_form"]))
{
    $rap=$rap=new Acc_Report($cn,$http->post("fr_id","number"));
    $rap->delete();
    $menu=1;
}



if (isset($_REQUEST["action"]) && $menu == 0)
{

    $action=$http->request("action");
    $rap->id=$http->request('fr_id',"number",0);

    if ($action=="add"&&!isset($_REQUEST['fr_id']))
    {

        echo '<DIV class="content">';
        echo '<h1>'._('Définition').'</h1>';
        echo '<form method="post" >';
        echo dossier::hidden();
        $form_definition=new Form_Definition_SQL($cn);
        /* name cannot be empty */
        $name=$http->post("fr_name");
        $name=(trim($name==""))?"auto-".date('d.m.Y H:I'):$name;
        $form_definition->setp("fr_label",$name);
        
        $form_definition->save();
        /** if there is file ($_FILES['report']) then import first */
        $rap=new Acc_Report($cn);
        $rap->set_form_definition($form_definition);
        $rap->upload();
        
        echo '<DIV class="content">';
        $iName=$rap->input_name($name);
        echo '<h3>';
        echo $iName->input();
         echo '</h3>';
        
        $acc_report_mtable=Acc_Report_MTable::build(0, $form_definition->getp("fr_id"));
        $acc_report_mtable->create_js_script();
        echo $acc_report_mtable->display_table();
        
        
        echo "</DIV>";
    }
    if ($action=="view" || $action == "record")
    {
        echo '<DIV class="content">';
        $id=$http->request("fr_id","number");
        $form_definition=new Form_Definition_SQL($cn,$id);
        $name=$form_definition->getp("fr_label");
        $rap=new Acc_Report($cn);
        $rap->set_form_definition($form_definition);
        $iName=$rap->input_name($name);
        echo '<h3>';
        echo  $iName->input();
        echo '</h3>';
        
        $acc_report_mtable=Acc_Report_MTable::build(0, $id);
        $acc_report_mtable->create_js_script();
        echo $acc_report_mtable->display_table();
        
        echo '<form method="get"  action="export.php" style="display:inline" >';
        echo dossier::hidden();
        echo HtmlInput::hidden("act", "CSV:reportinit");
        echo HtmlInput::hidden('f',$id);
        echo HtmlInput::submit('bt_csv', "Export CSV");
        echo HtmlInput::request_to_hidden(array('ac', 'action', 'p_action', 'fr_id'));
        $href=http_build_query(array('ac'=>$http->request('ac'),'gDossier'=>$gDossier));
        
        echo '</form>';
        echo '<form style="display:inline" method="post" id="del_form_frm" onsubmit="return confirm_box(\'del_form_frm\',content[47])">';
        echo HtmlInput::request_to_hidden(array('ac',  'fr_id'));
        echo HtmlInput::hidden("del_form","1");
        echo HtmlInput::submit(uniqid(), _('Efface'));
        echo '</form>';
        echo '<a style="display:inline" class="button" href="do.php?'.$href.'">'._('Retour').'</a>';  
        echo "</DIV>";
    }
}
else
{
    $rap->create();
    $lis=$rap->get_list();
    $ac="&ac=".$http->request('ac');
    $p_action='p_action=defreport';
    echo '<div class="content">';
   echo _('Cherche')." ".HtmlInput::filter_table("rapport_table_id", '0', 1);

    echo '<TABLE id="rapport_table_id" class="vert_mtitle">';
    echo '<TR><TD class="first">';
    echo '<a href="#" onclick="document.getElementById(\'acc_report_create_div\').style.display=\'block\'">'
            ._("Ajout")
            .'</A></TD></TR>';

    foreach ($lis as $row)
    {
        printf('<TR><TD><A  HREF="?'.$p_action.$ac.'&action=view&fr_id=%s&%s">%s</A></TD></TR>', $row->id, $str_dossier, $row->name);
    }
    echo "</TABLE>";
    echo '</div>';
}
html_page_stop();
?>

