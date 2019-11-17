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
 * @brief concerne print_ledger_detail
 * @coversDefaultClass Print_Ledger_Detail
 */
include_once 'global.php';

class Print_Ledger_DetailTest extends TestCase
{

    /**
     * @var 
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->from=92;
        $this->to=103;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
        

    }
    function test_export()
    {
         global $g_connection;
        $p_from=$this->from;
        $p_to=$this->to;
        
        // Financial
        $ledger_fin=new Acc_Ledger($g_connection,4);
        $ledger=\Print_Ledger::factory($g_connection,"D",  $ledger_fin, $p_from, $p_to, "paid");
        $this->assertTrue($ledger instanceof Print_Ledger_Detail
                ,"Misc. Detail returns Print_Ledger");
       
        $ledger->setDossierInfo($ledger_fin->jrn_def_name);
        $ledger->AliasNbPages();
        $ledger->AddPage();
        $ledger->SetAuthor('NOALYSS');
        $ledger->setTitle(_("Journal"), true);
        $ledger->export();
        $ledger->Output(__DIR__."/file/misc-detail.pdf","F");
        $this->assertFileExists(__DIR__."/file/misc-detail.pdf");
    }

}
