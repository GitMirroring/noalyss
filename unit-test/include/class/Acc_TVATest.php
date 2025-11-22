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
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @brief display error from tva_rate_mtable
     * @param Tva_Rate_MTable $tva_rate_mtable
     * @return void
     */
    function display_error(Tva_Rate_MTable $tva_rate_mtable) {
        $col=$tva_rate_mtable->get_order();
        foreach($col as $item) {
            $error =  $tva_rate_mtable->get_error($item);
            if ( !empty ($error)) print "$error \n";
        }
    }

    static function dataCheck()  {
         return array(
             ['abc',true]
             ,['13A',true]
             ,['1',false]
             ,['1-A',false]
             ,['+a',false]
             ,['abcdefg',false]
         );
    }

    /**
     * @testdox check TVA_CODE value must constains digit and letter
     * @dataProvider dataCheck
     * @return void
     */
     #[DataProvider('dataCheck')]
    function testCheck($tva_code,$result)
    {
        $cn=\Dossier::connect();
        $vtva_rate=new V_Tva_rate_SQL($cn,-1);
        $vtva_rate->tva_code=$tva_code;
        $vtva_rate->tva_label="Test";
        $vtva_rate->tva_sale="451";
        $vtva_rate->tva_both_side="0";
        $vtva_rate->tva_rate=0.21;
        $vtva_rate->tva_peppol_code='S';
        $tva_rate_mtable=new Tva_Rate_MTable($vtva_rate);
        $tva_rate_mtable->setPreviousId(0);

        $check = $tva_rate_mtable->check();
        
        $this->assertTrue($result==$check," erreur pour $tva_code ");
        if ( $result != $check)
        {
            print "Error for $peppopl_code\n";
            print_r($tva_rate_mtable->aerror);
            $this->display_error($tva_rate_mtable);
        }
    }
    static function dataCheckVatex()  {
         return array(
             ['S',true,null]
             ,['Z',true,null]
             ,['A',false,""]
             ,['A',true,"XX"]
         );
    }
    /**
     * @brief VATEX Mandatory of tva_peppol_code not in S or Z
     * @testdox VATEX Mandatory if tva_peppol_code not S or Z
     * @param type $tva_code
     * @param type $result
     */
     #[DataProvider('dataCheckVatex')]
    function testCheckVatex($peppopl_code,$result,$vatex_code)
    {
        $cn=\Dossier::connect();
        $vtva_rate=new V_Tva_rate_SQL($cn,1);
        
        $vtva_rate->tva_label="Test";
        $vtva_rate->tva_sale="451";
        $vtva_rate->tva_both_side="0";
        $vtva_rate->tva_rate=0.21;
        $vtva_rate->tva_peppol_code=$peppopl_code;
        $vtva_rate->vx_code=$vatex_code;
        $tva_rate_mtable=new Tva_Rate_MTable($vtva_rate);
        $tva_rate_mtable->setPreviousId(1);

        $check = $tva_rate_mtable->check();
        
        $this->assertTrue($result==$check," erreur pour $peppopl_code ");
        if ( $result != $check)
        {
            print "Error for $peppopl_code\n";
            print_r($tva_rate_mtable->aerror);
            $this->display_error($tva_rate_mtable);
        }
    }
    
    static function dataBuild()  {
        return array(
            ['0A',4]
            ,['0B',6]
            ,[6,6]
            ,['NONE',-1]
            ,[14,-1]
            ,["  ",-1]
            ,[null,-1]
        );
    }
    /**
     * @testdox check Acc_TVA::Build
     * @dataProvider dataBuild
     * @return void
     */
     #[DataProvider('dataBuild')]
    function testBuild($tva_code,$result)
    {
        $cn=\Dossier::connect();
        $tva=Acc_Tva::build($cn, $tva_code);
        $tva->load();
        $this->assertTrue($result==$tva->tva_id," erreur pour tva_code [$tva_code] tva_id {$tva->tva_id}");

    }

}