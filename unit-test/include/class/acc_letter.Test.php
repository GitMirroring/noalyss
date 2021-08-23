<?php

/*
 * * Copyright (C) 2021 Dany De Bontridder <dany@alchimerys.be>
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
 * Author : Dany De Bontridder danydb@noalyss.eu
 * 
 */

/**
 * @file
 * @brief 
 */
use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 */
require 'global.php';

class Acc_Letter extends TestCase
{
    /**
     * @var Fiche
     */
    protected $object;
   
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test method is executed.
     */
    protected function setUp()
    {
        include 'global.php';
        $this->a_column=array(
            "j_id",
           "j_date",
           "j_date_fmt",
           "jr_pj_number",
           "j_montant",
           "j_debit",
           "jr_comment",
           "jr_internal",
           "jr_id",
           "jr_def_id",
           "letter",
           "letter_diff",
           "currency_amount",
           "currency_id",
           "currency_rate",
           "currency_rate_ref",
           "cr_code_iso"
    );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test method is executed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * the setUpBeforeClass() template methods is called before the first test of the test case
     *  class is run 
     */
    public static function setUpBeforeClass()
    {
        include 'global.php';
        
    }

    /**
     *  tearDownAfterClass() template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass()
    {
        //        include 'global.php';
    }
    public function dataget_Letter()
    {
               return array([22,577],[26,343]);
    }
     /**
     * @brief 
     * @testdox testget_letterAccount Lettering_Account::get_letter
     * @covers Lettering_Card::get_letter
    *  @dataProvider dataget_Letter
     */
    public function testget_letterAccount($p_fiche_id,$p_jrnx_id)
    {
        global $g_connection;
        $letter = new Lettering_Account($g_connection);
        $fiche=new Fiche($g_connection,$p_fiche_id);
	$letter->set_parameter('account', $fiche->strAttribut(ATTR_DEF_ACCOUNT));
	$letter->set_parameter('start', '01.01.2010' );
	$letter->set_parameter('end', '31.12.2019');
        
        // lettered operations by card
        $letter->get_letter();
        $this->assertTrue(!empty($letter->content)&& is_array($letter->content)," result is not an array");
        $this->assertTrue(isset($letter->content[0]['currency_id']),"missing column currency_id");
        foreach ($this->a_column as $column) {
            $this->assertTrue(array_key_exists($column,$letter->content[0]),"missing column $column");
        }
        
        // all operations by card
        $letter->get_all();
        
        $this->assertTrue(!empty($letter->content)&& is_array($letter->content)," result is not an array");
        foreach ($this->a_column as $column) {
            $this->assertTrue(array_key_exists($column,$letter->content[0]),"missing column $column");
        }
        
        // unlettered operations by card
        $letter->get_unletter();
        $this->assertTrue(!empty($letter->content)&& is_array($letter->content)," result is not an array");
        foreach ($this->a_column as $column) {
            $this->assertTrue(array_key_exists($column,$letter->content[0]),"missing column $column");
        }
        
        // lettered with different amount operations by card
        $restore=$g_connection->get_value("select j_montant from jrnx where j_id=$1",[$p_jrnx_id]);
        $g_connection->exec_sql("update jrnx set j_montant = j_montant -1 where j_id=$1 ",[$p_jrnx_id]);
        $letter->get_letter_diff();
        
        $this->assertTrue(!empty($letter->content)&& is_array($letter->content)," result is not an array");
         foreach ($this->a_column as $column) {
            $this->assertTrue(array_key_exists($column,$letter->content[0]),"missing column $column");
        }
        
        $g_connection->exec_sql("update jrnx set j_montant =$2 where j_id=$1 ",[$p_jrnx_id,$restore]);

    }
   /**
     * @brief 
     * @testdox testget_letterCard Lettering_Card::get_letter
     * @covers Lettering_Card::get_letter
    *  @dataProvider dataget_Letter
     */
    public function testget_letterCard($p_fiche_id,$p_jrnx_id)
    {
        global $g_connection;
        $letter = new Lettering_Card($g_connection);
        $fiche=new Fiche($g_connection,$p_fiche_id);
	$letter->set_parameter('quick_code', $fiche->strAttribut(ATTR_DEF_QUICKCODE));
	$letter->set_parameter('start', '01.01.2010' );
	$letter->set_parameter('end', '31.12.2019');
        
        // lettered operations by card
        $letter->get_letter();
        $this->assertTrue(!empty($letter->content)&& is_array($letter->content)," result is not an array");
          foreach ($this->a_column as $column) {
            $this->assertTrue(array_key_exists($column,$letter->content[0]),"missing column $column");
        }
        // all operations by card
        $letter->get_all();
        
        $this->assertTrue(!empty($letter->content)&& is_array($letter->content)," result is not an array");
          foreach ($this->a_column as $column) {
            $this->assertTrue(array_key_exists($column,$letter->content[0]),"missing column $column");
        }
        // unlettered operations by card
        $letter->get_unletter();
        $this->assertTrue(!empty($letter->content)&& is_array($letter->content)," result is not an array");
        $this->assertTrue(isset($letter->content[0]['currency_id']),"missing column currency_id");
        foreach ($this->a_column as $column) {
            $this->assertTrue(array_key_exists($column,$letter->content[0]),"missing column $column");
        }
        // lettered with different amount operations by card
        $restore=$g_connection->get_value("select j_montant from jrnx where j_id=$1",[$p_jrnx_id]);
        $g_connection->exec_sql("update jrnx set j_montant = j_montant -1 where j_id=$1 ",[$p_jrnx_id]);
        $letter->get_letter_diff();
        
        $this->assertTrue(!empty($letter->content)&& is_array($letter->content)," result is not an array");
          foreach ($this->a_column as $column) {
            $this->assertTrue(array_key_exists($column,$letter->content[0]),"missing column $column");
        }
        $g_connection->exec_sql("update jrnx set j_montant =$2 where j_id=$1 ",[$p_jrnx_id,$restore]);

    }
    public function dataDateLimit()
    {
        return array(
            [100,'01.01.2018','31.12.2018'],
            [125,'01.01.2019','31.12.2019'],
            [120,'01.01.2019','31.12.2019']
        );
    }
    /**
     * @testdox testDateLimitComputed Check the default date
     * @global type $g_connection
     * @param type $p_id
     * @dataProvider dataDateLimit
     */
    public function testDateLimitComputed($p_periode_id,$p_first_day,$p_last_day)
    {
        global $g_connection;
        $user=new User($g_connection);
        $restore=$user->get_periode();
        $user->set_periode($p_periode_id);
        
        $lettering=new Lettering($g_connection);
        $lettering->set_parameter('quick_code', 'FOURNI');
        $this->assertEquals($p_first_day,$lettering->get_parameter("start"),"first day incorrect");
        $this->assertEquals($p_last_day,$lettering->get_parameter("end"),"last day incorrect");
        
        $lettering=new Lettering_Card($g_connection);
        $lettering->set_parameter('quick_code', 'FOURNI');
        $this->assertEquals($p_first_day,$lettering->get_parameter("start"),"first day incorrect");
        $this->assertEquals($p_last_day,$lettering->get_parameter("end"),"last day incorrect");
        
        $lettering=new Lettering_Account($g_connection);
        $lettering->set_parameter('quick_code', 'FOURNI');
        $this->assertEquals($p_first_day,$lettering->get_parameter("start"),"first day incorrect");
        $this->assertEquals($p_last_day,$lettering->get_parameter("end"),"last day incorrect");
    }
    /**
     * @testdox testDateLimitGiven Check the default date
     * @global type $g_connection
     * @param type $p_id
     * @dataProvider dataDateLimit
     */
    public function testDateLimitGiven($p_periode_id,$p_first_day,$p_last_day)
    {
        global $g_connection;
        
        $lettering=new Lettering($g_connection);
        $lettering->set_parameter('quick_code', 'FOURNI');
        $lettering->set_parameter('start', $p_first_day);
        $lettering->set_parameter('end',$p_last_day);
        $this->assertEquals($p_first_day,$lettering->get_parameter("start"),"first day incorrect");
        $this->assertEquals($p_last_day,$lettering->get_parameter("end"),"last day incorrect");
        
        $lettering=new Lettering_Card($g_connection);
        $lettering->set_parameter('quick_code', 'FOURNI');
        $lettering->set_parameter('start', $p_first_day);
        $lettering->set_parameter('end',$p_last_day);
        $this->assertEquals($p_first_day,$lettering->get_parameter("start"),"first day incorrect");
        $this->assertEquals($p_last_day,$lettering->get_parameter("end"),"last day incorrect");
        
        $lettering=new Lettering_Account($g_connection);
        $lettering->set_parameter('quick_code', 'FOURNI');
        $lettering->set_parameter('start', $p_first_day);
        $lettering->set_parameter('end',$p_last_day);
        $this->assertEquals($p_first_day,$lettering->get_parameter("start"),"first day incorrect");
        $this->assertEquals($p_last_day,$lettering->get_parameter("end"),"last day incorrect");
    }

}
