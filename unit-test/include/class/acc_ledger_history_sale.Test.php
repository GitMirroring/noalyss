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
 * @brief concerne Acc_Ledger_History_SaleTest.class
 */
class Acc_Ledger_History_SaleTest extends TestCase
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
        global $g_connection;
        $this->object=new Acc_Ledger_History_Sale($g_connection,[2],92,131,'L');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }
    function testGet_row()
    {
        $this->object->get_row();
        $this->assertSame(10,count($this->object->get_data()),"Acc_Ledger_History_Sale->get_row");
    }
     private function  save_file($p_name,$content)
    {
        $hFile=fopen(__DIR__."/file/".$p_name,"w+");
        fwrite($hFile, $content);
        fclose($hFile);
    }
    //@covers Acc_Ledger_History_Sale::export_oneline_html
    function testExport_Oneline_Html()
    {
        //- Listing
        $name="acc_ledger_history_sale_export_listing.html";
        $this->object->set_m_mode("L");
        ob_start();
        echo \Noalyss\Facility::page_start();
        $this->object->export_html();
        $content=ob_get_contents();
        ob_end_clean();
        $this->save_file($name, $content);
        $this->assertFileExists(__DIR__."/file/".$name);
        
        //- Extended
        $name="acc_ledger_history_sale_export_extended.html";
        $this->object->set_m_mode("E");
        ob_start();
        echo \Noalyss\Facility::page_start();
        $this->object->export_html();
        $content=ob_get_contents();
        ob_end_clean();
        $this->save_file($name, $content);
        $this->assertFileExists(__DIR__."/file/".$name);
        
        //- Detail
        $name="acc_ledger_history_sale_export_detail.html";
        $this->object->set_m_mode("D");
        ob_start();
        echo \Noalyss\Facility::page_start();
        $this->object->export_html();
        $content=ob_get_contents();
        ob_end_clean();
        $this->save_file($name, $content);
        $this->assertFileExists(__DIR__."/file/".$name);

        //- Accounting
        $name="acc_ledger_history_sale_export_accounting.html";
        $this->object->set_m_mode("D");
        ob_start();
        echo \Noalyss\Facility::page_start();
        $this->object->export_html();
        $content=ob_get_contents();
        ob_end_clean();
        $this->save_file($name, $content);
        $this->assertFileExists(__DIR__."/file/".$name);
    }
     /**
     * @covers Acc_Ledger_History::get_ledger_type
     */
    
    function testGet_Ledger_type()
    {
        $this->assertEquals($this->object->get_ledger_type(),'VEN');
    }
}
