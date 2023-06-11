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
 * @testdox Class Package_RepositoryTest : used for ...
 * @backupGlobals enabled
 * @coversDefaultClass
 */
class Package_RepositoryTest extends TestCase
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
    /**
     * @testdox Test constructor
     * @covers       Package_Repository::__construct
     * @backupGlobals enabled
     */
    function testConstruct()
    {
        $web_xml = $_ENV['TMP'] . "/web.xml";
        if ( file_exists($web_xml) )        unlink($web_xml);

        $rapav_repository = new Package_Repository();
        $this->assertFileExists($web_xml, $_ENV['TMP'] . "/web.xml not loaded");
        $this->assertGreaterThan(18000, filesize($web_xml), " web.xml truncated");


    }

    /**
     * @testdox Test the cache
     * @covers       Package_Repository::__construct
     * @backupGlobals enabled
     * */
    function testCache()
    {
        $web_xml = $_ENV['TMP'] . "/web.xml";
        $rapav_repository = new Package_Repository();
        $this->assertFileExists($web_xml, $_ENV['TMP'] . "/web.xml not loaded");
        clearstatcache(true, $web_xml);
        $time = filemtime($web_xml);
        sleep(2);
        $rapav_repository2 = new Package_Repository();
        clearstatcache(true, $web_xml);
        $time2 = filemtime($web_xml);
        $this->assertEquals($time, $time2, " web.xml not cached");

        Package_Repository::setTimeCacheSecond(5);

        sleep(10);

        $this->assertEquals(Package_Repository::getTimeCacheSecond(), 5, "cache not set correctly");
        $rapav_repository3 = new Package_Repository();

        clearstatcache(true, $web_xml);
        $time3 = filemtime($web_xml);
        $this->assertNotEquals($time, $time3, " web.xml not refreshed");
    }

    /**
     * @testdox Find a Template
     * @covers Package_Repository::find_template
     * @backupGlobals enabled
     */
    function testfindTemplate()
    {
        $repository=new \Package_Repository();
        $report=$repository->find_template("mod1");
        fwrite(STDERR,"[  ".get_class($report)."]");
        $this->assertTrue(get_class($report)=="SimpleXMLElement","invalid return");

        $report=$repository->find_template("FRBIL01XXXXX");
        $this->assertTrue(empty($report),"unnkown db template must return NULL");

    }/**
     * @testdox Find a Plugin
     * @covers Package_Repository::find_plugin(
     * @backupGlobals enabled
     */
    function testfindPlugin()
    {
        $repository=new \Package_Repository();
        $report=$repository->find_plugin("NDER");
        fwrite(STDERR,"[  ".get_class($report)."]");
        $this->assertTrue(get_class($report)=="SimpleXMLElement","invalid return");

        $report=$repository->find_plugin("FRBIL01XXXXX");
        $this->assertTrue(empty($report),"unnkown Plugin must return NULL");

    }

}