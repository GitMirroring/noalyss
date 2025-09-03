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
     /**
      * @testdox test the output of view_followup_card
      * @covers       Follow_Up::view_list,Follow_Up::create_query
      * @backupGlobals enabled
      */
    function testAjax_View_list()
    {
        global $g_user;
        $g_user=new Noalyss_User($this->connection);

        $CARD_ID=22;
        $get=array( "op"=>"view_followup_card",'f_id'=>22,'gDossier'=>DOSSIER,'div'=>'unit_test');
        $_REQUEST=$_POST=$_GET=$get;
        ob_start();
        require  NOALYSS_HOME.'/ajax_misc.php';
        $content=ob_get_contents();
        ob_end_clean();
        $this->assertStringContainsString("BONDEC3-1", $content);
        $this->assertTrue(mb_strlen($content)>=1619 && mb_strlen($content)<1642,"error result not valid $content size = ".mb_strlen($content));


    }
    /**
     * @testdox test ajax_search_action and function short_list
     * @covers       Follow_Up::short_list
     * @backupGlobals enabled
     */
    function testSearch_short_list()
    {
        global $g_user;
        $g_user=new Noalyss_User($this->connection);
        $query=Follow_Up::create_query($this->connection,array(
            "ag_dest_query" => "-2",
            "qcode" => "CLIENT1",
            "ctlc"=>"action"
        ));
        $_GET['ctlc']="test";
        $_GET['op']="search_action";
        $_REQUEST['op']="search_action";
        $sql=  "1=1  ".$query;
        ob_start();
        require   NOALYSS_HOME.'/ajax_misc.php';
        echo Follow_Up::short_list($this->connection, $sql);
        $content=ob_get_contents();
        ob_end_clean();
        $this->assertStringContainsString("BONDEC3-1", $content);



    }
    /**
     * @testdox save a short event
     * @covers       Follow_Up::save_short
     * @backupGlobals enabled
     */
    function testSaveShort() {
        global $g_user;
        $title='phpunit'.date('y.m.d H:i');
        $array=array(
            "date_event"=>'22.04.2022'
            ,"dest"=>''
            ,'event_group'=>1
            ,'event_priority'=>2
            ,'title_event'=>$title
            ,'summary'=>'<h1>Test</h1>'
            ,"type_event"=>2
            ,'hour_event'=>'07:30'
            ,'op'=>'action_save'
            ,'gDossier'=>DOSSIER
        );
        $_GET=$array;
        $_REQUEST=$array;
        ob_start();
        require   NOALYSS_HOME.'/ajax_misc.php';
        $content=ob_get_clean();

        $this->assertStringContainsString('<status>OK</status>',$content);


        global $cn;
        $id = $cn->get_value("select ag_id from action_gestion where ag_title=$1",[$title]);

        $this->assertTrue(!empty($id),'event not save in action_gestion');

        $comment_nb=$cn->get_value("select count(*)  from action_gestion_comment where ag_id=$1",[$id]);
        $this->assertTrue($comment_nb != 0 ,' event has no description');

        $comment_id=$cn->get_value("select agc_id from action_gestion_comment where ag_id=$1",[$id]);

        $a_row=new Action_Gestion_Comment_SQL($cn,$comment_id);
        $this->assertTrue( ! empty($a_row->agc_comment) , 'comment not saved');
        $this->assertTrue( ! empty($a_row->agc_comment_raw) , 'comment raw not saved');

        $cn->exec_sql("delete from action_gestion where ag_title like 'phpunit%'");
    }}