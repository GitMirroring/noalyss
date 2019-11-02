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
    protected function setUp(): void
    {
        global $g_connection;
        $this->object=new \Tax_Summary($g_connection, "01.01.2014", "31.12.2019");
    }

    function testCheck()
    {
        // No warning expected
        try
        {
            $this->object->check();
            $this->assertTrue(TRUE);
        }
        catch (Exception $e)
        {
            $this->assertSame('OK', $e->getMessage());
        }
        // Warning expected
        try
        {
            $this->object->set_date_start("01.01.2090");
            $this->object->check();
            $this->assertTrue(FALSE);
        }
        catch (Exception $e)
        {
            $this->assertTrue(TRUE);
        }
    }

    function testDisplay()
    {
        $this->expectOutputRegex("/.*0,00.*\<\/tr\>\<\/table\>\n/");
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
        //-- For creating the array
        Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getrow_purchase.txt", print_r($array, TRUE));
        $a_result=array
            (
            "0"=>array
                (
                "jrn_def_name"=>"Achat",
                "tva_label"=>"0%",
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "qp_vat_code"=>4,
                "amount_vat"=>0.0000,
                "amount_wovat"=>658.2500,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "1"=>array
                (
                "jrn_def_name"=>"Achat",
                "tva_label"=>"12%",
                "tva_rate"=>0.1200,
                "tva_both_side"=>0,
                "qp_vat_code"=>2,
                "amount_vat"=>109.8000,
                "amount_wovat"=>915.0000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "2"=>array
                (
                "jrn_def_name"=>"Achat",
                "tva_label"=>"21%",
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "qp_vat_code"=>1,
                "amount_vat"=>6.1000,
                "amount_wovat"=>29.0000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "3"=>array
                (
                "jrn_def_name"=>"Achat",
                "tva_label"=>"6%",
                "tva_rate"=>0.0600,
                "tva_both_side"=>0,
                "qp_vat_code"=>3,
                "amount_vat"=>5.2800,
                "amount_wovat"=>88.3200,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "4"=>array
                (
                "jrn_def_name"=>"Achat",
                "tva_label"=>"ART44",
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "qp_vat_code"=>8,
                "amount_vat"=>0.0000,
                "amount_wovat"=>315.2000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "5"=>array
                (
                "jrn_def_name"=>"Achat",
                "tva_label"=>"EXPORT",
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "qp_vat_code"=>6,
                "amount_vat"=>0.0000,
                "amount_wovat"=>286.9500,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "6"=>array
                (
                "jrn_def_name"=>"Achat",
                "tva_label"=>"IMMO",
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "qp_vat_code"=>1002,
                "amount_vat"=>319.2400,
                "amount_wovat"=>1520.2000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "7"=>array
                (
                "jrn_def_name"=>"Achat",
                "tva_label"=>"VOIT",
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "qp_vat_code"=>1003,
                "amount_vat"=>21.0000,
                "amount_wovat"=>100.0000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "8"=>array
                (
                "jrn_def_name"=>"Frais Divers",
                "tva_label"=>"21%",
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "qp_vat_code"=>1,
                "amount_vat"=>244.5500,
                "amount_wovat"=>1164.5400,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "9"=>array
                (
                "jrn_def_name"=>"Frais Divers",
                "tva_label"=>"EXPORT",
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "qp_vat_code"=>6,
                "amount_vat"=>0.0000,
                "amount_wovat"=>259.8000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "10"=>array
                (
                "jrn_def_name"=>"Frais Divers",
                "tva_label"=>"IMMO",
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "qp_vat_code"=>1002,
                "amount_vat"=>319.2400,
                "amount_wovat"=>1520.2000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "11"=>array
                (
                "jrn_def_name"=>"Frais Divers",
                "tva_label"=>"INTRA",
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "qp_vat_code"=>5,
                "amount_vat"=>0.0000,
                "amount_wovat"=>1250.0000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "12"=>array
                (
                "jrn_def_name"=>"Frais Divers",
                "tva_label"=>"VOIT",
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "qp_vat_code"=>1003,
                "amount_vat"=>42.0000,
                "amount_wovat"=>200.0000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000,
            )
        );
        $nb_result=count($a_result);
        $nb_array=count($array);
        $this->assertEquals($nb_result, $nb_array);
        $ix=0;
        for ($i=0; $i<$nb_result; $i++)
        {
            $row=$a_result[$i];
            for ($e=0; $e<$nb_array; $e++)
            {
                if (
                        $row['qp_vat_code']==$array[$e]['qp_vat_code']
                        && $row['jrn_def_name'] == $array[$e]["jrn_def_name"]
                    )
                {
                    $this->assertEquals($row['amount_vat'], $array[$e]['amount_vat']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $this->assertEquals($row['amount_wovat'], $array[$e]['amount_wovat']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $this->assertEquals($row['amount_sided'], $array[$e]['amount_sided']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $this->assertEquals($row['amount_noded_amount'], $array[$e]['amount_noded_amount']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $this->assertEquals($row['amount_noded_tax'], $array[$e]['amount_noded_tax']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $this->assertEquals($row['amount_noded_return'], $array[$e]['amount_noded_return']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $this->assertEquals($row['amount_private'], $array[$e]['amount_private']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $ix++;
                }
            }
        }
        $this->assertEquals($nb_array, $ix, 'Not all VAT CODE found');
    }

    function testGet_row_sale()
    {
        $array=$this->object->get_row_sale();
        //-- For creating the array
       //Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getrow_sale.txt", print_r($array, TRUE));
        $a_result=array
            (
            "0"=>array
                (
                "jrn_def_name"=>"Vente",
                "tva_label"=>"21%",
                "qs_vat_code"=>1,
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "amount_vat"=>219.5700,
                "amount_wovat"=>1045.6000,
                "amount_sided"=>0.0000
            ),
            "1"=>array
                (
                "jrn_def_name"=>"Vente",
                "tva_label"=>"EXPORT",
                "qs_vat_code"=>6,
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "amount_vat"=>0.0000,
                "amount_wovat"=>1047.9000,
                "amount_sided"=>0.0000
            ),
            "2"=>array
                (
                "jrn_def_name"=>"Vente",
                "tva_label"=>"INTRA",
                "qs_vat_code"=>5,
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "amount_vat"=>0.0000,
                "amount_wovat"=>1800.0000,
                "amount_sided"=>0.0000
            ),
            "3"=>array
                (
                "jrn_def_name"=>"Vente",
                "tva_label"=>"VOIT",
                "qs_vat_code"=>1003,
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "amount_vat"=>8.4000,
                "amount_wovat"=>40.0000,
                "amount_sided"=>0.0000
            ),
            "4"=>Array
                (
                "jrn_def_name"=>"Vente différée",
                "tva_label"=>"EXPORT",
                "qs_vat_code"=>6,
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "amount_vat"=>0.0000,
                "amount_wovat"=>150.5600,
                "amount_sided"=>0.0000
            )
        );
        $nb_result=count($a_result);
        $nb_array=count($array);
        $this->assertEquals($nb_result, $nb_array);
        $ix=0;
        for ($i=0; $i<$nb_result; $i++)
        {
            $row=$a_result[$i];
            for ($e=0; $e<$nb_array; $e++)
            {
                if (
                        $row['qs_vat_code']==$array[$e]['qs_vat_code']&&$row['jrn_def_name']==$array[$e]["jrn_def_name"]
                )
                {
                    $this->assertEquals($row['amount_vat'], $array[$e]['amount_vat']
                            , sprintf("Code %s ", $row['qs_vat_code']));
                    $this->assertEquals($row['amount_wovat'], $array[$e]['amount_wovat']
                            , sprintf("Code %s ", $row['qs_vat_code']));
                    $this->assertEquals($row['amount_sided'], $array[$e]['amount_sided']
                            , sprintf("Code %s ", $row['qs_vat_code']));
                    $ix++;
                }
            }
        }
        $this->assertEquals($nb_array, $ix, 'Not all VAT CODE found');
    }

    function testget_summary_purchase()
    {
        $array=$this->object->get_summary_purchase();
        //-- For creating the array
        // \Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_summary_purchase.txt", print_r($array, TRUE));
        $a_result=array
            (
            "0"=>array
                (
                "tva_label"=>"0%",
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "qp_vat_code"=>4,
                "amount_vat"=>0.0000,
                "amount_wovat"=>658.2500,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "1"=>array
                (
                "tva_label"=>"12%",
                "tva_rate"=>0.1200,
                "tva_both_side"=>0,
                "qp_vat_code"=>2,
                "amount_vat"=>109.8000,
                "amount_wovat"=>915.0000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "2"=>array
                (
                "tva_label"=>"21%",
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "qp_vat_code"=>1,
                "amount_vat"=>250.6500,
                "amount_wovat"=>1193.5400,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "3"=>array
                (
                "tva_label"=>"6%",
                "tva_rate"=>0.0600,
                "tva_both_side"=>0,
                "qp_vat_code"=>3,
                "amount_vat"=>5.2800,
                "amount_wovat"=>88.3200,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "4"=>array
                (
                "tva_label"=>"ART44",
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "qp_vat_code"=>8,
                "amount_vat"=>0.0000,
                "amount_wovat"=>315.2000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "5"=>array
                (
                "tva_label"=>"EXPORT",
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "qp_vat_code"=>6,
                "amount_vat"=>0.0000,
                "amount_wovat"=>546.7500,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "6"=>array
                (
                "tva_label"=>"IMMO",
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "qp_vat_code"=>1002,
                "amount_vat"=>638.4800,
                "amount_wovat"=>3040.4000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "7"=>array
                (
                "tva_label"=>"INTRA",
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "qp_vat_code"=>5,
                "amount_vat"=>0.0000,
                "amount_wovat"=>1250.0000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            ),
            "8"=>array
                (
                "tva_label"=>"VOIT",
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "qp_vat_code"=>1003,
                "amount_vat"=>63.0000,
                "amount_wovat"=>300.0000,
                "amount_sided"=>0.0000,
                "amount_noded_amount"=>0.0000,
                "amount_noded_tax"=>0.0000,
                "amount_noded_return"=>0.0000,
                "amount_private"=>0.0000
            )
        );
        $nb_result=count($a_result);
        $nb_array=count($array);
        $this->assertEquals($nb_result, $nb_array);
        $ix=0;
        for ($i=0; $i<$nb_result; $i++)
        {
            $row=$a_result[$i];
            for ($e=0; $e<$nb_array; $e++)
            {
                if ($row['qp_vat_code']==$array[$e]['qp_vat_code'])
                {
                    $this->assertEquals($row['amount_vat'], $array[$e]['amount_vat']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $this->assertEquals($row['amount_wovat'], $array[$e]['amount_wovat']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $this->assertEquals($row['amount_sided'], $array[$e]['amount_sided']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $this->assertEquals($row['amount_noded_amount'], $array[$e]['amount_noded_amount']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $this->assertEquals($row['amount_noded_tax'], $array[$e]['amount_noded_tax']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $this->assertEquals($row['amount_noded_return'], $array[$e]['amount_noded_return']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $this->assertEquals($row['amount_private'], $array[$e]['amount_private']
                            ,sprintf("Code %s ",$row['qp_vat_code']));
                    $ix++;
                }
            }
        }
        $this->assertEquals($nb_array, $ix, 'Not all VAT CODE found');
    }

    function testget_summary_sale()
    {
        $array=$this->object->get_summary_sale();
        //-- For creating the array
        // Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_summary_sale.txt", print_r($array, TRUE));

        $a_result=array
            (
            array
                (
                "tva_label"=>"21%",
                "qs_vat_code"=>1,
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "amount_vat"=>219.5700,
                "amount_wovat"=>1045.6000,
                "amount_sided"=>0.0000
            ),
            array
                (
                "tva_label"=>"EXPORT",
                "qs_vat_code"=>6,
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "amount_vat"=>0.0000,
                "amount_wovat"=>1198.4600,
                "amount_sided"=>0.0000
            ),
            array
                (
                "tva_label"=>"INTRA",
                "qs_vat_code"=>5,
                "tva_rate"=>0.0000,
                "tva_both_side"=>0,
                "amount_vat"=>0.0000,
                "amount_wovat"=>1800.0000,
                "amount_sided"=>0.0000
            ),
            array
                (
                "tva_label"=>"VOIT",
                "qs_vat_code"=>1003,
                "tva_rate"=>0.2100,
                "tva_both_side"=>0,
                "amount_vat"=>8.4000,
                "amount_wovat"=>40.0000,
                "amount_sided"=>0.0000
            )
        );
        $nb_result=count($a_result);
        $nb_array=count($array);
        $this->assertEquals($nb_result, $nb_array);
        $ix=0;
        for ($i=0; $i<$nb_result; $i++)
        {
            $row=$a_result[$i];
            for ($e=0; $e<$nb_array; $e++)
            {
                if ($row['qs_vat_code']==$array[$e]['qs_vat_code'])
                {
                    $this->assertEquals($row['amount_vat'], $array[$e]['amount_vat']
                            ,sprintf("Code %s ",$row['qs_vat_code']));
                    $this->assertEquals($row['amount_wovat'], $array[$e]['amount_wovat']
                            ,sprintf("Code %s ",$row['qs_vat_code']));
                    $this->assertEquals($row['amount_sided'], $array[$e]['amount_sided']
                            ,sprintf("Code %s ",$row['qs_vat_code']));
                    $ix++;
                }
            }
        }
        $this->assertEquals($nb_array, $ix, 'Not all VAT CODE found');
    }

}
