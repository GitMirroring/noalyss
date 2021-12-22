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
 * @brief concerne acc_ledger_historyTest.class , used in "impression journaux"
 * @coversDefaultClass Acc_Ledger_History
 */
class Acc_Ledger_HistoryTest extends TestCase
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp():void
    {
        include 'global.php';
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown():void
    {
        
    }
    /**
     * @covers Acc_Ledger_History::factory
     * @covers ::get_ledger_type
     * @covers ::set_filter_operation
     */
    function testFactory()
    {
        global $g_connection;
        $p_min_id=$g_connection->get_value("select p_id from parm_periode order by p_start limit 1");
        $p_max_id=$g_connection->get_value("select p_id from parm_periode order by p_start desc limit 1");
        
        $object=Acc_Ledger_History::factory($g_connection, [1],$p_min_id ,$p_max_id , "D", 'all');
        $this->assertEquals('FIN',$object->get_ledger_type());

        $object=Acc_Ledger_History::factory($g_connection, [4],$p_min_id ,$p_max_id , "D", 'all');
        $this->assertEquals('ODS',$object->get_ledger_type());
        
        $object=Acc_Ledger_History::factory($g_connection, [2],$p_min_id ,$p_max_id , "D", 'all');
        $this->assertEquals('VEN',$object->get_ledger_type());
        
        $object=Acc_Ledger_History::factory($g_connection, [3],$p_min_id ,$p_max_id , "D", 'all');
        $this->assertEquals('ACH',$object->get_ledger_type());
        
    }
    /**
    * @covers ::get_tiers
    * @covers ::get_tiers_id
    */
    function testGet_tiers()
    {
        global $g_connection;
        $p_min_id=$g_connection->get_value("select p_id from parm_periode order by p_start limit 1");
        $p_max_id=$g_connection->get_value("select p_id from parm_periode order by p_start desc limit 1");

        $object=Acc_Ledger_History::factory($g_connection, [1],$p_min_id ,$p_max_id , "E", 'paid');
        $this->assertEquals(" ",$object->get_tiers('ODS',213));

        $this->assertEquals("Client 2",trim($object->get_tiers('VEN',213)));
         
         
    }
    /**
     * @covers ::get_filter_operation
     */
    function testget_Filter_Operation()
    {
        global $g_connection;
        $p_min_id=$g_connection->get_value("select p_id from parm_periode order by p_start limit 1");
        $p_max_id=$g_connection->get_value("select p_id from parm_periode order by p_start desc limit 1");

        $object=Acc_Ledger_History::factory($g_connection, [2],$p_min_id ,$p_max_id , "D", 'all');
        $this->assertEquals("all",$object->get_filter_operation()) ;

        $object=Acc_Ledger_History::factory($g_connection, [3],$p_min_id ,$p_max_id , "D", 'all');
        $this->assertEquals("all",$object->get_filter_operation()) ;
        
        $object=Acc_Ledger_History::factory($g_connection, [1],$p_min_id ,$p_max_id , "D", 'all');
        $this->assertEquals("all",$object->get_filter_operation()) ;
    }
    /**
     * @covers ::build_filter_operation
     * @covers ::set_filter_operation
     * 
     */
    function testBuild_filter_operation()
    {
        global $g_connection;
        $p_min_id=$g_connection->get_value("select p_id from parm_periode order by p_start limit 1");
        $p_max_id=$g_connection->get_value("select p_id from parm_periode order by p_start desc limit 1");
        $object=Acc_Ledger_History::factory($g_connection, [2],$p_min_id ,$p_max_id , "D", 'all');
        $object->get_row();
        $a_row=$object->get_data();
        $this->assertEquals(13,count($a_row));
        
        $object->set_filter_operation("paid");
        $object->get_row();
        $a_row=$object->get_data();
        $this->assertEquals(6,count($a_row));
        
        $object->set_filter_operation("unpaid");
        $object->get_row();
        $a_row=$object->get_data();
        $this->assertEquals(7,count($a_row));

        
        }
    
}