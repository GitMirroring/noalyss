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
 */
require DIRTEST.'/global.php';

class PeriodeTest extends TestCase
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
        //        include 'global.php';
    }

    /**
     *  tearDownAfterClass():void template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass():void
    {
        //        include 'global.php';
    }

    public function dataInsert()
    {
        return array(
            [ '01.01.2023' , '31.01.2023','2020','2020.2023','NOK'],
            [ '01.01.2023' , '31.01.2023','2023','2020.2023','OK'],
            [ '01.02.2023' , '28.02.2023','2020','2020.2023','NOK'],
            [ '01.02.2023' , '28.02.2023','2023','2020.2023','OK'],
            [ '01.02.2023' , '28.01.2023','2023','2020.2023','NOK']
            );
    }

    /**
     * @brief test the trigger comptaproc.check_periode()
     * @testdox insert - comptaproc.check_periode
     * @dataProvider dataInsert
     * @param type $p_param
     * @covers
     */
    public function testInsert($p_start,$p_end,$p_exercice,$p_exercice_label,$p_status)
    {
        global $g_connection;

        $g_connection->exec_sql("delete from parm_periode where p_id > 144");
        $obj=new Parm_periode_SQL($g_connection);
        $obj->set('p_start',$p_start);
        $obj->set('p_end',$p_end);
        $obj->set('p_closed',false);
        $obj->set('p_central',false);
        $obj->set('p_exercice',$p_exercice);
        $obj->set('p_exercice_label',$p_exercice_label);
        try {
            
            $obj->insert();
            if ($p_status== 'OK') {
                $this->assertTrue(True,"Expected");
            } else {
                $this->assertTrue(FALSE,"Error");
            }
        } catch(Exception $e) {
            if ($p_status == 'NOK') {
                $this->assertTrue(True,"Expected");
            } else {
                $this->assertTrue(FALSE,"Error");
                printf("message : %s",$e->getMessage());
            }
            
        }
        $g_connection->exec_sql("delete from parm_periode where p_id > 144");
    }
    public function dataUpdate()
    {
        return array(
            [ '01.01.2023' , '31.01.2023','2020','2020.2023','NOK'],
            [ '01.01.2023' , '31.01.2023','2023','2020.2023','OK'],
            [ '01.02.2023' , '28.02.2023','2020','2020.2023','NOK'],
            [ '01.02.2023' , '28.02.2023','2023','2020.2023','OK'],
            [ '01.02.2023' , '28.01.2023','2023','2020.2023','NOK']
            );
    }

    /**
     * @brief test the trigger comptaproc.check_periode()
     * @testdox update - comptaproc.check_periode
     * @dataProvider dataInsert
     * 
     * 
     */
    public function testUpdate($p_start,$p_end,$p_exercice,$p_exercice_label,$p_status)
    {
        
        global $g_connection;
        /* insert the record to update */
        $g_connection->exec_sql("delete from parm_periode where p_exercice =$1",["2024-PHPUNIT"]);
        $obj=new Parm_periode_SQL($g_connection);
        $obj->set('p_start','01.01.2024');
        $obj->set('p_end','31.01.2024');
        $obj->set('p_closed',false);
        $obj->set('p_central',false);
        $obj->set('p_exercice','2024');
        $obj->set('p_exercice_label',"2024-PHPUNIT");
        try {
            
            $obj->insert();
            $obj->set('p_start',$p_start);
            $obj->set('p_end',$p_end);
            $obj->set('p_closed',false);
            $obj->set('p_central',false);
            $obj->set('p_exercice',$p_exercice);
            $obj->set('p_exercice_label',$p_exercice_label);
            $obj->update();
            if ($p_status== 'OK') {
                $this->assertTrue(True,"Expected");
            } else {
                $this->assertTrue(FALSE,"Error");
            }
           
        } catch(Exception $e) {
            if ($p_status == 'NOK') {
                $this->assertTrue(True,"Expected");
            } else {
                $this->assertTrue(FALSE,"Error");
                printf("message : %s",$e->getMessage());
            }
            
        }
        $g_connection->exec_sql("delete from parm_periode where p_exercice =$1",["2024-PHPUNIT"]);
    }
}
