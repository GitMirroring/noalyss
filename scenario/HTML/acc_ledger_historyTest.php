<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
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
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

//@description:History : show different history : detail, list, ...

/**
 * @file
 * @brief Test of ledger_history show html
 */
require_once NOALYSS_INCLUDE."/class/acc_ledger_history.class.php";
html_page_start();
$ledger=[1,2,3,4];
echo Dossier::hidden();
global $cn, $g_user, $g_succeed, $g_failed;
$cn=Dossier::connect();

$ledger_history=Acc_Ledger_History::factory($cn, $ledger, $min, $max, "E","all");
echo h1("Detailled Accounting");
echo h2(_("export detail html all ledgers result = Detailled Accounting from Acc_Ledger_History_Generic"));
echo '<a href="#n1">Detailled Accounting</a>';
$ledger_history->export_detail_html();

echo h2(_("Set mode to D etailled all_ledgers result = Detailled Accounting from Acc_Ledger_History_Generic" )
    ,'id="n1"');
echo '<a href="#n2">Sale Listing</a>';

$ledger_history->set_m_mode("D");
$ledger_history->export_html();

echo h1(_("Only VEN from Acc_Ledger_History_Sale"),' id="n2"');
echo '<a href="#n3">Sale Detailed</a>';
$ledger_history=Acc_Ledger_History::factory($cn, [2], $min , $max , "L","all");
$ledger_history->export_detail_html();

echo h2(_("Only VEN one line"));
$ledger_history->set_a_ledger([2]);
$ledger_history->set_m_mode("L");
$ledger_history->export_html();
echo h2(_("Only VEN Detailled"),'id="n3"');
echo '<a href="#n4">Sale Extended</a>';
$ledger_history->set_a_ledger([2]);
$ledger_history->set_m_mode("D");
$ledger_history->export_html();

echo h2(_("Only VEN Extended"),'id="n4"');
echo '<a href="#n5">Sale + Purchase</a>';
$ledger_history->set_a_ledger([2]);
$ledger_history->set_m_mode("E");
$ledger_history->export_html();

echo h2("VEN + ACH",'id="n5"');
echo '<a href="#n6">Purchase Detailed</a>';
$ledger_history=Acc_Ledger_History::factory($cn, [3,2], $min, $max , "L","all");

$ledger_history->export_oneline_html();

echo h1("ACH from Acc_Ledger_History_Purchase");
echo h2("Detailled accouting",'id="n6"');
echo '<a href="#n7">Purchase listing</a>';
$ledger_history=new Acc_Ledger_History_Purchase($cn,[3],$max,$min,"A","all");
$ledger_history->export_html();
echo h2("Ach one line",'id="n7"');
echo '<a href="#n8">Purchase Detailed</a>';
$ledger_history->set_m_mode("L");
$ledger_history->export_html();
echo h2("Ach Detail" ,'id="n8"');
echo '<a href="#n9">Purchase Extended</a>';
$ledger_history->set_m_mode("D");
$ledger_history->export_html();
echo h2("Ach Extended" ,'id="n9"');
echo '<a href="#n10">Financial</a>';
$ledger_history->set_m_mode("E");
$ledger_history->export_html();

echo h1("FIN from Acc_Ledger_History_Financial");
echo h2("Detailled accouting",'id="n10"');
echo '<a href="#n11">Financial one line</a>';
$ledger_history=new Acc_Ledger_History_Financial($cn,[11,16],$min,$max,"A","all");
$ledger_history->export_html();
echo h2("FIN one line" ,'id="n11"');
echo '<a href="#n12">Financial Detailed</a>';
$ledger_history->set_m_mode("L");
$ledger_history->export_html();
echo h2(">FIN Detail", 'id="n12"');
echo '<a href="#n13">Financial extended</a>';
$ledger_history->set_m_mode("D");
$ledger_history->export_html();
echo h2("FIN Extended", 'id="n13"');
$ledger_history->set_m_mode("E");
$ledger_history->export_html();
