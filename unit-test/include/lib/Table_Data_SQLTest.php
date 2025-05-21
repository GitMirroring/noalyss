<?php

/*
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * 
 * Author : Dany De Bontridder danydb@noalyss.eu
 * Copyright (C) 2025 Dany De Bontridder <dany@alchimerys.be>
 * 
 */

/**
 * @file
 * @brief noalyss
 */
use PHPUnit\Framework\TestCase;

require DIRTEST . '/global.php';

class FakeData1_SQL extends \Table_Data_SQL {

    function __construct(Database $p_cn, $p_id = -1) {
        $this->table = "public.jrn";
        $this->primary_key = "jr_id";
        /*
         * List of columns
         */
        $this->name = array(
            "jr_id" => "jr_id"
            , "jr_def_id" => "jr_def_id"
            , "jr_montant" => "jr_montant"
            , "jr_comment" => "jr_comment"
            , "jr_date" => "jr_date"
            , "jr_grpt_id" => "jr_grpt_id"
            , "jr_internal" => "jr_internal"
            , "jr_tech_date" => "jr_tech_date"
            , "jr_tech_per" => "jr_tech_per"
            , "jrn_ech" => "jrn_ech"
            , "jr_ech" => "jr_ech"
            , "jr_rapt" => "jr_rapt"
            , "jr_valid" => "jr_valid"
            , "jr_opid" => "jr_opid"
            , "jr_c_opid" => "jr_c_opid"
            , "jr_pj" => "jr_pj"
            , "jr_pj_name" => "jr_pj_name"
            , "jr_pj_type" => "jr_pj_type"
            , "jr_pj_number" => "jr_pj_number"
            , "jr_mt" => "jr_mt"
            , "jr_date_paid" => "jr_date_paid"
            , "jr_optype" => "jr_optype"
            , "currency_id" => "currency_id"
            , "currency_rate" => "currency_rate"
            , "currency_rate_ref" => "currency_rate_ref"
        );

        /*
         * Type of columns
         */
        $this->type = array(
            "jr_id" => "numeric"
            , "jr_def_id" => "numeric"
            , "jr_montant" => "numeric"
            , "jr_comment" => "text"
            , "jr_date" => "date"
            , "jr_grpt_id" => "numeric"
            , "jr_internal" => "text"
            , "jr_tech_date" => "date"
            , "jr_tech_per" => "numeric"
            , "jrn_ech" => "date"
            , "jr_ech" => "date"
            , "jr_rapt" => "text"
            , "jr_valid" => "boolean"
            , "jr_opid" => "numeric"
            , "jr_c_opid" => "numeric"
            , "jr_pj" => "oid"
            , "jr_pj_name" => "text"
            , "jr_pj_type" => "text"
            , "jr_pj_number" => "text"
            , "jr_mt" => "text"
            , "jr_date_paid" => "date"
            , "jr_optype" => "text"
            , "currency_id" => "numeric"
            , "currency_rate" => "numeric"
            , "currency_rate_ref" => "numeric"
        );

        $this->default = array("jr_id" => "auto");

        $this->date_format = "DD.MM.YYYY";
        parent::__construct($p_cn, $p_id);
    }
}

/**
 * @testdox Test Table_Data_SQL
 * @coversDefaultClass
 * @global $g_connection
 */
class Table_Data_SQLTest extends TestCase {

    /**
     * @testdox Load existing row
     * @covers load(), __construct
    
     */
    function testLoadExisting()
    {
        global $g_connection;
        $jrn=new FakeData1_SQL($g_connection,911);
        $this->assertTrue($jrn->jr_id==911," Load existing doesn't load 911");
        $this->assertTrue($jrn->jr_internal=='V000410'," jr_internal not correct");
    }
    /**
     * @testdox Not existing row
     * @covers load(), __construct
     */
    function testLoadNotExists()
    {
        global $g_connection;
        $jrn=new FakeData1_SQL($g_connection,191911);
        $this->assertTrue($jrn->jr_id==-1," Load existing doesn't load 911");
        $this->assertTrue($jrn->jr_internal==null," jr_internal not correct");
    }
    /**
     * @testdox Virtual Column : formatted date
     * @covers load(), __construct ,set_virtual_col
     */
    function testVirtualColFormattedDate()
    {
        global $g_connection;
        
        $jrn=new FakeData1_SQL($g_connection);
        $jrn->jr_id=915;
        $jrn->set_virtual_col("str_date", " to_char(jr_tech_date,'DD.MM.YY HH24:MI:SS')");
        $jrn->load();
        $this->assertTrue($jrn->str_date=='05.01.22 13:01:46',"virtual col fails gets [{$jrn->str_date}]");
    }
     /**
     * @testdox Virtual Column : calc
     * @covers load(), __construct, set_virtual_col
     */
    function testVirtualColCalc()
    {
        global $g_connection;
        $jrn=new FakeData1_SQL($g_connection);
        $jrn->jr_id=911;
        $jrn->set_virtual_col("double_amount", " jr_montant  * 2 ");
        $jrn->load();
        $this->assertTrue($jrn->double_amount==2*$jrn->jr_montant,"virtual col fails gets [{$jrn->double_amount}]");
    }
    /**
     * @testdox Virtual Column : when case
     * @covers load(), __construct, set_virtual_col
     */
    function testVirtualColWhenCase()
    {
        global $g_connection;
        $jrn=new FakeData1_SQL($g_connection);
        $jrn->set_virtual_col('day_date'," to_char(jr_date,'DD')");
        $jrn->set_virtual_col('day_event',"  case when to_char(jr_date,'DD')::int  % 2 = 0 then 'EVEN' else 'ODD' end ");
        $r=$jrn->seek();
        
        $nb=\Database::num_row($r);
        $this->assertTrue ($nb > 0 , "nothing found");
        for ($x=0;$x < $nb ; $x++)
        {
            $a_jrn=\Database::fetch_array($r, $x);
            if ( $a_jrn['day_date'] % 2 == 0 ) $this->assertTrue( $a_jrn['day_event'] =='EVEN',"virtual col fails gets event ".var_export($a_jrn,true));
            if ( $a_jrn['day_date'] % 2 != 0 ) $this->assertTrue( $a_jrn['day_event']=='ODD',"virtual col fails gets event ".var_export($a_jrn,true));
        }
    }
    /**
     * @testdox Data_SQL::to_row and update : 
     * @covers Data_SQL->to_row() and Table_Data_SQL->update()
     */
    function testTo_Row()
    {
        global $g_connection;
        $jrn=new FakeData1_SQL($g_connection);
        $jrn->set_virtual_col("double_amount", " jr_montant  * 2 ");
        $jrn->jr_id=911;
        $jrn->load();
        $this->assertTrue($jrn->jr_id==911," Load existing doesn't load 911");
        $this->assertTrue($jrn->jr_internal=='V000410'," jr_internal not correct");
        $this->assertTrue($jrn->double_amount==$jrn->jr_montant *2," virtual column double_amount not correct {$jrn->double_amount} and jr_montant is {$jrn->jr_montant}");
        $jrn->to_row(array("double_amount"=>-1));
        $this->assertTrue($jrn->double_amount==-1," virtual column double_amount not changed {$jrn->double_amount}");
        $this->assertTrue($jrn->jr_internal=='V000410'," jr_internal has changed");
        
    }
}
