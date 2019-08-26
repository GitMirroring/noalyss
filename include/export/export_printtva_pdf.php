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
 * @brief export PDF
 *
 */
if (!defined('ALLOWED')) die('Appel direct ne sont pas permis');
require_once NOALYSS_INCLUDE . "/class/tax_summary.class.php";
require_once NOALYSS_INCLUDE . '/class/user.class.php';
require_once NOALYSS_INCLUDE . '/class/pdf_land.class.php';
require_once NOALYSS_INCLUDE . '/lib/http_input.class.php';
bcscale(4);
$http = new HttpInput();

$from_periode = $http->request("date_start");
$to_periode = $http->request("date_end");


$gDossier = dossier::id();

/* Security */
$cn = Dossier::connect();
$tax_summary = new Tax_Summary($cn, $from_periode, $to_periode);
$pdf = new PDFLand($cn);
$pdf->setDossierInfo(sprintf(_("Date") . " : %s %s", $from_periode, $to_periode));
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAuthor('NOALYSS');
$pdf->setTitle(_("Résumé TVA"), true);




//-------------------------------------------------------------------------
// Sales
//-------------------------------------------------------------------------
$a_sale_header = [_("Code TVA"), _("Taux"), _("Montant HT"), _("Montant TVA"),
    _("Montant Autoliquidation")];
$nb_sale = count($a_sale_header);
$array = $tax_summary->get_row_sale();
// Table header
$nb_array = count($array);
$tot_vat = 0;
$tot_wovat = 0;
$tot_sided = 0;
$ledger_name = "";
//-------------------------------------------------------------------------
// For each sale ledger
//-------------------------------------------------------------------------
// Table with total
$a_col=['tva_label','tva_rate','amount_wovat','amount_vat','amount_sided'];
$nb_col=count($a_col);
$a_tot=[];
// initialize totals
for ($e=2;$e<$nb_col;$e++){
    $a_tot[$a_col[$e]]=0;
}

for ($i = 0; $i < $nb_array; $i++) {
    for ($e=2;$e<$nb_col;$e++){
        $colname=$a_col[$e];
        $a_tot[$colname]=bcadd($a_tot[$colname],$array[$i][$colname]);
    }

    if ($i == 0) {
        // Display ledger name

        $ledger_name = $array[$i]['jrn_def_name'];
        $pdf->SetFont('DejaVuCond', 'B', 10);
        $pdf->write_cell(50, 8, $ledger_name);
        $pdf->line_new();

        // Display Header
        $pdf->SetFont('DejaVuCond', 'B', 7);
        for ($e=0;$e<$nb_col;$e++){
            if ( $e == 0 ) {
                $pdf->write_cell(40, 5,$a_sale_header[$e], 1, 0, 'L');
            } else {
                 $pdf->write_cell(40, 5,$a_sale_header[$e], 1, 0, 'R');
            }
            $a_tot[$colname]=0;
        }
        $pdf->line_new();
        $pdf->SetFont('DejaVuCond', '', 7);
    }
    if ($ledger_name != $array[$i]['jrn_def_name']) {
        // Display totals
        $pdf->SetFont('DejaVuCond', 'B', 7);
        $pdf->line_new();
        $pdf->write_cell(70, 5, "");
        for ($e=2;$e<$nb_col;$e++){
            $colname=$a_col[$e];
            $pdf->write_cell(40, 5, nbm($a_tot[$colname]), 0, 0, 'R');
            $a_tot[$colname]=0;
        }
        $pdf->line_new();
        $pdf->SetFont('DejaVuCond', 'B', 7);

        // Display ledger name
        $ledger_name = $array[$i]['jrn_def_name'];
        $pdf->SetFont('DejaVuCond', 'B', 10);
        $pdf->write_cell(50, 8, $ledger_name);
        $pdf->line_new();

        // Display Header
        for ($e=0;$e<$nb_col;$e++){
            if ( $e == 0 ) {
                $pdf->write_cell(40, 5,$a_sale_header[$e], 1, 0, 'L');
            } else {
                $pdf->write_cell(40, 5,$a_sale_header[$e], 1, 0, 'R');
            }
            $a_tot[$colname]=0;
        }
    }
    for ($e=0;$e<$nb_col;$e++){
        $colname=$a_col[$e];
        if ( $e ==0 ) {
            $pdf->write_cell(40, 5, $array[$i][$colname], 1, 0, 'L');
        } elseif ($e == 1 ){
            $pdf->write_cell(40, 5, nbm($array[$i][$colname]*100), 1, 0, 'R');
        }else {
            $pdf->write_cell(40, 5, nbm($array[$i][$colname]), 1, 0, 'R');
        }
    }
    $pdf->line_new();
}
$pdf->SetFont('DejaVuCond', 'B', 7);
$pdf->write_cell(80, 5, "");
for ($e=2;$e<$nb_col;$e++){
    $colname=$a_col[$e];
    $pdf->write_cell(40, 5, nbm($a_tot[$colname]), 1, 0, 'R');
}

