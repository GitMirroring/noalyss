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

/**
 * @testdox Class Acc_Reconciliation for operations links
 * @backupGlobals enabled
 * @coversDefaultClass Acc_Reconciliation
 */
class Acc_ReconciliationTest extends TestCase {

    /**
     * @var Fiche
     */
    protected $object;
    protected $connection;
    private $g_user;

    /**
     *  @backupGlobals enabled
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test method is executed.
     */
    protected function setUp(): void {
        require DIRTEST . '/global.php';
        $this->connection = $g_connection;
        $this->g_user = $g_user;
    }

    /**
     * @testdox Count rows not linked
     * @global $g_connection
     */
    function testNotReconcilied() {
        $acc_reconciliation = new \Acc_Reconciliation($this->connection);
        $acc_reconciliation->prepare_query_detail_quant();
        $array = $acc_reconciliation->get_data(3);
        $this->assertEquals(18, count($array), "count number of rows found");
    }
    /**
     * @brief build an object Acc_Reconciliation
     * @return \Acc_Reconciliation
     */
    function build_object() {
        $acc_reconciliation = new \Acc_Reconciliation($this->connection);
        $acc_reconciliation->start_day = '01.01.2019';
        $acc_reconciliation->end_day = '01.01.2019';
        return $acc_reconciliation;
    }

    /**
     * @testdox Count rows linked
     * @backupGlobals enabled
     */
    function testReconcilied() {
        $acc_reconciliation = $this->build_object();
        $array = $acc_reconciliation->get_data(0);
        $this->assertEquals(8, count($array), "count number of rows found");
    }

    /**
     * @testdox Count rows  linked  but with different amount
     * @backupGlobals enabled
     */
    function testReconciliedDifferentAmount() {
        $acc_reconciliation = new \Acc_Reconciliation($this->connection);
        $array = $acc_reconciliation->get_data(1);
        $this->assertEquals(0, count($array), "count number of rows found");
    }

    function testBuild_temp_total_operation() {
        $acc_reconciliation = new \Acc_Reconciliation($this->connection);
        $acc_reconciliation->build_temp_total_operation();
        $acc_reconciliation->build_temp_total_operation();
        $acc_reconciliation->build_temp_total_operation();
        $cnt = $this->connection->get_value("select count(*) from temp_total_operation");
        $this->assertEquals(16, $cnt, " incorrect number of rows");
    }
}
