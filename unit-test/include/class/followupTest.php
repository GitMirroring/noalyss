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
 * 
 * Author : Dany De Bontridder danydb@noalyss.eu $(DATE)
 */

/**
 * @file
 * @brief noalyss
 */

use PHPUnit\Framework\TestCase;

require DIRTEST . '/global.php';

/**
 * @testdox Class followupTest : used for ...
 * @backupGlobals enabled
 * @coversDefaultClass
 */
class FollowupTest extends TestCase
{

    /**
     * @var Fiche
     */
    protected $object;
    protected $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test method is executed.
     */
    protected function setUp(): void
    {

        $this->connection=\Dossier::connect();
        $this->object=new Follow_Up($this->connection);
    }

     /**
     * @testdox create query search by AG_ID
     * @covers       Follow_Up::create_query
     * @backupGlobals enabled
     */
    function testSearch_Ag_ID()
    {
        global $g_user;
        $g_user=new Noalyss_User($this->connection);
        $query=Follow_Up::create_query($this->connection,array("ag_id"=>2));
        $sql=Follow_Up::SQL_list_action()." where 1=1  $query";
        $array=$this->connection->get_array($sql);
        $this->assertEquals(2,$array[0]['ag_id']);
        $this->assertEquals('COURRI6-1',$array[0]['ag_ref']);

    }
    /**
     * @testdox create query search by Action_Query
     * @covers       Follow_Up::create_query
     * @backupGlobals enabled
     */
    function testSearch_action_query()
    {
        global $g_user;
        $g_user=new Noalyss_User($this->connection);
        $query=Follow_Up::create_query($this->connection,array("action_query"=>'test'));
        $sql=Follow_Up::SQL_list_action()." where 1=1  $query";
        $array=$this->connection->get_array($sql);
        $this->assertEquals(2, count($array));
        $this->assertEquals(1,$array[0]['ag_id']);
        $this->assertEquals('BONDEC3-1',$array[0]['ag_ref']);

    }
    /**
     * @testdox create query search by QCode
     * @covers       Follow_Up::create_query
     * @backupGlobals enabled
     */
    function testSearch_qcode()
    {
        global $g_user;
        $g_user=new Noalyss_User($this->connection);
        $query=Follow_Up::create_query($this->connection,array("qcode"=>'CLIENT1'));
        $sql=Follow_Up::SQL_list_action()." where 1=1  $query";
        $array=$this->connection->get_array($sql);
        $this->assertEquals(1,$array[0]['ag_id']);
        $this->assertEquals('BONDEC3-1',$array[0]['ag_ref']);
    }

    /**
     * @testdox export and search with ag_id
     * @covers       Follow_Up::export_csv,Follow_Up::create_query
     * @backupGlobals enabled
     */
    function testExport_CSV_Ag_ID()
    {
        global $g_user;
        $g_user=new Noalyss_User($this->connection);
        ob_start();
        $this->object->export_csv(["ag_id"=>2]);
        $content=ob_get_contents();
        ob_end_clean();
        $this->assertStringContainsString("COURRI6-1", $content);
    }
}