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
 * @brief  Test Anc_Group
 */
use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 * @coversDefaultClass \Anc_Group
 */
require DIRTEST.'/global.php';

class Anc_GroupTest extends TestCase
{


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
        $g_connection->exec_sql("delete from plan_analytique where pa_id>1");
        $g_connection->exec_sql("INSERT INTO plan_analytique (pa_id,pa_name,pa_description) VALUES
	 (2,'AXE2','Axe 2'),
	 (3,'AXE3','Axe 3')");
        
    }

    /**
     *  tearDownAfterClass():void template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass():void
    {
        include 'global.php';
         global $g_connection;
        $g_connection->exec_sql("delete from plan_analytique where pa_id>1");
    }

    public function datasqlStmt()
    {
        return array([1,"Group 1",1],
                [2,"Group 2",1],
                [3,"Group 3",1],
                [4,"Group 4",null]
                );
    }

    /**
     * @brief 
     * @testdox insert, load and remove
     * @dataProvider datasqlStmt
     * @param type $p_param
     * @covers
     */
    public function test_sqlStmt($p_ga_id,$pga_description,$ppa_id)
    {
        global $g_connection;
        // check group empty
        $this->assertEquals(0,$g_connection->get_value("select count(*) from groupe_analytique"),
                    "Table not empty");
        $obj=new Anc_Group($g_connection);
        $obj->ga_id=$p_ga_id;
        $obj->ga_description=$pga_description;
        $obj->pa_id=$ppa_id;
        $obj->insert();
        $this->assertEquals(1,$g_connection->get_value("select count(*) from groupe_analytique"),
                    "record not inserted");
        // load it
        $reload=new Anc_Group($g_connection);
        $reload->ga_id=$obj->ga_id;
        $reload->load();
        $this->assertEquals($reload->ga_description,$obj->ga_description,"Cannot load from db");
        
        // remove it
        $reload->remove();
         $this->assertEquals(0,$g_connection->get_value("select count(*) from groupe_analytique"),
                    "Cannot remove");
    }

}
