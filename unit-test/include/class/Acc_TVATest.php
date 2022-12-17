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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 17/12/22
/*! 
 * \file
 * \brief test Acc_TVA
 */

use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 * @coversDefaultClass \Acc_Payment
 */
require DIRTEST.'/global.php';


class Acc_TVATest extends TestCase
{
    protected $object;

    protected function setUp():void
    {
        include 'global.php';
    }

    /**
     * @testdox check set_parameter function
     * @return void
     */
     function testSet_parameter()
    {
        $cn=\Dossier::connect();
        $count=$cn->get_value("select count(*) from tva_rate");
        $this->assertEquals(9,$count);
        $tva=new Acc_Tva($cn,2);
        $this->assertEquals(0.1200,$tva->get_parameter("rate"),"error tva rate get_parameter ");
        $this->assertEquals(0.1200,$tva->tva_rate,"error tva rate direct access ");
        $tva->set_parameter("id",1);
        $tva->load();
        $this->assertEquals(0.2100 , $tva->tva_rate,"Cannot get tva rate after set_parameter");
    }
    function testConstructor()
    {
        $cn=\Dossier::connect();
        $count=$cn->get_value("select count(*) from tva_rate");
        $this->assertEquals(9,$count);
        $tva=new Acc_Tva($cn,2);
        $this->assertEquals(0.1200,$tva->tva_rate,"error tva rate direct access ");
        $tva->set_parameter("id",1);
        $tva->load();
        $this->assertEquals(0.2100 , $tva->tva_rate,"Cannot get tva rate after set_parameter");
        $tva->set_parameter("id",1);
        $tva->load();
        $this->assertEquals(0.2100 , $tva->tva_rate,"Cannot get tva rate after set_parameter");
    }

}