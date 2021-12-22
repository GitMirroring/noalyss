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
 * @coversDefaultClass \Acc_Payment
 */
require 'global.php';

class AccPaymentTest extends TestCase
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

    public function dataLoad()
    {
        $a_json=array();
        $a_json[0]=['{
		"mp_id" : 4,
		"mp_lib" : "Caisse",
		"mp_jrn_def_id" : 1,
		"mp_fd_id" : null,
		"mp_qcode" : null,
		"jrn_def_id" : 3
	}'];

        $a_json[1]=['{
		"mp_id" : 3,
		"mp_lib" : "Par gérant ou administrateur",
		"mp_jrn_def_id" : 2,
		"mp_fd_id" : null,
		"mp_qcode" : null,
		"jrn_def_id" : 3
	}'];
        $a_json[2]=['{
		"mp_id" : 1,
		"mp_lib" : "Paiement électronique",
		"mp_jrn_def_id" : 1,
		"mp_fd_id" : 3,
		"mp_qcode" : "COMPTE",
		"jrn_def_id" : 2
	}'];
        $a_json[3]=['	{
		"mp_id" : 2,
		"mp_lib" : "Caisse",
		"mp_jrn_def_id" : 4,
		"mp_fd_id" : 2,
		"mp_qcode" : null,
		"jrn_def_id" : 2
	}'];
        $a_json[4]=['{
		"mp_id" : 11,
		"mp_lib" : "Caisse",
		"mp_jrn_def_id" : 1,
		"mp_fd_id" : 3,
		"mp_qcode" : "COMPTE",
		"jrn_def_id" : 35
	}'];
        return $a_json;
    }

    /**
     * @brief 
     * @testdox load record
     * @dataProvider dataLoad
     * @param json $json
     * @covers ::load ::get_parameter
     */
    public function testLoad($json)
    {
        global $g_connection;
        $acc_pay=json_decode($json, true);
        $object=new Acc_Payment($g_connection, $acc_pay['mp_id']);
        $object->load();
        $this->assertEquals($acc_pay['mp_id'], $object->get_parameter('id'), "Error for column mp_id");
        $this->assertEquals($acc_pay['mp_lib'], $object->get_parameter('lib'), "Error for column mp_lib");
        $this->assertEquals($acc_pay['mp_qcode'], $object->get_parameter('qcode'), "Error for column mp_qcode");
        $this->assertEquals($acc_pay['mp_jrn_def_id'], $object->get_parameter('ledger_target'),
                "Error for column mp_jrn_def_id");
        $this->assertEquals($acc_pay['jrn_def_id'], $object->get_parameter('ledger_source'),
                "Error for column jrn_def_id");
        $this->assertEquals($acc_pay['mp_fd_id'], $object->get_parameter('fiche_def'), "Error for column mp_fd_id");
    }

    /**
     * @brief 
     * @testdox Test from array
     * @dataProvider dataLoad
     * @param json $json
     * @covers ::from_array ::get_parameter
     */
    public function testFromArray($json)
    {
        global $g_connection;
        $acc_pay=json_decode($json, true);
        $object=new Acc_Payment($g_connection);
        $object->from_array($acc_pay);
        $this->assertEquals($acc_pay['mp_id'], $object->get_parameter('id'), "Error for column mp_id");
        $this->assertEquals($acc_pay['mp_lib'], $object->get_parameter('lib'), "Error for column mp_lib");
        $this->assertEquals($acc_pay['mp_qcode'], $object->get_parameter('qcode'), "Error for column mp_qcode");
        $this->assertEquals($acc_pay['mp_jrn_def_id'], $object->get_parameter('ledger_target'),
                "Error for column mp_jrn_def_id");
        $this->assertEquals($acc_pay['jrn_def_id'], $object->get_parameter('ledger_source'),
                "Error for column jrn_def_id");
        $this->assertEquals($acc_pay['mp_fd_id'], $object->get_parameter('fiche_def'), "Error for column mp_fd_id");
    }
    /**
     * @brief 
     * @testdox function get_valide
     * @covers ::set_parameter ::get_valide
     */
    public function testGet_Valide()
    {
        global $g_connection;
        $object=new Acc_Payment($g_connection);
        $object->set_parameter("ledger_source", 2);
        $valid_payment=$object->get_valide();
        $this->assertEquals(2, count($valid_payment), 'Error different payment method');
        $this->assertEquals($valid_payment[0]->get_parameter('qcode'), 'COMPTE',
                'Propose method no correct ['.$valid_payment[0]->get_parameter('qcode').']');
        $this->assertEquals($valid_payment[1]->get_parameter('lib'), 'Caisse',
                'label no correct ['.$valid_payment[0]->get_parameter('lib').']');
    }
    /**
     * @brief 
     * @testdox function get_all
     * @covers ::set_parameter ::get_all
     */
    public function testGet_All()
    {
        global $g_connection;
        $object=new Acc_Payment($g_connection);
        $valid_payment=$object->get_all();
        $this->assertEquals(5, count($valid_payment), 'Error different payment method');
        $this->assertEquals($valid_payment[0]->get_parameter('qcode'), null,
                'Propose method no correct ['.$valid_payment[0]->get_parameter('qcode').']');
        $this->assertEquals($valid_payment[1]->get_parameter('lib'), 'Caisse',
                'label no correct ['.$valid_payment[0]->get_parameter('lib').']');
    }

}
