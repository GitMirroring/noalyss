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
 * @brief
 *
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

require_once  NOALYSS_INCLUDE."/class/tax_summary.class.php";
require_once  NOALYSS_INCLUDE."/lib/noalyss_csv.class.php";
$http=new HttpInput();
$tax_summary = new Tax_Summary($cn,$http->get("date_start"),$http->get("date_end"));
try {
    $tax_summary->check();
}catch (Exception $e)
{
    if ($e->getCode() <> 100)  {
        echo $e->getMessage();
        return;
    }
}

$csv=new Noalyss_Csv("summary_tva");
$csv->send_header();
// Sale
$csv->write_header([
    _("Journal"),
    ("Code TVA"),
    _("Taux"),
    _("Montant HT"),
    _("Montant TVA"),
    _("Montant Autoliquidation")]);
$array=$tax_summary->get_row_sale();
$nb_array=count($array);
for ($i=0;$i < $nb_array;$i++) {
    $csv->add ($array[$i]['jrn_def_name']);
    $csv->add ($array[$i]['tva_label']);
    $csv->add ($array[$i]['tva_rate'],'number');
    $csv->add ($array[$i]['amount_wovat'],'number');
    $csv->add ($array[$i]['amount_vat'],'number');
    $csv->add ($array[$i]['amount_sided'],'number');
    $csv->write();
}
//Purchase
$csv->write_header([
    _("Journal"),
    ("Code TVA"),
    _("Taux"),
    _("Montant HT"),
    _("Privée"),
    _("Montant TVA"),
    _("Montant Autoliquidation"),
    _("Montant ND"),
    _("TVA Non Déd"),
    _("TVA Non Déd & récup")
    ]);
$array=$tax_summary->get_row_purchase();
$nb_array=count($array);
for ($i=0;$i < $nb_array;$i++) {
    $csv->add ($array[$i]['jrn_def_name']);
    $csv->add ($array[$i]['tva_label']);
    $csv->add ($array[$i]['tva_rate'],'number');
    $csv->add ($array[$i]['amount_wovat'],'number');
    $csv->add ($array[$i]['amount_private'],'number');
    $csv->add ($array[$i]['amount_vat'],'number');
    $csv->add ($array[$i]['amount_sided'],'number');
    $csv->add ($array[$i]['amount_noded_amount'],'number');
    $csv->add ($array[$i]['amount_noded_tax'],'number');
    $csv->add ($array[$i]['amount_noded_return'],'number');
    $csv->write();
}
