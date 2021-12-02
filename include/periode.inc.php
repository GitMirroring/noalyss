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
/*! \file
 * \brief add, modify, close or delete a period
 */
if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
$gDossier=dossier::id();
echo '<div class="content">';
$cn=Dossier::connect();
//-----------------------------------------------------
// Periode
//-----------------------------------------------------
$http=new HttpInput();

$p_ledger_id=$http->request("jrn_def_id", "number", 0);
?>
<script>
    var jsper = new Periode(<?php echo $p_ledger_id; ?>);
    jsper.set_callback("ajax_misc.php");
    jsper.set_js_obj_name("jsper");
    jsper.set_dossier('<?php echo Dossier::id(); ?>');

</script>
<?php
//-------------------------------------------------------------------
//Modify exercice label
//-------------------------------------------------------------------
if ( isset($_POST['mod_exercice_label_bt'])) {
    $err = 0;
    $p_exercice_label=$http->post("p_exercice_label");
    $p_exercice=$http->post("p_exercice");
    if (empty(trim($p_exercice_label))) {
     echo_warning(_("Libellé exercice ne peut pas être vide"));
     $err =1;
    } 
    if ($err == 0 && $cn->get_value("select count(*) from parm_periode where p_exercice_label=$1 and p_exercice <>$2",
            [$p_exercice_label,$p_exercice]) > 0) 
    {
        echo_warning(_("Le même libellé ne peut pas être utilisé pour 2 exercices"));
        $err=1;
    } 
    
    if ($err == 0) {
        try
        {
           $cn->start();
           /**
            * @todo
            * Rewrite : split table parm_periode into parm_periode and parm_exercice
            * disabling temporarily a trigger is not the right solution , 
            * but make the trigger more flexible and less secure
            * is worst. The trigger is disable in a transaction means it is still enable
            * for other session.
            */
           $cn->exec_sql("alter table parm_periode  disable trigger parm_periode_check_periode_trg");
           $cn->exec_sql("update parm_periode set p_exercice_label=$1 where p_exercice=$2",
                   [$p_exercice_label,$p_exercice]);
           $cn->exec_sql("alter table parm_periode  enable trigger parm_periode_check_periode_trg");
           $cn->commit();

        }
        catch (Exception $exc)
        {
            echo_warning( $exc->getMessage());
            error_log($exc->getTraceAsString());
            $cn->rollback();
        }

    }
}
//--------------------------------------------------------------------
// Add an exercice 
// receive nb_exercice
//--------------------------------------------------------------------
if (isset($_POST['add_exercice']))
{
    $obj=new Periode($cn);
    try
    {
        $p_exercice=$http->post("p_exercice", "number");
        $p_exercice_label=$http->post("p_exercice_label", "string");
        $p_year=$http->post("p_year", "number");
        $nb_month=$http->post("nb_month", "number");
        $from_month=$http->post("from_month", "number");
        $day_opening=$http->post("day_opening", "string", 0);
        $day_closing=$http->post("day_closing", "string", 0);
        $exercice=new Periode($cn);
        $exercice->insert_exercice($p_exercice, $p_year, $from_month, $nb_month,
                $day_opening, $day_closing,$p_exercice_label);
    }
    catch (Exception $ex)
    {
        echo_warning($ex->getMessage());
    }
}
//-------------------------------------------------------------------
// Select a ledger or global
//-------------------------------------------------------------------
echo '<form method="GET" >';
echo dossier::hidden();
$sel_jrn=$cn->make_array("select jrn_def_id, jrn_def_name from ".
        " jrn_def order by jrn_def_name");
$sel_jrn[]=array('value'=>0, 'label'=>_('Global : periode pour tous les journaux'));
$wSel=new ISelect();
$wSel->value=$sel_jrn;
$wSel->name='jrn_def_id';
$wSel->selected=$p_ledger_id;
echo _("Choisissez global ou uniquement le journal à fermer").$wSel->input();
echo HtmlInput::hidden('ac', $http->request('ac'));
// display a filter by exercice
echo _("Montrer l'exercice");
$max_exercice=$cn->get_value("select max(p_exercice) from parm_periode");
$p_exercice=$http->request("p_exercice_sel","string",$max_exercice);
Periode::filter_exercice($p_exercice);
$js_close_selected="jsper.close_selected()";

echo HtmlInput::submit('choose', 'Valider');
echo "</form>";

echo HtmlInput::button_action(_("Fermer les périodes sélectionnées"),
        $js_close_selected);

/*
 * Display all the periode for all ledgers
 */
if ($p_ledger_id==0)
{
    echo HtmlInput::button_action(_("Ajout exercice"),
            "\$('exercice_add').show()");
//-------------------------------------------------------------------
// Add a new Exercice
//-------------------------------------------------------------------
    echo '<div id="exercice_add" style="display:none" class="inner_box">';
    Periode::form_exercice_add();
    echo '</div>';
//-------------------------------------------------------------------
// Add a new Periode
//-------------------------------------------------------------------
    echo HtmlInput::button_action(_("Ajout période"), "\$('periode_add').show()");
    echo '<div id="periode_add" style="display:none;width:auto" class="inner_box">';
    Periode::form_periode_add("jsper");
    echo '</div>';
//-------------------------------------------------------------------
// Change label of Exercice
//-------------------------------------------------------------------
    echo HtmlInput::button_action(_("Modifie libellé exercice"), "\$('exercice_label_div').show()");
    echo '<div id="exercice_label_div" style="display:none;width:60ch" class="inner_box">';
    Periode::form_exercice_label("jsper");
    echo '</div>';    

//-------------------------------------------------------------------
// List of Periode
//-------------------------------------------------------------------
    $periode=new Parm_Periode_SQL($cn);
    Periode::display_periode_global("jsper");
}
else
{
    echo '<p class="info">'._("Pour ajouter, effacer ou modifier une période, il faut choisir global").'</p>';
    $ledger=new Acc_Ledger($cn, $p_ledger_id);
    echo h2($ledger->get_name());

    $periode_ledger=new Periode_Ledger_Table(0);
    $ret=$periode_ledger->get_resource_periode_ledger($p_ledger_id);
    $periode_ledger->display_table($ret, "jsper");
}

echo '</div>';
?>
<script>
    Periode.filter_exercice('periode_tbl');
    activate_checkbox_range("sel_per_close_ck");
</script>
