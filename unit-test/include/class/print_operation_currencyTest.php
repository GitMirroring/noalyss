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
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief test cprint_currency01.inc.php and, Print_Operation_Currency
 */

class Print_Operation_CurrencyTest extends TestCase
{

    protected function setUp():void
    {
        include 'global.php';
        
    }
    /**
     * @brief Test Filter_Data_Currency_Card_Category
     * @covers Filter_Data_Currency_Card_Category
     */
    function testFilter_data_currency_card_categoryTest()
    {
        $cn=Dossier::connect();
        $from_date='01.01.2019';
        $to_date='31.12.2020';
        $currency_id=0;
        $card_category=2;
        $object=new Filter_Data_Currency_Card_Category($cn, 
                $from_date, 
                $to_date,
                $currency_id, 
                $card_category);
        $sql=$object->SQL_Condition();
       
        $this->assertStringContainsString(" and jrnx.f_id in ( select f_id from fiche where fd_id=$4)",$sql);
        $array=$object->get_data();
        $this->assertEquals(count($array),0,"operation in EURO");
        
        
        $object->setCurrency_id(1);
        $array=$object->get_data();
        $this->assertEquals(count($array),5,"operations in Dollars");
        
    }
    /**
     * @brief Test Data_Currency_Operation
     * @covers Data_Currency_Operation
     */
    function testData_currency_operationTest()
    {
       $cn=Dossier::connect();
        $from_date='01.01.2019';
         $to_date='31.12.2020';
        $currency_id=0;
       
        $object=new Data_Currency_Operation($cn, 
                $from_date, 
                $to_date,
                $currency_id 
                );
        $sql=$object->SQL_Condition();
       
        $this->assertStringContainsString("where jrn.currency_id = $1 and",$sql);
        $array=$object->get_data();
        $this->assertEquals(0,count($array),"operation in EURO");
        
        
        $object->setCurrency_id(1);
        $array=$object->get_data();
        $this->assertEquals(18,count($array),"operations in Dollars");
    }
    /**
     * @brief test Filter_Data_Currency_Card
     * @covers Filter_Data_Currency_Card 
     */
      function testFilter_data_currency_cardTest()
      {
        $cn=Dossier::connect();
        $from_date='01.01.2020';
         $to_date='31.12.2020';
        $currency_id=0;
        $card='FOURNI1';
        $object=new Filter_Data_Currency_Card($cn, 
                $from_date, 
                $to_date,
                $currency_id, 
                $card);
        $sql=$object->SQL_Condition();
       
        $this->assertStringContainsString("and f_id=$4",$sql);
        $array=$object->get_data();
        $this->assertEquals(0,count($array),"operation in EURO");
        
        
        $object->setCurrency_id(1);
        $object->setCard("FOURNI1");
        $array=$object->get_data();
        $this->assertEquals(2,count($array),"operations in Dollars");
      }
      /**
       * @brief test Filter_Data_Currency_Card_Accounting
       * @covers Filter_Data_Currency_Card_Accounting
       */
      function testFilter_data_currency_accounting()
      {
        $cn=Dossier::connect();
        $from_date='01.01.2018';
         $to_date='31.12.2020';
        $currency_id=0;
        $from_poste='40';
        $to_poste='450';
        $object=new Filter_Data_Currency_Accounting($cn, 
                $from_date, 
                $to_date,
                $currency_id, 
                $from_poste,
                $to_poste);
        
        $sql=$object->SQL_Condition();
        $this->assertStringContainsString("and j_poste >= $4 and j_poste <= $5",$sql);
        $array=$object->get_data();
        $this->assertEquals(0,count($array),"operation in EURO");
        
        
        $object->setCurrency_id(1);
       
        $array=$object->get_data();
        $this->assertEquals(7,count($array),"operations in Dollars");
      }
      function testPrint_operation_currency()
      {
        $_REQUEST['from_date']='01.01.2000';
        $_REQUEST['to_date']='31.12.2020';
        $_REQUEST['p_currency_code']=0;
        $_REQUEST['from_account']='40';
        $_REQUEST['to_account']='450';
        $_REQUEST['card']='FOURNI1';
        $_REQUEST['search_type']='by_accounting';
        
        $print_operation=Print_Operation_Currency::build("by_accounting");
        $array=$print_operation->getData_operation()->get_data();
        $export=new Noalyss_CSV("test");
        Noalyss\Facility::save_file(__DIR__."/file", "print_operation_currency_by_accounting.html",
                Noalyss\Facility::page_start().$print_operation->export_html());
        ob_start();
        $print_operation->export_csv($export);
        $csv=ob_get_contents();
        ob_end_clean();
        
        Noalyss\Facility::save_file(__DIR__."/file", "print_operation_currency_by_accounting.csv",
                $csv);
        
        $this->assertEquals(0,count($array),"by_accounting operation in EURO"); 
        
        $_REQUEST['p_currency_code']=1;
        $print_operation=Print_Operation_Currency::build("by_card");
        $array=$print_operation->getData_operation()->get_data();
        
        Noalyss\Facility::save_file(__DIR__."/file", "print_operation_currency_by_card.html",
                Noalyss\Facility::page_start().$print_operation->export_html());
        ob_start();
        $print_operation->export_csv($export);
        $csv=ob_get_contents();
        ob_end_clean();
        
        Noalyss\Facility::save_file(__DIR__."/file", "print_operation_currency_by_card.csv",
                $csv);

          
        
        $this->assertEquals(2,count($array),"by_card operation in USD"); 
        
        $print_operation=Print_Operation_Currency::build("all");
        $array=$print_operation->getData_operation()->get_data();
        $this->assertEquals(20,count($array),"all operations in Dollars");
        ob_start();
        $print_operation->export_csv($export);
        $csv=ob_get_contents();
        ob_end_clean();
        
         Noalyss\Facility::save_file(__DIR__."/file", "print_operation_currency_all.csv",
              $csv)  ;
         
          Noalyss\Facility::save_file(__DIR__."/file", "print_operation_currency_all.html",
                Noalyss\Facility::page_start().$print_operation->export_html());
        
      }
}
