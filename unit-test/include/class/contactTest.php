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
 * Author : Dany De Bontridder danydb@noalyss.eu
 * 
 */

/**
 * @file
 * @brief test the class Contact
 */
use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 * @coversDefaultClass \Contact
 */
require DIRTEST.'/global.php';

class ContactTest extends TestCase
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
        $this->object = new stdClass();
        $this->object->fiche_def=0;
        $this->object->card_to_clean=array();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test method is executed.
     */
    protected function tearDown():void
    {
        include 'global.php';
        global $g_connection;
        $sql=new ArrayObject();
        $sql->append("delete from fiche_detail where f_id in (select f_id from fiche where fd_id = $1 )");
        $sql->append("delete from fiche where f_id not in (select f_id from fiche_detail where $1=$1)");
        $sql->append("delete from jnt_fic_attr where fd_id  = $1 ");
        $sql->append("delete from fiche_def where fd_id = $1");
        foreach ($sql as $s) {
            $g_connection->exec_sql($s,[$this->object->fiche_def->id]);
        }

    }

    /**
     * the setUpBeforeClass() template methods is called before the first test of the test case
     *  class is run 
     */
    public static function setUpBeforeClass():void
    {
        //        include 'global.php';
    }

    /**
     *  tearDownAfterClass() template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass():void
    {
        //        include 'global.php';
    }
//
//    public function dataExample()
//    {
//        return array([1], [2], [3]);
//    }

    /**
     * a. create contact category
     */
    public function createContact()
    {
        include_once 'global.php';

        global $g_connection;
        // create a category of card, type Charges
        $fiche_def=new Fiche_Def($g_connection);
        $aParam=["nom_mod"=>"Test.Contact", 
            "fd_description"=>'PHPUNIT test', 'class_base'=>'', 'FICHE_REF'=>16,
            'create'=>'off'];
        $this->assertEquals($fiche_def->id, 0, 'Before created');

        $fiche_def->add($aParam);
        $this->assertLessThan($fiche_def->id, 0, 'After created');

        $this->assertEquals($g_connection->get_value("select count(*) 
                from fiche_def where fd_id=$1", [$fiche_def->id]),
                1, "Category not created");
        $this->object->fiche_def=$fiche_def;
        
    }
    /**
     *
     * @dataProvider dataContactCard
     * @param array $p_param
     */
    public function createContactCard()
    {
        global $g_connection;
        if ( $this->object->fiche_def == 0) {
            $this->createContact();
        }
        $aName=array();
        // Available fd_id for supplier or customer = 25 21 22 31 32
        $aName[]=['name'=>'Chantal','company'=>'CLIENT'];
        $aName[]=['name'=>'Pierre','company'=>'CLIENT'];
        $aName[]=['name'=>'William','company'=>'FOURNI'];
        $aName[]=['name'=>'Daniel','company'=>'FOURNI'];
        $aName[]=['name'=>'Geert','company'=>'FOURNI'];
        foreach ($aName as $param  ) {
            $fiche=new Fiche($g_connection);
            $fiche->fiche_def=$this->object->fiche_def->id;
            $fiche->load();
            $fiche->setAttribut(ATTR_DEF_NAME, $param['name']);
            $fiche->setAttribut(ATTR_DEF_COMPANY, $param['company']);
            $fiche->insert($fiche->fiche_def,$fiche->to_array());
            $this->object->card_to_clean[]=$fiche->id;
            printf("Card to clean %s ",$fiche->id);
        }
        $this->assertTrue(count($aName) == count($this->object->card_to_clean),"Not all contact created");
    }

    /**
     * @brief 
     * @testdox Contact Summary
     */
    public function testSummary()
    {
        global $g_connection;
        if ( $this->object->fiche_def == 0) {
            $this->createContactCard();
          }
        $contact=new Contact($g_connection);
        $_SERVER['REQUEST_URI']="?";
        $_SERVER['PHP_SELF']=__FILE__;
        $r=$contact->summary();
        $this->assertEquals(count($this->object->card_to_clean)*2 , substr_count($r,'fill_ipopcard')," 1. Missing card");
        $contact->company=' fourni ';
        $r=$contact->summary();
        $this->assertEquals(6, substr_count($r,'fill_ipopcard') , 'not found all the contacts from FOURNI');
        $r=$contact->summary('william');
        $this->assertEquals(2, substr_count($r,'fill_ipopcard') , 'Search does not filter');
        
    }

}
