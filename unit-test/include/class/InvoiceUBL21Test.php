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


/**
 * @testdox Class InvoiceUBL21 : used for managing invoice format UBL21
 * @backupGlobals false
 * @coversDefaultClass InvoiceUBL21Test
 */
class InvoiceUBL21Test extends TestCase {

    /**
     * @var Fiche
     */
    protected $object;
    protected $connection;

    
    /**
     * @testdox thx jr_id makes an $array
     * @covers \Noalyss\Invoice\XMLDocument\InvoiceUBL21::build_data
     */
    function testBuild_Data() {
        $cn = \Dossier::connect();
        $ublinvoice21 = new \Noalyss\XMLDocument\InvoiceUBL21($cn);
    }
    
    /**
     * @brief check that the parameter extra has a value or add a temporary one
     * @param $code (string) code to check
     * @param $value (string) temp value
     * @return true : operation succeed , false code doesn't exist
     */
    function parameter_extra_set($code,$value)
    {
        $cn = \Dossier::connect();
        $c=$cn->get_value("select pe_value from parameter_extra where pe_code = $1",
                [$code]);
        $id=1;
        if ( $c == "") {
            $id = $cn->get_value(" update parameter_extra set pe_value=$1 where pe_code=$2 returning id",
                    [$code,$value]);
            
        }
        return ($id != "")?true:false;
    }
    
    /**
     * @brief if parameter extra has a temp value reset it to null
     * @param $code (string) code to check
     * @param $value (string) temp value
     * @return true : operation succeed , false code doesn't exist
     */
    function parameter_extra_clean($code,$value)
    {
        $cn = \Dossier::connect();
        $c=$cn->get_value("select pe_value from parameter_extra where pe_code = $1",
                [$code]);
        $id=1;
        if ( $c == $value ) {
            $id = $cn->get_value(" update parameter_extra set pe_value=null where pe_code=$1 returning id",
                    [$code]);
            
        }
        return ($id != "")?true:false;
    }
    /**
     * @testdox Make an XML UBL21, create the PDF invoice and include it into the XML
     */
    function testDOMMake_XML() {
     try {

            $cn = \Dossier::connect();
            // clean first 
            $cn->exec_sql('update jrn set jr_pj_name=null,jr_pj_type=null, jr_pj = null where jr_id=$1',[2]);
            $tmp_value= uniqid("phpunit");
        // COMPANY_BANK_IBAN and COMPANY_BANK_BIC must have a value
            
            if ( ! $this->parameter_extra_set('COMPANY_BANK_IBAN',$tmp_value))
            {
                $this->assertTrue(false,"failed to set COMPANY_BANK_IBAN");
            }
            
            if ( ! $this->parameter_extra_set('COMPANY_BANK_BIC',$tmp_value) )
            {
                $this->assertTrue(false,"failed to set COMPANY_BANK_IBAN");
            }
            
            $cn->start();
            $ublinvoice21 = new \Noalyss\XMLDocument\InvoiceUBL21($cn);
            // create document
            $sold=new \Acc_Sold($cn,2);
            $sold->get();
            $array= \Acc_Ledger_Sale::convert_to_array($sold);
            $array['gen_doc']=-2; //<- Standard invoice
            $acc_document=new \Acc_Document($cn,2);
            $acc_document->create_document($sold->det->jr_internal, $array);
            $cn->commit();
            // verification du PDF dans DB
            $row=$cn->get_row("select jr_pj_name,jr_pj, jr_pj_type
                        from jrn 
                        where jr_id=$1
                    ",[2]);
                       
            $this->assertTrue("inv-std-VEN2-pdf"==$row['jr_pj_name'],"Incorrect invoice name");
            $this->assertTrue("application/pdf"==$row['jr_pj_type'],"Incorrect invoice type");
            $this->assertTrue($row['jr_pj'] != "","OID not created");
            $cn->start();
            $pdf_filename= tempnam("/tmp", "phpunit");
            \Noalyss\Facility::save_file ('/tmp', __FUNCTION__.".txt", $pdf_filename);
            $acc_document->export_file($pdf_filename);
            $cn->commit();

            
            $ublinvoice21->set_pdf_filename($pdf_filename);
            $ublinvoice21->make_xml(2);
            $data_id = $ublinvoice21->get_data()['id'];
            $file = fopen("invoice-" . $data_id . ".xml", "w+");
            fwrite($file, $ublinvoice21->saveXML());
            fclose($file);
            $this->assertTrue(file_exists("invoice-" . $data_id . ".xml")
                    ,"file not create");
            echo "save saved invoice {$data_id}.xml \n";
            $this->parameter_extra_clean('COMPANY_BANK_IBAN',$tmp_value);
            $this->parameter_extra_clean('COMPANY_BANK_BIC',$tmp_value);
         
     } catch (Exception $exc) {
         echo "ERROR" ; ///$exc->getTraceAsString();
     }
    }

    function testCompany_Data() {
        $cn = \Dossier::connect();
        $ublinvoice21 = new \Noalyss\XMLDocument\InvoiceUBL21($cn);
        $a_error=array();
        $a_error=$ublinvoice21->check_company_data();
        $this->assertTrue(count($a_error) ==8 , " nb of errors incorrect ".print_r($a_error,true));
    }

    function testCustomer_Data() {
        $cn = \Dossier::connect();
        $ublinvoice21 = new \Noalyss\XMLDocument\InvoiceUBL21($cn);
        $ublinvoice21->build_data(2);
        $customer = $ublinvoice21->get_data()['customer'];
         $a_error=$ublinvoice21->check_customer_data($customer['card_id']);
        $this->assertTrue(count($a_error) ==6 , " nb of errors incorrect ".print_r($a_error,true));
    }
}
