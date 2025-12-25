<?php

/*
 *   This file is part of NOALYSS.
 *
 *   NOALYSS is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   NOALYSS is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23

/**
 * @file
 * @brief test Single_Record
 */
use PHPUnit\Framework\TestCase;

class Single_RecordTest extends TestCase {

    /**
     * @covers Single_Record::hidden
     * @covers Single_Record::__construct()
     * @global $g_connection
     */
    function testHiddenDatabase() {
        global $g_connection;
        $test_dup = new \Single_Record("test_dup", $g_connection);
        $r = $test_dup->hidden();
        $this->assertTrue($test_dup->id > 0, " id not computed " . $test_dup);
    }

    /**
     * @covers Single_Record::hidden
     * @covers Single_Record::__construct()
     * @global $g_connection
     */
    function testHiddenDefaultDatabase() {
        global $g_connection;
        global $cn;
        $cn = $g_connection;
        $test_dup = new \Single_Record("test_dup");
        $r = $test_dup->hidden();
        $this->assertTrue($test_dup->id > 0, " id not computed " . $test_dup);
    }

    /**
     * @covers Single_Record::hidden
     * @covers Single_Record::__construct()
     * @global $g_connection
     */
    function testSequence() {
        global $g_connection;
        global $cn;
        $cn = $g_connection;
        $test_dup = new \Single_Record("test_dup");
        $r = $test_dup->hidden();
        $a = $test_dup->id;
        $this->assertTrue($test_dup->id > 0, " id not computed " . $test_dup);
        $r = $test_dup->hidden();
        $this->assertTrue($test_dup->id > $a, " id not incremented" . $test_dup);
    }

    /**
     * @covers Single_Record::save()
     * @global $g_connection
     */
    function testSave() {
        global $g_connection;
        global $cn;
        $cn = $g_connection;
        $test_dup = new \Single_Record("test_dup");
        $r = $test_dup->hidden();
        $this->assertTrue($test_dup->id > 0, " id not computed " . $test_dup);
        $test_dup->save(["test_dup"=>$test_dup->id]);
    }

    /**
     * @covers Single_Record::check()
     * @global $g_connection
     */
    function testCheck() {
        global $g_connection;
        global $cn;
        $this->expectException (\Exception::class);
        $cn = $g_connection;
        $test_dup = new \Single_Record("test_dup");
        $r = $test_dup->hidden();
        $test_dup->save(["test_dup"=>$test_dup->id]);
        $this->assertTrue($test_dup->id > 0, " id not computed " . $test_dup);
        $test_dup->check(['test_dup'=>$test_dup->id]);
        $test_dup->check(['test_dup'=>$test_dup->id]);
    }
    /**
     * @covers Single_Record::get_count()
     * @global $g_connection
     */
    function testCount() {
        global $g_connection;
        global $cn;
        $cn = $g_connection;
        $test_dup = new \Single_Record("test_dup");
        $r = $test_dup->hidden();
        $this->assertTrue($test_dup->id > 0, " id not computed " . $test_dup);
         $test_dup->save(["test_dup"=>$test_dup->id]);
        $cnt = $test_dup->get_count(['test_dup'=>$test_dup->id]);
        $this->assertTrue($cnt > 0,"not count") ;
    }
}
