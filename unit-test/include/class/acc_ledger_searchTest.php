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
class Acc_Ledger_searchTest extends TestCase
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
        require DIRTEST.'/global.php';
        global $g_user;
        $g_user->set_periode(119);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown():void
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
        $this->assertEquals(" jrn_def_id in (3,83,152,1,35,4,2,36,-1) and  jr_date >= to_date('02.01.2019','DD.MM.YYYY')".
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
        \Noalyss\Facility::save_file(__DIR__."/file", "acc_ledger_search-test_display_search_form.html", $r);
        $filesize=strlen($r);
        $this->assertTrue($filesize==10971,"Size of the html string for display_search_form see "
                . __DIR__."/file/acc_ledger_search-test_display_search_form.html ");
    }
    /**
     * @testdox Build the search filter
     * @covers ::build_search_filter
     */
    function test_build_search_filter()
    {
         $ledger=new Acc_Ledger_Search('ALL');
         $ret   = $ledger->build_search_filter();
         $result=sprintf("manage_search_filter({'div':'','ledger_type':'ALL','all_type':1,'dossier':'%d'})"
                 ,Dossier::id());
         $this->assertEquals($ret,$result,"Build filter for ALL");         
         $ledger=new Acc_Ledger_Search('FIN');
         $this->assertEquals($ret,$result);
         $ret   = $ledger->build_search_filter();
         $result=sprintf("manage_search_filter({'div':'','ledger_type':'FIN','all_type':1,'dossier':'%d'})"
                 ,Dossier::id());
         $this->assertEquals($ret,$result,"Build filter for FIN");
         
         $ledger=new Acc_Ledger_Search('VEN');
         $this->assertEquals($ret,$result);
         $ret   = $ledger->build_search_filter();
         $result=sprintf("manage_search_filter({'div':'','ledger_type':'VEN','all_type':1,'dossier':'%d'})"
                 ,Dossier::id());
         $this->assertEquals($ret,$result,"Build filter for VEN");
         
         $ledger=new Acc_Ledger_Search('ACH');
         $this->assertEquals($ret,$result);
         $ret   = $ledger->build_search_filter();
         $result=sprintf("manage_search_filter({'div':'','ledger_type':'ACH','all_type':1,'dossier':'%d'})"
                 ,Dossier::id());
         $this->assertEquals($ret,$result,"Build filter for ACH");

         $ledger=new Acc_Ledger_Search('ODS');
         $this->assertEquals($ret,$result);
         $ret   = $ledger->build_search_filter();
         $result=sprintf("manage_search_filter({'div':'','ledger_type':'ODS','all_type':1,'dossier':'%d'})"
                 ,Dossier::id());
         
         $this->assertEquals($ret,$result,"Build filter for ODS");
         
    }
    /**
     * @testdox Build the SQL and where clause : no condition
     * @covers ::build_search_sql
     */
     public function testBuild_search_sql_nocondition()
    {
        global $g_connection;
        $ledger=new Acc_Ledger_Search('ALL');
        $result=$ledger->build_search_sql([]);

        $this->assertEquals(" jrn_def_id in (3,83,152,1,35,4,2,36,-1) and  jr_date >= to_date('02.01.2019','DD.MM.YYYY') and  jr_date <= to_date('31.01.2019','DD.MM.YYYY')",$result[1],"Filter on date incorrect");
        $row=$g_connection->get_array($result[0]);
        $this->assertEquals(4,count($row),"wrong number on row found");


    }
    /**
     * @testdox Build the SQL and where clause : conditon on amount
     * @covers ::build_search_sql
     */
    public function testBuild_search_sql_amount()
    {
        global $g_connection;
        $ledger=new Acc_Ledger_Search('ALL');
        $result=$ledger->build_search_sql(['amount_min'=>150,'amount_max'=>200]);

        $this->assertEquals(" jrn_def_id in (3,83,152,1,35,4,2,36,-1) and  jr_montant >=150 and  jr_montant <=200",$result[1],"Filter incorrect");
        $row=$g_connection->get_array($result[0]);
        $this->assertEquals(13,count($row),"wrong number on row found");
        foreach ($row as $row_item) {
            $this->assertTrue(
                $row_item['jr_montant']>=150
                && $row_item['jr_montant'] <= 200,
                " Filter on amount failed found {$row_item['jr_montant']} ");
        }

    }
    /**
     * @testdox Build the SQL and where clause : conditon on accounting
     * @covers ::build_search_sql
     */
    public function testBuild_search_sql_account()
    {
        global $g_connection;
        $ledger=new Acc_Ledger_Search('ALL');
        $result=$ledger->build_search_sql(["accounting"=>"40"]);

        $result[1]=str_replace([" ","\n"],"",$result[1]);
        $expected=str_replace ([" ","\n"],"","jrn_def_id in (3,83,152,1,35,4,2,36,-1) and  jr_date >= to_date('02.01.2019','DD.MM.YYYY') and  jr_date <= to_date('31.01.2019','DD.MM.YYYY') and   jr_grpt_id in (select j_grpt from jrnx where j_poste::text like '40%' )");

        $this->assertEquals($expected,$result[1],"Filter  incorrect");
        $row=$g_connection->get_array($result[0]);
        $this->assertEquals(2,count($row),"wrong number on row found");

    }

    /**
     * @testdox Build the SQL and where clause : conditon on card
     * @covers ::build_search_sql
     */
    public function testBuild_search_sql_card()
    {
        global $g_connection;
        $ledger=new Acc_Ledger_Search('ALL');
        $result=$ledger->build_search_sql(["qcode"=>"FOURNI1"]);

        $result[1]=str_replace([" ","\n"],"",$result[1]);
        $expected=str_replace ([" "],"","jrn_def_id in (3,83,152,1,35,4,2,36,-1) and  jr_date >= to_date('02.01.2019','DD.MM.YYYY') and  jr_date <= to_date('31.01.2019','DD.MM.YYYY') and   jr_grpt_id in ( select j_grpt fromjrnx where trim(j_qcode) = upper(trim('FOURNI1')))");

        $this->assertEquals($expected,$result[1],"Filter  incorrect");
        $row=$g_connection->get_array($result[0]);
        $this->assertEquals(1,count($row),"wrong number on row found");

    }
    /**
     * @testdox Build the SQL and where clause : conditon on TVA id between 01.01.2010 and 31.12.2019
     * @covers ::build_search_sql
     */
    public function testBuild_search_sql_TVA()
    {
        global $g_connection;
        $ledger=new Acc_Ledger_Search('ALL');
        $result=$ledger->build_search_sql(["tva_id_search"=>"1"
            ,"amount_min"=>0
            ,"amount_max"=>0
            ,"date_start"=>"01.01.2010"
            ,"date_end"=>"31.12.2019"]);

        $result[1]=str_replace([" ","\n"],"",$result[1]);
        $expected=str_replace ([" ","\n"],""," jrn_def_id in (3,83,152,1,35,4,2,36,-1) and  jr_date >= to_date('01.01.2010','DD.MM.YYYY') and  jr_date <= to_date('31.12.2019','DD.MM.YYYY') and  jr_internal in 
                (   select distinct qp_internal 
                        from quant_purchase 
                        where qp_vat_code=1  union all  
                    select distinct qs_internal 
                        from quant_sold 
                        where qs_vat_code=1)

");

        $this->assertEquals($expected,$result[1],"Filter  incorrect");
        $row=$g_connection->get_array($result[0]);
        $this->assertEquals(18,count($row),"wrong number on row found");

    }

}
