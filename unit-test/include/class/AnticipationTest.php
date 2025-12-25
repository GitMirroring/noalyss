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
 * @backupGlobals disabled
 * @coversDefaultClass \Anticipation
 */
require DIRTEST.'/global.php';

class AnticipationTest extends TestCase
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

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test method is executed.
     */
    protected function tearDown():void
    {
        
    }

    /**
     * the setUpBeforeClass():void template methods is called before the first test of the test case
     *  class is run 
     */
    public static function setUpBeforeClass():void
    {
        include 'global.php';
        global $g_connection;
        $g_connection->exec_sql("delete from forecast");
    }

    /**
     *  tearDownAfterClass():void template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass():void
    {
        //        include 'global.php';
    }

    

    /**
     * @brief test insert, load , delete
     * @testdox insert into forecast
     * @covers ::insert ::delete ::load
     */
    public function testSQL_Anticipation()
    {
        global $g_connection;
        $forecast_sql=new Forecast_SQL($g_connection);
        $forecast_sql->setp("f_name","phpunit");
        $forecast_sql->setp("f_start_date",92);
        $forecast_sql->setp("f_end_date",103);
        $forecast_sql->save();
        $this->assertEquals (1,$g_connection->get_value("select count(*) from forecast")," forecast not created");
    }
    
    /**
     * @brief test update, load , insert, delete
     * @testdox update into forecast
     * @covers ::insert ::delete ::load
     */
    public function testClone()
    {
           global $g_connection;
        $forecast_sql=new Forecast_SQL($g_connection);
        $forecast_sql->setp("f_name","phpunit2");
        $forecast_sql->setp("f_start_date",92);
        $forecast_sql->setp("f_end_date",103);
        $forecast_sql->save();
        $id=$forecast_sql->getp('f_id');
        $anticipation = new \Anticipation($g_connection,$id);
        $clone_id = $anticipation->object_clone();
        $this->assertTrue(isNumber($clone_id)==1 && $clone_id > $id,"clone not created id = {$id} clone_id = {$clone_id}");
        
    }
    
    
}
