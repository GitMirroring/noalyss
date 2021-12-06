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
 * @coversDefaultClass \Forecast
 */
require 'global.php';

class ForecastTest extends TestCase
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
        global $g_connection;
        $g_connection->exec_sql('delete from forecast');
    }

    /**
     *  tearDownAfterClass() template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass()
    {
        //        include 'global.php';
    }

    

    /**
     * @brief test insert, load , delete
     * @testdox insert into forecast
     * @covers ::insert ::delete ::load
     */
    public function testSQL_Insert()
    {
        global $g_connection;
        $obj=new Forecast($g_connection);
        $obj->set_parameter("start_date", 111);
        $obj->set_parameter("end_date", 116);
        $obj->set_parameter("name","PhpUNIT 2014");
        $obj->insert();
        $this->assertTrue($obj->get_parameter("id")>0,"Cannot insert");
        $obj->delete();
        $this->assertFalse($obj->load(),"Cannot delete");
    }
    
    /**
     * @brief test update, load , insert, delete
     * @testdox update into forecast
     * @covers ::insert ::delete ::load
     */
    public function testSQL_Update()
    {
        global $g_connection;
        $obj=new Forecast($g_connection);
        $obj->set_parameter("start_date", 111);
        $obj->set_parameter("end_date", 116);
        $obj->set_parameter("name", "PhpUNIT 2014");
        $obj->insert();
        
        $this->assertTrue($obj->get_parameter("id")>0,"Cannot insert");
        $obj->set_parameter('name','TEST UPDATE');
        $obj->save();
        $reload=new Forecast($g_connection,$obj->get_parameter("id"));
        $reload->load();
        $this->assertTrue($reload->get_parameter("name") == $obj->get_parameter("name"),"cannot update");
        $reload->delete();
        $this->assertFalse($reload->load(),"Cannot delete");
        
    }
    
}
