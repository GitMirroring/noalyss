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
 * @coversDefaultClass \Follow_Up_Detail
 */

#[\AllowDynamicProperties]
class FollowUp_DetailTest extends TestCase
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
        require DIRTEST.'/global.php';
        global $g_connection;
        $this->g_connection=$g_connection;
        $this->assertNotNull($this->g_connection , "Database",'invalide ressource');
        $this->assertEquals(get_class ($this->g_connection) , "Database",'invalide ressource');
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
     *  @backupGlobals enabled
     */
    public static function setUpBeforeClass():void
    {
        require DIRTEST.'/global.php';
        global $g_connection;
        $g_connection->exec_sql('delete from action_detail');

    }

    /**
     *  tearDownAfterClass():void template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass():void
    {
        //        include 'global.php';
    }

      private function insert(Follow_Up_Detail $pFollow_Detail) 
      {
        $pFollow_Detail->set_parameter("qcode", 23);
        $pFollow_Detail->set_parameter("text" ,"Marchandise 1");
        $pFollow_Detail->set_parameter("price_unit",5.30);
        $pFollow_Detail->set_parameter("quantity",12);
        $pFollow_Detail->set_parameter("tva_id",null);
        $pFollow_Detail->set_parameter("tva_amount", 0);
        $pFollow_Detail->set_parameter("total", 63.6);
        $pFollow_Detail->set_parameter("ag_id", 1);
        $pFollow_Detail->insert();
      }

    /**
     * @brief test insert, load , delete
     * @testdox insert into action_detail
     * @covers ::insert ::delete ::load
     */
    public function testSQL_Insert()
    {
        global $g_connection;
        $obj=new Follow_Up_Detail($g_connection);
        $this->insert($obj);
        $this->assertTrue($obj->get_parameter("id")>0,"Cannot insert");
        $obj->delete();
        $this->assertFalse($obj->load(),"Cannot delete");
    }
    
    /**
     * @brief test update, load , insert, delete
     * @testdox update into action_detail
     * @covers ::insert ::delete ::load
     */
    public function testSQL_Update()
    {



         $obj=new Follow_Up_Detail($this->g_connection);
        $this->insert($obj);
        $this->assertTrue($obj->get_parameter("id")>0,"Cannot insert");
        
        $obj->set_parameter('text','TEST UPDATE');
        $obj->save();
        $reload=new Follow_Up_Detail($this->g_connection,$obj->get_parameter("id"));
        $reload->load();
        $this->assertTrue($reload->get_parameter("text") == $obj->get_parameter("text"),"cannot update");
        $reload->delete();
        $this->assertFalse($reload->load(),"Cannot delete");
        
    }
    
}
