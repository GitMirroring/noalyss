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
 * @testdox Test Operation_Exercice, Operation_Opening and Operation_Closing
 * @coversDefaultClass
 */
class Operation_ExerciceTest extends TestCase
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
        global $aOperation;
        $aOperation=array();


    }

    /**
     *  tearDownAfterClass() template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass(): void
    {
        global $g_connection;
        global $aOperation;

        // clean operation
        if (!empty($aOperation)) {
            foreach ($aOperation as $item) {
                $g_connection->exec_sql("delete from operation_exercice where oe_id=$1",[$item]);

            }
        }
    }
//
//    public function dataExample()
//    {
//        return array([1], [2], [3]);
//    }

    /**
     * @testdox insert row for opening
     * @covers       Operation_Opening::insert
     */
    function testInsertOpening()
    {
        global $g_connection;
        global $aOperation;
        $operation=new \Operation_Opening(-1);
        $operation->set_exercice(2020);
        $operation->set_from_folder(DOSSIER);
        $operation->insert();
        $operation_id=$operation->get_operation_exercice_sql()->getp("oe_id");
        $this->assertFalse(empty($operation_id),'operation not saved');
        $aOperation[]=$operation_id;
        $sum_cred=$g_connection->get_value("select sum(oed_amount) 
                from operation_exercice_detail where oed_debit='f' and oe_id=$1",[$operation_id]);
        $this->assertEquals(1796.89,$sum_cred,'Total credit incorrect for operation $operation_id');

        $sum_deb=$g_connection->get_value("select sum(oed_amount) 
                from operation_exercice_detail where oed_debit='t' and oe_id=$1",[$operation_id]);
        $this->assertEquals(1117.08,$sum_deb,'Total debit incorrect for operation $operation_id');

        $sum=$g_connection->get_value("select sum(oed_amount) 
                from operation_exercice_detail where oe_id=$1",[$operation_id]);
        $this->assertEquals(2913.97,$sum,'Total incorrect for operation $operation_id');

        $g_connection->exec_sql("insert into operation_exercice_detail(oe_id,oed_poste,oed_amount,oed_debit)
values ($1,$2,$3,$4)",[$operation_id,'4111',$sum_cred-$sum_deb,'t']);

    }
    /**
     * @testdox insert row for closing
     * @covers       Operation_Closing::insert
     */

    function testInsertClosing()
    {
        global $g_connection;
        global $aOperation;

        $operation=new \Operation_Closing(-1);
        $operation->set_exercice(2020);
        $operation->insert();
        $operation_id=$operation->get_operation_exercice_sql()->getp("oe_id");
        $this->assertFalse(empty($operation_id),'operation not saved');
        $aOperation[]=$operation_id;
        $sum_cred=$g_connection->get_value("select sum(oed_amount) 
                from operation_exercice_detail where oed_debit='f' and oe_id=$1",[$operation_id]);
        $this->assertEquals(1492.89,$sum_cred,'Total credit incorrect for operation $operation_id');

        $sum_deb=$g_connection->get_value("select sum(oed_amount) 
                from operation_exercice_detail where oed_debit='t' and oe_id=$1",[$operation_id]);
        $this->assertEquals(813.08,$sum_deb,'Total debit incorrect for operation $operation_id');

        $sum=$g_connection->get_value("select sum(oed_amount) 
                from operation_exercice_detail where oe_id=$1",[$operation_id]);
        $this->assertEquals(2305.97,$sum,'Total incorrect for operation $operation_id');
        $g_connection->exec_sql("insert into operation_exercice_detail(oe_id,oed_poste,oed_amount,oed_debit)
values ($1,$2,$3,$4)",[$operation_id,'140',$sum_cred-$sum_deb,'t']);


    }

    /**
     * @testdox transform into an array for Acc_ledger
     * @depends testInsertClosing
     * @depends testInsertOpening
     * @return void
     */
    function testTransformOpening()
    {
        global $g_connection;
        global $aOperation;
        $ledger_id=4;

        // clean operation
        if (!empty($aOperation)) {
            foreach ($aOperation as $item) {
                $operation_sql=new \Operation_Exercice_SQL($g_connection,$item);
                if ( $operation_sql->getp('oe_type')=='opening') {
                    global $oe_data;
                    $operation=new \Operation_Opening($item);
                    $operation->transform($ledger_id);
                    $this->assertEquals(12,$oe_data['nb_item'],"Number of elements not correct");
                }
                if ( $operation_sql->getp('oe_type')=='closing') {
                    global $oe_data;
                    $operation=new \Operation_Closing($item);
                    $operation->transform($ledger_id);
                    $this->assertEquals(5,$oe_data['nb_item'],"Number of elements not correct");
                }
            }
        }
    }
}
