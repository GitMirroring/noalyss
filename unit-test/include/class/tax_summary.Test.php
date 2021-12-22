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
    protected function setUp():void
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
    private function check_result ($p_scenario , $p_array,$p_array_expected,$jrn_type)
    {
        $nb_result=count($p_array_expected);
        $nb_array=count($p_array);
        $this->assertEquals($nb_result, $nb_array,$p_scenario);
        $ix=0;
        switch ($jrn_type)
        {
            case "ACH":
                $vat_code="qp_vat_code";
                break;
            case "SMRACH":
                $vat_code="qp_vat_code";
                break;
            case "VEN":
                $vat_code="qs_vat_code";
                break;
            case "SMRVEN":
                $vat_code="qs_vat_code";
                break;

            default:
                throw new Exception(_("unknow jrn_type"));
                break;
        }
        for ($i=0; $i<$nb_result; $i++)
        {
            $row=$p_array_expected[$i];
            for ($e=0; $e<$nb_array; $e++)
            {
                $test_jrn_expected="";
                $test_jrn_result="";
                if ( in_array($jrn_type, ['ACH','VEN'])) 
                {
                    $test_jrn_expected=$row['jrn_def_name'] ;
                    $test_jrn_result=$p_array[$e]["jrn_def_name"];
                }
                if (
                        $row[$vat_code]==$p_array[$e][$vat_code]
                        && $test_jrn_expected == $test_jrn_result
                    )
                {
                    $this->assertEquals($row['amount_vat'], $p_array[$e]['amount_vat']
                            ,sprintf("%s Code %s ",$p_scenario,$row[$vat_code]));
                    
                    $this->assertEquals($row['amount_sided'], $p_array[$e]['amount_sided']
                            ,sprintf("%s Code %s ",$p_scenario,$row[$vat_code]));
                    
                    $this->assertEquals($row['amount_wovat'], $p_array[$e]['amount_wovat']
                        ,sprintf("%s Code %s ",$p_scenario,$row[$vat_code]));
                    
                    if ( $jrn_type=="ACH") {

                        $this->assertEquals($row['amount_noded_amount'], $p_array[$e]['amount_noded_amount']
                                ,sprintf("%s Code %s ",$p_scenario,$row[$vat_code]));
                        $this->assertEquals($row['amount_noded_tax'], $p_array[$e]['amount_noded_tax']
                                ,sprintf("%s Code %s ",$p_scenario,$row[$vat_code]));
                        $this->assertEquals($row['amount_noded_return'], $p_array[$e]['amount_noded_return']
                                ,sprintf("%s Code %s ",$p_scenario,$row[$vat_code]));
                        $this->assertEquals($row['amount_private'], $p_array[$e]['amount_private']
                                ,sprintf("%s Code %s ",$p_scenario,$row[$vat_code]));
                    }
                    $ix++;
                }
            }
        }
        $this->assertEquals($nb_array, $ix, 'Not all VAT CODE found');
    }
    function testGet_row_purchase()
    {
        // Operation date
        $this->object->set_tva_type("O");
        $array=$this->object->get_row_purchase();
        //-- For creating the array
        // Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getrow_purchase.txt", var_export($array, TRUE));
        require __DIR__."/data/tax_summary_getrow_purchase_o.php";
        $this->check_result("tva_type = O , get_row_purchase",$array,$a_result,'ACH');

        // Operation date
        $this->object->set_tva_type("P");
        $array=$this->object->get_row_purchase();
        //-- For creating the array
        // Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getrow_purchase.txt", var_export($array, TRUE));
        require __DIR__."/data/tax_summary_getrow_purchase_p.php";
        $this->check_result("tva_type = P , get_row_purchase",$array,$a_result,'ACH');
        
        $this->object->set_tva_type("T");
        $array=$this->object->get_row_purchase();
        //-- For creating the array
        //Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getrow_purchase.txt", var_export($array, TRUE));
        require __DIR__."/data/tax_summary_getrow_purchase_t.php";
        $this->check_result("tva_type = T , get_row_purchase",$array,$a_result,'ACH');
        
        
        
        
    }

    function testGet_row_sale()
    {
         $this->object->set_tva_type("O");
        $array=$this->object->get_row_sale();
        //-- For creating the array
         Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getrow_sale_o.txt", var_export($array, TRUE));
       require __DIR__."/data/tax_summary_getrow_sale_o.php";
        $this->check_result("tva_type = O , get_row_sale",$array,$a_result,'VEN');
         
         
         $this->object->set_tva_type("P");
        $array=$this->object->get_row_sale();
        //-- For creating the array
       Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getrow_sale_p.txt", var_export($array, TRUE));
        require __DIR__."/data/tax_summary_getrow_sale_p.php";
        $this->check_result("tva_type = P , get_row_sale",$array,$a_result,'VEN');

         
         $this->object->set_tva_type("T");
         $array=$this->object->get_row_sale();
        //-- For creating the array
        Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getrow_sale_t.txt", var_export($array, TRUE));
        require __DIR__."/data/tax_summary_getrow_sale_t.php";
        $this->check_result("tva_type = T , get_row_sale",$array,$a_result,'VEN');
        
    }

    function testget_summary_purchase()
    {
         $this->object->set_tva_type("T");
        $array=$this->object->get_summary_purchase();
        //-- For creating the array
        Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getsummary_purchase_t.txt", var_export($array, TRUE));
        require __DIR__."/data/tax_summary_getsummary_purchase_t.php";
        $this->check_result("tva_type = T , get_summary_purchase",$array,$a_result,'SMRACH');
       
         $this->object->set_tva_type("O");
        $array=$this->object->get_summary_purchase();
        //-- For creating the array
        Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getsummary_purchase_o.txt", var_export($array, TRUE));
        require __DIR__."/data/tax_summary_getsummary_purchase_o.php";
        $this->check_result("tva_type = O , get_summary_purchase",$array,$a_result,'SMRACH');
       
        $this->object->set_tva_type("P");
        $array=$this->object->get_summary_purchase();
        //-- For creating the array
        Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getsummary_purchase_p.txt", var_export($array, TRUE));
        require __DIR__."/data/tax_summary_getsummary_purchase_p.php";
        $this->check_result("tva_type = P , get_summary_purchase",$array,$a_result,'SMRACH');
       
       
    }

    function testget_summary_sale()
    {
        $this->object->set_tva_type("O");
        $array=$this->object->get_summary_sale();
        //-- For creating the array
        Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getsummary_sale_o.txt", var_export($array, TRUE));
        require __DIR__."/data/tax_summary_getsummary_sale_o.php";
        $this->check_result("tva_type = O , get_summary_sale",$array,$a_result,'SMRVEN');
        
        $this->object->set_tva_type("P");
        $array=$this->object->get_summary_sale();
        //-- For creating the array
        // Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getsummary_sale_p.txt", var_export($array, TRUE));
        require __DIR__."/data/tax_summary_getsummary_sale_p.php";
        $this->check_result("tva_type = P , get_summary_sale",$array,$a_result,'SMRVEN');
        
        
        $this->object->set_tva_type("T");
        $array=$this->object->get_summary_sale();
        //-- For creating the array
        Noalyss\Facility::save_file(__DIR__."/file", "tax_summary_getsummary_sale_t.txt", var_export($array, TRUE));
        require __DIR__."/data/tax_summary_getsummary_sale_t.php";
        $this->check_result("tva_type = T , get_summary_sale",$array,$a_result,'SMRVEN');


        
    }

}
