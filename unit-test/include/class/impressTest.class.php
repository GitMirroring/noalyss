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
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief test impress.class.php
 * 
 * 
 */
class ImpressTest extends TestCase
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
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * 
     * @covers Impress::check_formula
     */
    function test_check_formula()
    {
        $aFormulaTest=array(
            ['1', true],
            ['(45+5)', true],
            ['round([45])', true],
            ['$A=9', true],
            ['$S30=($F1 >=0)?$F1:0', true],
            ['[45%]', true],
            ['[50]*[51%]', true],
            ['$A1=[50]*[51%]', true],
            ['[50]*9', true],
            ['[50]*9.0', true],
            ['[50%]*9', true],
            ['$C1111=[50%]*9', true],
            ['$C1111=[50%]*9*$D1', true],
            ['$C10=[10%]', true],
            ['[50%]*9.0', true],
            ['[50%]*9.0 FROM=01.2004', true],
            ['[50%]*9.0FROM=01.2004', true],
            ['system', false],
            ['unlink', false],
            ['ls -1', false],
            ['<script>document.location="https://yahoo.fr";</script>', false],
            ["[45ABC]*1", true],
            ["[4511-s]", true],
            ["{TEL}", true],
            ["{TEL}*45", true],
            ["{TEL-s}*45", true],
            ["{1TEL-s}*45", true],
            ["{1TEL}*45", true],
            ["{T*EL-s}*45", false],
            ["{T+EL-s}*45", false],
            ["{T/EL-s}*45", false]
        );
        foreach ($aFormulaTest as $formula)
        {
            $this->assertEquals(\Impress::check_formula($formula[0]), $formula[1], " $formula[0] ");

            foreach (array('+', '-', '/') as $b)
            {
                $ee=str_replace('*', $b, $formula[0]);
                $this->assertEquals(\Impress::check_formula($formula[0]), $formula[1], " with $formula[0] ");
            }
            for ($e=0; $e<3; $e++)
            {
                $formula[0].="*".$formula[0];
                $this->assertEquals(\Impress::check_formula($formula[0]), $formula[1], " with $formula[0] ");
            }
        }
    }

    /***
     * @covers Impress::parse_formula
     */

    function test_compute_amount()
    {
        global $g_connection;
        // filter on period
        $sql_periode="j_date >= '2018-01-01' and j_date <= '2018-12-31'";
        
        // 0 = formula , 1 is the expected value
        $aFormula=array(
            ["[70%-s]", -456.8000],
            ["[61%-s]", 1491.5000],
            ["[61%-S]", -1491.5000],
            ["[61%-d]", 1591.50],
            ["[61%-c]", 100.00]
        );
        foreach ($aFormula as $formula) {
            $this->assertEquals($formula[1],\Impress::compute_amount($g_connection,$formula[0],$sql_periode),
                    " {$formula[0]} = {$formula[1]}");
        }
    }

    /**
     * Test pattern
     * 
     */
    function test_pattern()
    {
        $a_formula=array(
            ["[4500%-s]+[60]-[770-d]+120",3],
            ["{TEL}+[60]*5",2],
            ["{TEL-s}+[60]",2],
            ["{TEL-S}+[60]",2],
            ["{TEL}+[60-s]",2],
            ["{{TEL}}+[60-s]",2]
        );
        foreach ($a_formula as $p_formula)
        {
            $formula=$p_formula[0];
            $result=$p_formula[1];
            $this->assertEquals(preg_match_all(\Impress::STR_PATTERN, $formula, $e),$result,
                        " check $formula returns $result");
            
            
        }
    }
    /**
     * @covers compute_periode
     */
    function test_computePeriode()
    {
        global $g_connection,$g_parameter,$g_user;
        $g_user->set_periode(110);
        $this->assertEquals(trim("j_tech_per in (select p_id from parm_periode  where p_start >= to_date('01.01.2017','DD.MM.YYYY') and p_end <= to_date('30.11.2017','DD.MM.YYYY'))"),trim(\Impress::compute_periode($g_connection, "00.0000",115))
                ," SQL from 01.01.2017 to 01.11.2017");
        $this->assertEquals(
                trim("j_tech_per in (select p_id from parm_periode  where p_start >= to_date('01.02.2017','DD.MM.YYYY') and p_end <= to_date('31.10.2017','DD.MM.YYYY'))"),
                trim(\Impress::compute_periode($g_connection, "02.2017",114)),
                " SQL from 01.02.2017 to 01.10.2017");
        

    }
    function test_parse_formula()
    {
        global $g_connection,$g_user;
        $a_formula=array(
            array("sum 70% (credit is negative)", "[70%-s]",-456.80),
            array("sum 70% (credit is negative)", "0-round([70%-s],2)",456.80),
        );
        foreach ($a_formula as $formula) 
        {
            $result=\Impress::parse_formula($g_connection,$formula[0], $formula[1], 92, 103);
            $this->assertEquals($formula[2],$result['montant'],$formula[0]);
            
        }
    }
}
