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
 * @brief concerne print_LedgerTest
 * @coversDefaultClass Print_Ledger
 */
class print_LedgerTest extends TestCase
{

    /**
     * @var 
     */
    protected $object;

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
     * 
     * @covers ::factory
     */
    function testFactory()
    {
        global $g_connection;
        $ledger_sale=new Acc_Ledger_Sale($g_connection,2);
        $p_from=$this->from;
        $p_to=$this->to;
        
        // Check Sales
        $ledger=\Print_Ledger::factory($g_connection,"D",  $ledger_sale, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Simple
                ,"Sale Detail returns Print_Ledger_Simple");
        
        $ledger=\Print_Ledger::factory($g_connection,"L",  $ledger_sale, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Simple
                ,"Sale Listing returns Print_Ledger_Simple");
        
        $ledger=\Print_Ledger::factory($g_connection,"E",  $ledger_sale, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail_Item
                ,"Sale Extended returns Print_Ledger_Detail_Item");
        
        $ledger=\Print_Ledger::factory($g_connection,"A",  $ledger_sale, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail,
                "Sale Accounting returns Print_Ledger_Detail");
        
        // Check Purchase
        $ledger_purchase=new Acc_Ledger_Purchase($g_connection,3);
        $ledger=\Print_Ledger::factory($g_connection,"D",  $ledger_purchase, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Simple
                ,"Purchase Detail returns Print_Ledger_Simple");
        
        $ledger=\Print_Ledger::factory($g_connection,"L",  $ledger_purchase, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Simple
                ,"Purchase Listing returns Print_Ledger_Simple");
        
        $ledger=\Print_Ledger::factory($g_connection,"E",  $ledger_purchase, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail_Item
                ,"Purchase Extended returns Print_Ledger_Detail_Item");
        
        $ledger=\Print_Ledger::factory($g_connection,"A",  $ledger_purchase, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail
                ,"Purchase Accounting returns Print_Ledger_Detail");
        
        // Check Misc
        $ledger_misc=new Acc_Ledger($g_connection,4);
        $ledger=\Print_Ledger::factory($g_connection,"D",  $ledger_misc, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail
                 ,"Misc. Detail returns Print_Ledger_Detail");
        
        $ledger=\Print_Ledger::factory($g_connection,"L",  $ledger_misc, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Misc
                ,"Misc. Listing returns Print_Ledger_Misc");
        
        $ledger=\Print_Ledger::factory($g_connection,"E",  $ledger_misc, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail
                ,"Misc. Extended returns Print_Ledger_Detail");
        
        $ledger=\Print_Ledger::factory($g_connection,"A",  $ledger_misc, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail
                ,"Misc. Accounting returns Print_Ledger_Detail");
        
        // Financial
        $ledger_fin=new Acc_Ledger_Fin($g_connection,1);
        $ledger=\Print_Ledger::factory($g_connection,"D",  $ledger_fin, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Financial
                ,"Fin. Detail returns Print_Ledger_Financial");
        
        $ledger=\Print_Ledger::factory($g_connection,"L",  $ledger_fin, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Financial
                ,"Fin. Detail returns Print_Ledger_Financial");
        
        $ledger=\Print_Ledger::factory($g_connection,"E",  $ledger_fin, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail
                ,"Fin. Detail returns Print_Ledger_Financial");
        
        $ledger=\Print_Ledger::factory($g_connection,"A",  $ledger_fin, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail
                ,"Fin. Detail returns Print_Ledger_Detail");
        
    }
    //@covers \Print_Ledger::available_ledger
    function test_availableLedger()
    {
        $a_jrn=\Print_Ledger::available_ledger(94);
        $this->assertEquals(7,count($a_jrn),"Number of available ledger correct");
    }

}