//-------------------------------------------------------------------------
// Summary sales
//-------------------------------------------------------------------------
$a_sum = $tax_summary->get_summary_sale();
$pdf->line_new();
$pdf->SetFont('DejaVuCond', 'B', 10);
$pdf->write_cell(50, 8, _("Résumé TVA vente"));
$pdf->line_new();

$pdf->SetFont('DejaVuCond', 'B', 7);
for ($i = 0; $i < $nb_sale; $i++) {
    if ($i > 0 ) {
        $pdf->write_cell(40, 5, $a_sale_header[$i], 1, 0, 'R');
    } else {
        $pdf->write_cell(40, 5, $a_sale_header[$i], 1, 0, 'L');
    }
}
$pdf->line_new();

$nb_array = count($array);
// initialize totals
for ($e=2;$e<$nb_col;$e++){
    $a_tot[$a_col[$e]]=0;
}
$pdf->SetFont('DejaVuCond', '', 7);

for ($i = 0; $i < $nb_array; $i++) {
    // display each row
    for ($e=0;$e<$nb_col;$e++){
        $colname=$a_col[$e];
        if ( $e ==0 ) {
            // first column TVA_LABEL
            $pdf->write_cell(40, 5, $array[$i][$colname], 1, 0, 'L');
        } elseif ($e == 1 ){
            // Secund col. tva rate
            $pdf->write_cell(40, 5, nbm($array[$i][$colname]*100), 1, 0, 'R');
        }else {
            // Other cols,display amount and compute total
            $a_tot[$colname] = bcadd($a_tot[$colname], $array[$i][$colname]);
            $pdf->write_cell(40, 5, nbm($array[$i][$colname]), 1, 0, 'R');
        }
    }
    $pdf->line_new();
}
$pdf->SetFont('DejaVuCond', 'B', 7);
$pdf->write_cell(80, 5, "");
for ($e=2;$e<$nb_col;$e++){
    $colname=$a_col[$e];
    $pdf->write_cell(40, 5, nbm($a_tot[$colname]), 1, 0, 'R');
}
$pdf->line_new();

//------------------------------------------------------------------------------------------
// Purchase
//------------------------------------------------------------------------------------------
$array = $tax_summary->get_row_purchase();
$a_colp=['tva_label','tva_rate','amount_wovat','amount_private','amount_vat','amount_sided',
    'amount_noded_amount','amount_noded_tax','amount_noded_return'];
$a_purchase_header = [_("Code TVA"), _("Taux"), _("Montant HT"), _("Privée"), _("Montant TVA"),
    _("Montant Autoliquidation"), _("Montant Non Déd"), _("TVA Non Déd"), _("TVA Non Déd & récup")];
$nb_purchase = count($a_purchase_header);

for ($e=2;$e<$nb_col;$e++){
    $a_tot[$a_col[$e]]=0;
}

