<?php

/*
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
 * Author : Dany De Bontridder danydb@noalyss.eu
 * Copyright (C) 2025 Dany De Bontridder <dany@alchimerys.be>
 * 
 */

/**
 * @file
 * @brief noalyss
 */
use PHPUnit\Framework\TestCase;

require DIRTEST . '/global.php';

/**
 * @testdox Class xxx : used for ...
 * @backupGlobals enabled
 * @coversDefaultClass
 */
class Acc_DocumentTest extends TestCase {

    /**
     * @var Fiche
     */
    protected $object;
    protected $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test method is executed.
     */
    protected function setUp(): void {
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test method is executed.
     */
    protected function tearDown(): void {
        /**
         * example
         * if ( ! is_object($this->object->fiche_def)) return;
         * include_once DIRTEST.'/global.php';
         * $g_connection=Dossier::connect();
         * $sql=new ArrayObject();
         * $sql->append("delete from fiche_detail where f_id in (select f_id from fiche where fd_id =\$1 )");
         * $sql->append("delete from fiche where f_id not in (select f_id from fiche_detail where \$1=\$1)");
         * $sql->append("delete from jnt_fic_attr where fd_id  = \$1 ");
         * $sql->append("delete from fiche_def where fd_id = \$1");
         * foreach ($sql as $s) {
         * $g_connection->exec_sql($s,[$this->object->fiche_def->id]);
         * }
         */
    }

    /**
     * the setUpBeforeClass() template methods is called before the first test of the test case
     *  class is run
     */
    public static function setUpBeforeClass(): void {
        //        include 'global.php';
    }

    /**
     * @brief Data simulation

     * @return array
     */
    function dataExample() {
        return array(
            ['0A', 4]
            , ['0B', 6]
            , [6, 6]
            , ['NONE', -1]
            , [14, -1]
            , ["  ", -1]
            , [null, -1]
        );
    }

    /**
     *  tearDownAfterClass() template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass(): void {
        //        include 'global.php';
    }

//
//    public function dataExample()
//    {
//        return array([1], [2], [3]);
//    }

    /**
     * @testdox description of the test
     * @covers Acc_Balance::summary_add
     * @depend Acc_Balance::summary_init
     * @backupGlobals enabled
     * @dataProvider dataExample
     * @global $g_connection
     */
    function testFunction() {
        
    }
}
