<?php

use PHPUnit\Framework\TestCase;

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2002-2019) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief concern Print_Ledger_Simple
 * @coversDefaultClass  Print_Ledger_Simple
 */
class print_ledger_simpleTest extends TestCase
{

    /**
     * @var 
     */
    protected $object;
 private $from,$to;
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp():void
    {
        include 'global.php';
        // Exercice 2018
        $this->from=92;
        $this->to=103;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown():void
    {
        
    }

    /**
     * @covers ::export
     */
    function test_export()
    {
        global $g_connection;
        $p_from=$this->from;
        $p_to=$this->to;
        //-------------------------------------------------------------------------------------------------------------
        // Purchase
        //-------------------------------------------------------------------------------------------------------------

        $ledger_purchase=new Acc_Ledger_Purchase($g_connection, 3);

        // Paid 
        //------------
        $ledger=\Print_Ledger::factory($g_connection, "E", $ledger_purchase, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail_Item
                , "Purchase Extended returns Print_Ledger_Detail_Item");

        $ledger->setDossierInfo($ledger_purchase->jrn_def_name);
        $ledger->AliasNbPages();
        $ledger->AddPage();
        $ledger->SetAuthor('NOALYSS');
        $ledger->setTitle(_("Journal Achat Payé"), true);
        $ledger->export();
        $ledger->Output(__DIR__."/file/print_ledger_simple_purchase_paid.pdf", "F");
        $this->assertFileExists(__DIR__."/file/print_ledger_simple_purchase_paid.pdf");
        print __DIR__."/file/print_ledger_simple_purchase_paid.pdf".PHP_EOL;

        // Unpaid
        //------------
        $ledger=\Print_Ledger::factory($g_connection, "E", $ledger_purchase, $p_from, $p_to, "unpaid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail_Item
                , "Purchase Extended returns Print_Ledger_Detail_Item");

        $ledger->setDossierInfo($ledger_purchase->jrn_def_name);
        $ledger->AliasNbPages();
        $ledger->AddPage();
        $ledger->SetAuthor('NOALYSS');
        $ledger->setTitle(_("Journal Non Payé"), true);
        $ledger->export();
        $ledger->Output(__DIR__."/file/print_ledger_simple_purchase_unpaid.pdf", "F");
        $this->assertFileExists(__DIR__."/file/print_ledger_simple_purchase_unpaid.pdf");
print __DIR__."/file/print_ledger_simple_purchase_unpaid.pdf".PHP_EOL;
        // All
        //------------
        $ledger=\Print_Ledger::factory($g_connection, "E", $ledger_purchase, $p_from, $p_to, "all");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail_Item
                , "Purchase Extended returns Print_Ledger_Detail_Item");

        $ledger->setDossierInfo($ledger_purchase->jrn_def_name);
        $ledger->AliasNbPages();
        $ledger->AddPage();
        $ledger->SetAuthor('NOALYSS');
        $ledger->setTitle(_("Journal Achat tous"), true);
        $ledger->export();
        $ledger->Output(__DIR__."/file/print_ledger_simple_purchase_all.pdf", "F");
        $this->assertFileExists(__DIR__."/file/print_ledger_simple_purchase_all.pdf");
print __DIR__."/file/print_ledger_simple_purchase_all.pdf".PHP_EOL;
        //-------------------------------------------------------------------------------------------------------------
        // Sale
        //-------------------------------------------------------------------------------------------------------------
        $ledger_sale=new Acc_Ledger_Sale($g_connection, 2);

        // Paid
        //-----------------
        $ledger=\Print_Ledger::factory($g_connection, "E", $ledger_sale, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail_Item
                , "Purchase Extended returns Print_Ledger_Detail_Item");

        $ledger->setDossierInfo($ledger_sale->jrn_def_name);
        $ledger->AliasNbPages();
        $ledger->AddPage();
        $ledger->SetAuthor('NOALYSS');
        $ledger->setTitle(_("Journal Vente Payé"), true);
        $ledger->export();
        $ledger->Output(__DIR__."/file/print_ledger_simple_sale_paid.pdf", "F");
print __DIR__."/file/print_ledger_simple_sale_paid.pdf".PHP_EOL;

        // Unpaid
        //-----------------
        $ledger=\Print_Ledger::factory($g_connection, "E", $ledger_sale, $p_from, $p_to, "unpaid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail_Item
                , "Purchase Extended returns Print_Ledger_Detail_Item");

        $ledger->setDossierInfo($ledger_sale->jrn_def_name);
        $ledger->AliasNbPages();
        $ledger->AddPage();
        $ledger->SetAuthor('NOALYSS');
        $ledger->setTitle(_("Journal Non Payé"), true);
        $ledger->export();
        $ledger->Output(__DIR__."/file/print_ledger_simple_sale_unpaid.pdf", "F");
        $this->assertFileExists(__DIR__."/file/print_ledger_simple_sale_unpaid.pdf");
print __DIR__."/file/print_ledger_simple_sale_unpaid.pdf".PHP_EOL;

        // All
        //-----------------
        $ledger=\Print_Ledger::factory($g_connection, "E", $ledger_sale, $p_from, $p_to, "all");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail_Item
                , "Purchase Extended returns Print_Ledger_Detail_Item");

        $ledger->setDossierInfo($ledger_sale->jrn_def_name);
        $ledger->AliasNbPages();
        $ledger->AddPage();
        $ledger->SetAuthor('NOALYSS');
        $ledger->setTitle(_("Journal Vente tous"), true);
        $ledger->export();
        $ledger->Output(__DIR__."/file/print_ledger_simple_sale_all.pdf", "F");
        $this->assertFileExists(__DIR__."/file/print_ledger_simple_sale_all.pdf");
print __DIR__."/file/print_ledger_simple_sale_all.pdf".PHP_EOL;

    }

}
