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
 * @brief 
 */

class Tax_SummaryTest extends TestCase
{
     /**
     * @var Acc_Account
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() :void
    {
        global $g_connection;
        $this->object=new \Tax_Summary($g_connection, "01.01.2014", "31.12.2019");
    }
    function testCheck()
    {
        // No warning expected
        try {
            $this->object->check();
            $this->assertTrue(TRUE);
        } catch(Exception $e) {
            $this->assertSame('OK',$e->getMessage());
        }
        // Warning expected
        try {
            $this->object->set_date_start("01.01.2090");
            $this->object->check();
            $this->assertTrue(FALSE);
        } catch(Exception $e) {
            $this->assertTrue(TRUE);
        }
    }
    function testDisplay()
    {
        $this->expectOutputRegex("/.*10\.737,34.*\<\/tr\>\<\/table\>\n/");
        $this->object->display();
    }
    function testForm_export_csv()
    {
        $this->expectOutputRegex("/\<form method=\"GET\".*/");
        $this->object->form_export_csv();
    }
    function testForm_export_pdf()
    {
        $this->expectOutputRegex("/\<form method=\"GET\".*/");
        $this->object->form_export_pdf();
    }
    function testGet_row_purchase()
    {
        $array=$this->object->get_row_purchase();
        $this->assertSame($array, [['jrn_def_name' => 'Achat',
                                'tva_label' => '0%',
                                'tva_rate' => '0.0000',
                                'tva_both_side' => '0',
                                'qp_vat_code' => '4',
                                'amount_vat' => '0.0000',
                                'amount_wovat' => '658.2500',
                                'amount_sided' => '0.0000',
                                'amount_noded_amount' => '0.0000',
                                'amount_noded_tax' => '0.0000',
                                'amount_noded_return' => '0.0000',
                                'amount_private' => '0.0000'
                                ],[
                                'jrn_def_name' => 'Achat',
                                 'tva_label' => '12%',
                                 'tva_rate' => '0.1200',
                                 'tva_both_side' => '0',
                                 'qp_vat_code' => '2',
                                 'amount_vat' => '84.2200',
                                 'amount_wovat' => '701.7800',
                                 'amount_sided' => '0.0000',
                                 'amount_noded_amount' => '0.0000',
                                 'amount_noded_tax' => '0.0000',
                                 'amount_noded_return' => '0.0000',
                                 'amount_private' => '0.0000']]);
    }
    function testGet_row_sale()
    {
        $array=$this->object->get_row_sale();
        $this->assertSame($array, [[
                            'jrn_def_name' => 'Vente',
                            'tva_label' => '21%',
                            'qs_vat_code' => '1',
                            'tva_rate' => '0.2100',
                            'tva_both_side' => '0',
                            'amount_vat' => '2254.8400',
                            'amount_wovat' => '10737.3400',
                            'amount_sided' => '0.0000'
                            ]]);
       
    }
    function testget_summary_purchase()
    {
        $array=$this->object->get_summary_purchase();
        $this->assertSame($array,[[
                'tva_label' => '0%'
                 ,'tva_rate' => '0.0000'
                    ,'tva_both_side' => '0'
                    ,'qp_vat_code' => '4'
                    ,'amount_vat' => '0.0000'
                    ,'amount_wovat' => '658.2500'
                    ,'amount_sided' => '0.0000'
                    ,'amount_noded_amount' => '0.0000'
                    ,'amount_noded_tax' => '0.0000'
                    ,'amount_noded_return' => '0.0000'
                    ,'amount_private' => '0.0000'
                        ],[
                            'tva_label' => '12%'
                    ,'tva_rate' => '0.1200'
                    ,'tva_both_side' => '0'
                    ,'qp_vat_code' => '2'
                    ,'amount_vat' => '84.2200'
                    ,'amount_wovat' => '701.7800'
                    ,'amount_sided' => '0.0000'
                    ,'amount_noded_amount' => '0.0000'
                    ,'amount_noded_tax' => '0.0000'
                    ,'amount_noded_return' => '0.0000'
                    ,'amount_private' => '0.0000'
            ]]);

    }
    function testget_summary_sale()
    {
        $array=$this->object->get_summary_sale();
         $this->assertSame($array, [[
                            'tva_label' => '21%',
                            'qs_vat_code' => '1',
                            'tva_rate' => '0.2100',
                            'tva_both_side' => '0',
                            'amount_vat' => '2254.8400',
                            'amount_wovat' => '10737.3400',
                            'amount_sided' => '0.0000'
                            ]]);
    }
    
}