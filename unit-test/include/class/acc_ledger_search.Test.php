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
if ( ! defined("ALLOWED"))  {
    define('ALLOWED',1);
}
/**
 * @file
 * @brief concern acc_ledger_search
 * @coversDefaultClass  acc_ledger_search
 */
class Acc_Ledger_Test extends TestCase
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
        include 'global.php';
        global $g_user;
        $g_user->set_periode(119);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * @covers ::build_search_sql
     */
    function test_all_ledger()
    {
        global $g_connection;
        // all legder
        $ledger=new Acc_Ledger_Search('ALL');
        $result=$ledger->build_search_sql(NULL);
        $a_result=$g_connection->get_array($result[0]);
        $this->assertEquals(4,count($a_result));
        $this->assertEquals(" jrn_def_id in (3,83,1,35,4,2,36,-1) and  jr_date >= to_date('02.01.2019','DD.MM.YYYY')".
                " and  jr_date <= to_date('31.01.2019','DD.MM.YYYY')",$result[1]);
        
        // Expect exception
        try {
            $ledger=new Acc_Ledger_Search('ALL1');
            $this->assertTrue(FALSE,"Exception not thrown with invalide type");
        } catch (Exception $e) {
            $this->assertEquals($e->getCode(),1005,"Exception is type invalide");
        }
        
    }
    /**
     * @covers ::display_search_form
     */
    function test_display_search_form()
    {
         put_global(array(["key"=>"ac","value"=>"phpunit"]));
        $ledger=new Acc_Ledger_Search('ALL');
        $r=$ledger->display_search_form();
        $this->assertEquals(9068,strlen($r),"Size of the html string for display_search_form");
    }
    /**
     * @covers ::build_search_filter
     */
    function test_build_search_filter()
    {
         $ledger=new Acc_Ledger_Search('ALL');
         $ret   = $ledger->build_search_filter();
         $result=sprintf("manage_search_filter({'div':'','ledger_type':'ALL','all_type':1,'dossier':%d})"
                 ,Dossier::id());
         $this->assertEquals($ret,$result,"Build filter for ALL");
         
         $ledger=new Acc_Ledger_Search('FIN');
         $this->assertEquals($ret,$result);
         $ret   = $ledger->build_search_filter();
         $result=sprintf("manage_search_filter({'div':'','ledger_type':'FIN','all_type':1,'dossier':%d})"
                 ,Dossier::id());
         $this->assertEquals($ret,$result,"Build filter for FIN");
         
         $ledger=new Acc_Ledger_Search('VEN');
         $this->assertEquals($ret,$result);
         $ret   = $ledger->build_search_filter();
         $result=sprintf("manage_search_filter({'div':'','ledger_type':'VEN','all_type':1,'dossier':%d})"
                 ,Dossier::id());
         $this->assertEquals($ret,$result,"Build filter for VEN");
         
         $ledger=new Acc_Ledger_Search('ACH');
         $this->assertEquals($ret,$result);
         $ret   = $ledger->build_search_filter();
         $result=sprintf("manage_search_filter({'div':'','ledger_type':'ACH','all_type':1,'dossier':%d})"
                 ,Dossier::id());
         $this->assertEquals($ret,$result,"Build filter for ACH");

         $ledger=new Acc_Ledger_Search('ODS');
         $this->assertEquals($ret,$result);
         $ret   = $ledger->build_search_filter();
         $result=sprintf("manage_search_filter({'div':'','ledger_type':'ODS','all_type':1,'dossier':%d})"
                 ,Dossier::id());
         
         $this->assertEquals($ret,$result,"Build filter for ODS");
         
    }
}
