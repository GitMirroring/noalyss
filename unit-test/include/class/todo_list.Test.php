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
 * @coversDefaultClass \Todo_List
 */
require 'global.php';

class Todo_ListTest extends TestCase
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
        require 'global.php';
        global $g_connection;
        $g_connection->exec_sql("delete from todo_list");
        $g_connection->exec_sql("alter sequence todo_list_tl_id_seq restart with 1000");
        
        $g_connection->exec_sql("INSERT INTO todo_list (tl_id,tl_date,tl_title,tl_desc,use_login,is_public)
            VALUES
	 (1,'2018-02-24','TVA','Déclaration TVA',$1,'N'),
	 (2,'2019-02-28','Test PHPUNIT','Test PHPUNIST',$1,'Y'),
	 (3,'2014-03-14','PhpUNit 2','Second test',$1,'N'),
	 (4,'2017-06-24','PhpUnit 3','Déclaration TVA',$1,'N'),
	 (5,'2016-06-18','PhpUNit 4','4 test',$1,'N');",array($_SESSION[SESSION_KEY.'g_user'] ));
    }

    /**
     * @brief 
     * @testdox Test load, load_all , delete, insert , update
     * @param type $p_param
     *s @covers
     */
    public function test_SQLStmt()
    {
        global $g_connection;
        $obj=new Todo_List($g_connection);
        $obj->set_parameter("id", 3);
        $obj->load();
        $this->assertEquals('14.03.2014',$obj->get_parameter("date"),"Wrong record");
        
        $obj->set_parameter("id", 5);
        $obj->load();
        $this->assertEquals('18.06.2016',$obj->get_parameter("date"),"Wrong record");
        
        $a_todo_list=$obj->load_all();
        $this->assertEquals(5,count($a_todo_list),"Wrong number of rows");
        foreach ($a_todo_list as $todo_list) {
            $idx=$todo_list["tl_id"];
            $date=$todo_list["tl_date"];
            if ( $idx==1) {
                $this->assertEquals('24.02.2018',$date,"Wrong date [$date]" );
            }elseif ($idx==2) 
            {
                $this->assertEquals('28.02.2019',$date,"Wrong date [$date]" );
                
            }
        }
        $obj->delete();
        $this->assertEquals(false,$obj->load(),"Not deleted");
        
        
        $obj=new Todo_List($g_connection);
        $obj->set_parameter("id", 1);
        $obj->load();
        
        $obj2=clone $obj;
        $obj2->set_parameter("id",0);
        $obj2->insert();
        
        $obj3=new Todo_List($g_connection);
        $obj3->set_parameter("id", $obj2->get_parameter("id"));
        $this->assertEquals(true,$obj3->load(),"Not inserted");
         
         $obj3->set_parameter("title","PHPUNIT-TITLE");
         $obj3->save();
         
        $obj4=new Todo_List($g_connection);
        $obj4->set_parameter("id", $obj2->get_parameter("id"));
        $obj4->load();
        $this->assertEquals("PHPUNIT-TITLE",$obj4->get_parameter("title"),"not Updated");
        
         $g_connection->exec_sql("delete from todo_list");
        
    }

}
