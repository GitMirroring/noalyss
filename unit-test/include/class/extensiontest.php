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
 * @testdox Class ExtensionTest : used for ...
 * @backupGlobals enabled
 * @coversDefaultClass
 */
class ExtensionTest extends TestCase
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


    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test method is executed.
     */
    protected function tearDown(): void
    {
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
    public static function setUpBeforeClass(): void
    {
        //        include 'global.php';
    }

    /**
     *  tearDownAfterClass() template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass(): void
    {
        //        include 'global.php';
    }
//
//    public function dataExample()
//    {
//        return array([1], [2], [3]);
//    }

    /**
     * @testdox check the load of plugin and find the  plugin from an access_code
     * @covers \Extension::find_extension_code \Extension:read_definition , \Extension::verify
     * @backupGlobals  on
     */
    function testFind_extension_file()
    {
        global $cn;
        require DIRTEST . '/global.php';
        $cn=$g_connection;
        $a_extension=\Extension::read_definition(__DIR__."/data/plugin.xml");
        $access_code="EXT/TOOLS/PLUGIN_TEST";
        $extension=\Extension::find_extension_code($a_extension, $access_code);

        $this->assertTrue($extension->me_file=="skel/index-plugin_test.php");
        $this->assertTrue($extension->version==9019, " ne peut retrouver la version");
        $this->assertTrue($extension->noalyss_version==8111," ne peut pas retrouver la version de noalyss");
    }

    /**
     * @testdox check the load of plugin and find the  plugin from an access_code
     * @covers \Extension::find_extension_code \Extension:read_definition\Extension::verify
     * @backupGlobals  on
     */
    function testFind_extension_version()
    {
        global $cn;
        require DIRTEST . '/global.php';
        $cn=$g_connection;
        $a_extension=\Extension::read_definition(__DIR__."/data/plugin.xml");
        $access_code="EXT/TOOLS/SKEL";
        $extension=\Extension::find_extension_code($a_extension, $access_code);

        $this->assertTrue($extension->version==2000, " ne peut retrouver la version");
        $this->assertTrue($extension->noalyss_version==9000," ne peut pas retrouver la version de noalyss");

    }
}