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
 * \file
 * \brief display, add, delete and modify forecast
 */
if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

require_once NOALYSS_INCLUDE."/database/forecast_sql.class.php";
require_once NOALYSS_INCLUDE."/database/forecast_item_sql.class.php";
require_once NOALYSS_INCLUDE."/database/forecast_category_sql.class.php";


global $http;

$action=$http->get("action", "string", "");
$ac=$http->request("ac");
$forecast_id=$http->request('f_id', 'number',-1);

echo '<div class="content">';
$sa=$http->request("sa", "string", "list");
/* * ********************************************************************
 * Remove a anticipation
 *
 *
 * ******************************************************************** */
if ($action=='del')
{
    $f_id=$http->get("f_id", "number");
    $forecast=new Forecast_SQL($cn, $f_id);
    $forecast->delete();
}
/* * *******************************************************************
 * Cloning
 * ******************************************************************* */
if ($action=='clone')
{
    echo "<h2> cloning</h2>";
    /*
     * We need to clone the forecast
     */
    $f_id=$http->get("f_id", "number");
    $anti=new Anticipation($cn, $f_id);
    $forecast_id=$anti->object_clone();
    $action="mod_view";
}

/* * ********************************************************************
 * Save first the data for new
 *
 *
 * ******************************************************************** */
if ($sa=='new')
{
    try
    {
        $f_name=$http->post("f_name");
        $p_start=$http->post("p_start","number");
        $p_end=$http->post("p_end","number");
        if ( $f_name == "") {
            throw new Exception(_("Le nom ne peut pas être vide"));
        }
        $forecast_sql=new Forecast_SQL($cn);
        $forecast_sql->setp("f_name",$f_name);
        $forecast_sql->setp("f_start_date",$p_start);
        $forecast_sql->setp("f_end_date",$p_end);
        $forecast_sql->save();
        $action="mod_item";
        $forecast_id=$forecast_sql->getp("f_id");
        
    }
    catch (Exception $exc)
    {
        echo_warning($exc->getMessage());
        $sa="list";
    }
}

/* * ********************************************************************
 * If we request to modify the items
 *
 *
 * ******************************************************************** */
if ($action=='mod_item')
{

    /* Propose a form for the items
     */
    $anticipation=new Anticipation($cn, $forecast_id);
    $anticipation->input_form();



    return;
}
/* * ********************************************************************
 * if a forecast is asked we display the result
 *
 *
 * ******************************************************************** */
if ($sa=="vw")
{
    echo '<div class="content">';
    $forecast=new Anticipation($cn);
    

    $forecast->setForecastId($forecast_id);
    try
    {
        echo $forecast->display();
        echo '<div class="noprint">';
        echo '<form id="forecast_frm" method="get">';
        echo dossier::hidden();
        echo HtmlInput::hidden('action', '');
        echo HtmlInput::hidden('f_id', $forecast_id);
        echo HtmlInput::submit('mod_item_bt', _('Modifier éléments'), 'onclick="$(\'action\').value=\'mod_item\';"');
        //echo HtmlInput::submit('cvs',_('Export CVS'));
    
        echo HtmlInput::hidden('ac', $ac);
        $href=http_build_query(array('ac'=>$ac, 'gDossier'=>Dossier::id()));
        echo '<a style="display:inline" class="smallbutton" href="do.php?'.$href.'">'._('Retour').'</a>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        return;
    }
    catch (Exception $e)
    {
        echo "<div class=\"error\"><p>"._("Erreur")." : ".$e->getMessage().
        '</p><p>'._('Vous devez corriger').'</p></div>';
        $anc=new Anticipation($cn, $forecast_id);
        echo '<div class="content">';
        /* display a blank form for name and category */
        echo '<form method="post" action="?">';
        echo dossier::hidden();
        echo HtmlInput::hidden('sa', 'mod');
        echo HtmlInput::hidden('ac', $ac);
        echo $anc->form_cat();
        echo HtmlInput::submit('mod_cat_save', _('Sauver'));
        echo '</form>';
        echo '</div>';
    }
}
/* * ********************************************************************
 * Display menu
 *
 *
 * ******************************************************************** */
// display button add and list of forecast to display
if ($sa=='list')
{


    $aForecast=Forecast::load_all($cn);
    $menu=array();
    $get_dossier=dossier::get();
    require_once NOALYSS_TEMPLATE."/forecast-new.php";

    echo '<div class="content">';
    echo _('Filtre')." ".HtmlInput::filter_table("forecast_table_id", '0', 1);
    echo '<TABLE id="forecast_table_id" class="vert_mtitle">';
    $href="?ac=".$ac."&sa=new&".$get_dossier;
    echo '<TR><TD class="first"><A HREF="#" onclick="document.getElementById(\'forecast_new_div\').show()">'._("Ajout d'une prévision").'</A></TD></TR>';
    $forecast_id=$http->request('f_id', 'number', -1);

    for ($i=0; $i<count($aForecast); $i++)
    {
        $href="?ac=".$ac."&sa=vw&".$get_dossier.'&f_id='.$aForecast[$i]['f_id'];
        $name=h($aForecast[$i]['f_name']);
        $menu[]=array($href, $name, $name, $aForecast[$i]['f_id']);
        echo '<TR><TD><A HREF="'.$href.'">'.h($name).'</A></TD></TR>';
    }

    echo "</TABLE>";
    echo '</div>';
    return;
}
?>
</div>