for ($i = 0; $i < $nb_array; $i++) {
    for ($e=2;$e<$nb_col;$e++){
        $colname=$a_col[$e];
        $a_tot[$colname]=bcadd($a_tot[$colname],$array[$i][$colname]);
    }

    if ($i == 0) {
        // Display ledger name

        $ledger_name = $array[$i]['jrn_def_name'];
        $pdf->SetFont('DejaVuCond', 'B', 10);
        $pdf->write_cell(50, 8, $ledger_name);
        $pdf->line_new();

        // Display Header
        $pdf->SetFont('DejaVuCond', 'B', 7);
        for ($e=0;$e<$nb_col;$e++){
            if ( $e == 0 ) {
                $pdf->write_cell(40, 5,$a_purchase_header[$e], 1, 0, 'L');
            } else {
                $pdf->write_cell(40, 5,$a_purchase_header[$e], 1, 0, 'R');
            }
            $a_tot[$colname]=0;
        }
        $pdf->line_new();
        $pdf->SetFont('DejaVuCond', '', 7);
    }
    if ($ledger_name != $array[$i]['jrn_def_name']) {
        // Display totals
        $pdf->SetFont('DejaVuCond', 'B', 7);
        $pdf->line_new();
        $pdf->write_cell(70, 5, "");
        for ($e=2;$e<$nb_col;$e++){
            $colname=$a_col[$e];
            $pdf->write_cell(40, 5, nbm($a_tot[$colname]), 0, 0, 'R');
            $a_tot[$colname]=0;
        }
        $pdf->line_new();
        $pdf->SetFont('DejaVuCond', 'B', 7);

        // Display ledger name
        $ledger_name = $array[$i]['jrn_def_name'];
        $pdf->SetFont('DejaVuCond', 'B', 10);
        $pdf->write_cell(50, 8, $ledger_name);
        $pdf->line_new();

        // Display Header
        for ($e=0;$e<$nb_col;$e++){
            if ( $e == 0 ) {
                $pdf->write_cell(40, 5,$a_purchase_header[$e], 1, 0, 'L');
            } else {
                $pdf->write_cell(40, 5,$a_purchase_header[$e], 1, 0, 'R');
            }
            $a_tot[$colname]=0;
        }
    }
    for ($e=0;$e<$nb_col;$e++){
        $colname=$a_col[$e];
        if ( $e ==0 ) {
            $pdf->write_cell(40, 5, $array[$i][$colname], 1, 0, 'L');
        } elseif ($e == 1 ){
            $pdf->write_cell(40, 5, nbm($array[$i][$colname]*100), 1, 0, 'R');
        }else {
            $pdf->write_cell(40, 5, nbm($array[$i][$colname]), 1, 0, 'R');
        }
    }
    $pdf->line_new();
}
$pdf->SetFont('DejaVuCond', 'B', 7);
$pdf->write_cell(80, 5, "");
for ($e=2;$e<$nb_col;$e++){
    $colname=$a_col[$e];
    $pdf->write_cell(40, 5, nbm($a_tot[$colname]), 1, 0, 'R');
}

//-------------------------------------------------------------------------
// Summary sales
//-------------------------------------------------------------------------
$a_sum = $tax_summary->get_summary_sale();
$pdf->line_new();
$pdf->SetFont('DejaVuCond', 'B', 10);
$pdf->write_cell(50, 8, _("Résumé TVA Achat"));
$pdf->line_new();

$pdf->SetFont('DejaVuCond', 'B', 7);
for ($e=0;$e<$nb_col;$e++){
    if ( $e == 0 ) {
        $pdf->write_cell(40, 5,$a_purchase_header[$e], 1, 0, 'L');
    } else {
        $pdf->write_cell(40, 5,$a_purchase_header[$e], 1, 0, 'R');
    }
    $a_tot[$colname]=0;
}
$pdf->line_new();

$nb_array = count($array);
// initialize totals
for ($e=2;$e<$nb_col;$e++){
    $a_tot[$a_col[$e]]=0;
}
$pdf->SetFont('DejaVuCond', '', 7);

for ($i = 0; $i < $nb_array; $i++) {
    // display each row
    for ($e=0;$e<$nb_col;$e++){
        $colname=$a_col[$e];
        if ( $e ==0 ) {
            // first column TVA_LABEL
            $pdf->write_cell(40, 5, $array[$i][$colname], 1, 0, 'L');
        } elseif ($e == 1 ){
            // Secund col. tva rate
            $pdf->write_cell(40, 5, nbm($array[$i][$colname]*100), 1, 0, 'R');
        }else {
            // Other cols,display amount and compute total
            $a_tot[$colname] = bcadd($a_tot[$colname], $array[$i][$colname]);
            $pdf->write_cell(40, 5, nbm($array[$i][$colname]), 1, 0, 'R');
        }
    }
    $pdf->line_new();
}
$pdf->SetFont('DejaVuCond', 'B', 7);
$pdf->write_cell(80, 5, "");
for ($e=2;$e<$nb_col;$e++){
    $colname=$a_col[$e];
    $pdf->write_cell(40, 5, nbm($a_tot[$colname]), 1, 0, 'R');
}
$pdf->line_new();


//---------------------------------- Output --------------------------------------------------

$fDate = date('Ymd-Hi');

$pdf->Output('tva-' . $fDate . '.pdf', 'D');