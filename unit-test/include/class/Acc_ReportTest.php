<?php

/*
 * * Copyright (C) 2022 Dany De Bontridder <dany@alchimerys.be>
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
 * @coversDefaultClass \Acc_Report
 */
require DIRTEST.'/global.php';

class Acc_ReportTest extends TestCase
{

    /**
     * @var Fiche
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test method is executed.
     */
    protected function setUp():void
    {
        include 'global.php';
    }
   
    public function test_getRowExistingForm() 
    {
        global $g_connection;
        $report=new Acc_Report($g_connection,3000000);
        $aResult=$report->get_row(132,144,0);
        $aResultDate=$report->get_row('01.01.2020','31.12.2020',1);
        $this->assertEquals($aResult,$aResultDate,"Acc_Report::get_row gives different results ");
        $this->assertFalse(empty($aResult),"Acc_Report::get_row retrieves a empty array");
        
    }
    
    public function test_getRowNotExistingForm()
    {
        global $g_connection;
        
        $report=new Acc_Report($g_connection,1);
        $aResult=$report->get_row(132,144,0);
        $aResultDate=$report->get_row('01.01.2020','31.12.2020',1);
        $this->assertEquals($aResult,$aResultDate,"Acc_Report::get_row gives different results ");
        $this->assertTrue(empty($aResult),"Acc_Report::get_row retrieves a empty array");
        
    }

}
