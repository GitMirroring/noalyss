<?php

use PHPUnit\Framework\TestCase;

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2002-2019) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief concerne acc_ledger_infoTest.class
 */
class Acc_Ledger_InfoTest extends TestCase
{

    /**
     * @var 
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        include 'global.php';
        global $g_connection;
        $this->object=new Acc_Ledger_Info($g_connection);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }
    function test_insertBonCommande()
    {
        global $g_connection;
        $this->object->jr_id=3;
        $this->object->id_type='BON_COMMANDE';
        $this->object->ji_value='BON';
        
        $g_connection->exec_sql("delete from jrn_info where jr_id=$1",[$this->object->jr_id]);
        $cnt=$g_connection->get_value("select count(*) from jrn_info where jr_id=$1",[$this->object->jr_id]);
        $this->assertEquals($cnt,0,"Error : bon_commande already exist");
        $this->object->insert();
        $cnt=$g_connection->get_value("select count(*) from jrn_info where jr_id=$1",[$this->object->jr_id]);
        $this->assertEquals($cnt,1,'Error : not inserted');
        $g_connection->exec_sql("delete from jrn_info where jr_id=$1",[$this->object->jr_id]);

    }
    function test_insertOther()
    {
        global $g_connection;
        $this->object->set_jrn_id(7);
        $this->object->set_type('OTHER');
        $this->object->set_value('Autre test');
        
        $cnt=$g_connection->get_array("select * from jrn_info where jr_id=$1",[$this->object->jr_id]);
        $this->assertSame(count($cnt),0);
        $this->object->insert();
        $cnt=$g_connection->get_array("select * from jrn_info where jr_id=$1",[$this->object->jr_id]);
        $this->assertSame(count($cnt),1);
        $g_connection->exec_sql("delete from jrn_info where jr_id=$1",[$this->object->jr_id]);
    }
    /**
     * @testdox test loading
     */
    function test_load()
    {
	global $g_connection;
        $this->object->jr_id=3;
        $this->object->id_type='BON_COMMANDE';
        $this->object->ji_value='BONi 11111111';
        
        $cnt=$g_connection->get_array("select * from jrn_info where jr_id=$1",[$this->object->jr_id]);
        $this->assertSame(count($cnt),0);
        $this->object->insert();
        $cnt=$g_connection->get_array("select * from jrn_info where jr_id=$1",[$this->object->jr_id]);
	$this->assertSame(count($cnt),1);
	$reload_id=$g_connection->get_value("select ji_id from jrn_info where jr_id=$1",[$this->object->jr_id]);
	$reload=new Acc_Ledger_Info($g_connection,$reload_id);
	$reload->load();
	$this->assertSame($reload->ji_value,$this->object->ji_value,"Error cannot load");
	
        $g_connection->exec_sql("delete from jrn_info where jr_id=$1",[$this->object->jr_id]);
	    
    }
    
}
