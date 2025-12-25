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
class AccountRepositoryTest extends TestCase {

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
        $cn = new \Database(0);
       // $cn->exec_sql('drop database phpunit_account_repository');
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
//    function testFunction() {
//        
//    }

    private function make_conx() {
        $db = new \DatabaseCore(
                p_user: noalyss_user,
                p_password: noalyss_password,
                p_dbname: 'phpunit_account_repository',
                p_host: '127.0.0.1',
                p_port: noalyss_psql_port
        );
        return $db;
    }

    /**
     * @testdox create a repository
     * @backupGlobals disabled
     */
    function testCreate() {
        try {
            $repo = new \Database(0);
            $repo->exec_sql("drop database if exists phpunit_account_repository  ");
            $repo->exec_sql("create database phpunit_account_repository encoding='utf8'");

            $db = $this->make_conx();

            /**
             * create repository
             */
            $db->start();
            $db->execute_script(NOALYSS_INCLUDE . "/sql/account_repository/schema.sql");
            $db->execute_script(NOALYSS_INCLUDE . "/sql/account_repository/data.sql");
            $db->execute_script(NOALYSS_INCLUDE . "/sql/account_repository/constraint.sql");

            $db->commit();
            $db->close();
            $this->assertTrue(true, __CLASS__ . " cannot create DB");
            $db = $this->make_conx();
            $db->start();
            $nb_table = $db->get_value("select count(*) from information_schema.tables where table_schema='public'");
            $db->commit();
            $this->assertEquals(11, $nb_table, " number of table incorrect $nb_table");
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            echo $ex->getTraceAsString();
            $this->assertTrue(false, __CLASS__ . " cannot create DB");
        }
    }

    /**
     * @testdox patching the repository
     */
    function testPatching() {
        try {
            $db = $this->make_conx();
            $db->start();
            $MaxVersion = DBVERSIONREPO - 1;
            for ($i = 4; $i <= $MaxVersion; $i++) {
                if ($db->get_value(' select val from version') <= $i) {
                    $db->execute_script(NOALYSS_INCLUDE . '/sql/patch/ac-upgrade' . $i . '.sql');
                }
            }
            $this->assertTrue(true, __CLASS__ . " cannot patch DB");
            $nb_table = $db->get_value("select count(*) from information_schema.tables where table_schema='public'");
            
            // close first
            $db->commit();
            $db->close();
            // 
            $this->assertEquals(12, $nb_table, " number of table incorrect $nb_table");
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            echo $ex->getTraceAsString();
            $this->assertTrue(false, __CLASS__ . " cannot create DB");
        }
    }
}
