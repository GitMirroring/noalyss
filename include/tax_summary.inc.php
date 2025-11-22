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

// Copyright Author Dany De Bontridder danydb@noalyss.eu
/**
 * @file
 * @brief display summary of tax (VAT) possible parameter is time range
 *
 */
$http = new HttpInput();
$limit = $g_user->get_limit_current_exercice();

$start = $http->request("start_date", "date", $limit[0]);
$end = $http->request("end_date", "date", $limit[1]);

$start_periode = new IDate("start_date", $start);
$end_periode = new IDate("end_date", $end);
echo '<div class="content">';
echo '<FORM METHOD="GET">';
echo HtmlInput::array_to_hidden(['gDossier','ac'],$_GET);
echo HtmlInput::hidden("do","display");
$select_tva=new ISelect("tva_type");
$select_tva->value=array(
    array("value"=>'O',"label"=>_("Opération")),
    array("value"=>'P',"label"=>_("Paiement")),
    array("value"=>'T',"label"=>_("TVA"))
);
$select_tva->selected=$http->get('tva_type','string','O');
printf(_("Calcul d'après la date"));
echo $select_tva->input();
printf(_("Période du %s au %s"),
    $start_periode->input(),
    $end_periode->input());

echo HtmlInput::submit("show_tax_summary",_("Afficher"));
echo '</FORM>';
echo '<hr>';
if ( $http->get("do","string","no") == "display")
{
    $tax_summary=new Tax_Summary($cn,$start_periode->value,$end_periode->value);
    $tax_summary->set_tva_type($select_tva->selected);
    try {
        try {
            $tax_summary->check();
        }catch (Exception $e)
        {
            echo '<span class="warning">';
            echo $e->getMessage();
            echo '</span>';

        }
        echo '<ul class="aligned-block">';
        echo '<li>';
        echo $tax_summary->form_export_csv();
        echo '</li>';
        echo '<li>';
        echo $tax_summary->form_export_pdf();
        echo '</li>';
        echo '</ul>';
        $tax_summary->display();
        echo '<ul class="aligned-block">';
        echo '<li>';
        echo $tax_summary->form_export_csv();
        echo '</li>';
        echo '<li>';
        echo $tax_summary->form_export_pdf();
        echo '</li>';
        echo '</ul>';

    } catch (Exception $e) {
        echo '<span class="warning">';
        echo $e->getMessage();
        echo '</span>';
    }
}
echo '</div>';