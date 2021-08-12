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
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief test the Card Properties 
 */

/**
 * @testdox Test card : save attributes, insert card, create card category 
 * @covers Card_Property 
 * @backupGlobals enabled
 */
class Card_PropertyTest extends TestCase
{

    const FICHE_DEF='TESTPROPERTY';
    const FICHE_QCODE='PHPUNIT_CARD';

    /**
     * @brief 
     * @global type $g_connection
     */
    static function setUpBeforeClass()
    {
        require_once 'global.php';
        global $g_connection;
        $g_connection=Dossier::connect();
        // clean if exists
        $fiche_def_id=$g_connection->get_value("select fd_id from fiche_def where fd_label=$1", [self::FICHE_DEF]);
        if ($g_connection->size()>0)
        {
            $g_connection->exec_sql("delete from jnt_fic_attr where fd_id=$1", [$fiche_def_id]);

            $g_connection->exec_sql("delete from fiche_detail where f_id in (select f_id from fiche where fd_id=$1)",
                    [$fiche_def_id]);
            $g_connection->exec_sql("delete from fiche where fd_id=$1", [$fiche_def_id]);
            $g_connection->exec_sql("delete from fiche_def where fd_id=$1", [$fiche_def_id]);
        }

        // create a category of card, type Charges
        $fiche_def=new Fiche_Def($g_connection);
        $aParam=["nom_mod"=>"TESTPROPERTY", "fd_description"=>'PHPUNIT test', 'class_base'=>'600', 'FICHE_REF'=>3,
            'create'=>1];
        $fiche_def->add($aParam);

        // add to this new categorie all the possible attributes
        $aProperty=$g_connection->get_array("select ad_id from attr_def 
            where 
            ad_id not in (            select ad_id from jnt_fic_attr where fd_id=$1)", [$fiche_def->id]);
        foreach ($aProperty as $property)
        {
            $fiche_def->InsertAttribut($property['ad_id']);
        }
        $fiche=new Fiche($g_connection);
        $fiche->attribut=$fiche_def->getAttribut();
        foreach ($fiche->attribut as $row)
        {
            $fiche->setAttribut($row->ad_id, "av_text = {$row->ad_id}");
        }

        $fiche->setAttribut(ATTR_DEF_QUICKCODE, self::FICHE_QCODE);
        $fiche->setAttribut(ATTR_DEF_ACCOUNT, '600');
        $fiche->setAttribut(ATTR_DEF_TVA, '');
        $fiche->insert($fiche_def->id, $fiche->to_array());
        $fiche->load();
    }

    public static function tearDownAfterClass()
    {
        require_once 'global.php';
        global $g_connection;
        $g_connection=Dossier::connect();
        // clean if exists
        $fiche_def_id=$g_connection->get_value("select fd_id from fiche_def where fd_label=$1", [self::FICHE_DEF]);
        if ($g_connection->size()>0)
        {
            $g_connection->exec_sql("delete from jnt_fic_attr where fd_id=$1", [$fiche_def_id]);

            $g_connection->exec_sql("delete from fiche_detail where f_id in (select f_id from fiche where fd_id=$1)",
                    [$fiche_def_id]);
            $g_connection->exec_sql("delete from fiche where fd_id=$1", [$fiche_def_id]);
            $g_connection->exec_sql("delete from fiche_def where fd_id=$1", [$fiche_def_id]);
        }
    }

    public function getFicheDef()
    {
        require_once 'global.php';

        global $g_connection;
        $g_connection=Dossier::connect();
        $fiche_def_id=$g_connection->get_value("select fd_id from fiche_def where fd_label=$1", [self::FICHE_DEF]);
        $this->assertEquals($g_connection->size(), 1, 'find fiche_def.fd_id');
        $fiche_def=new Fiche_Def($g_connection, $fiche_def_id);
        return $fiche_def;
    }

    public function getFiche()
    {
        require_once 'global.php';

        global $g_connection;
        $g_connection=Dossier::connect();
        $fiche_id=$g_connection->get_value("select f_id from fiche_detail where ad_id=23 and ad_value=$1 ",
                [self::FICHE_QCODE]);
        $this->assertEquals($g_connection->size(), 1, 'find fiche.f_id');
        $fiche=new Fiche($g_connection, $fiche_id);
        return $fiche;
    }

    public function testPrint()
    {
        $fiche=$this->getFiche();
        foreach ($fiche->attribut as $row)
        {
            $result=$row->print();
            $this->assertStringStartsWith("<TR>", $result, "does not start with TR");
            $this->assertStringEndsWith("</TR>", $result, "does not end with TR");
        }
    }

    /**
     *  @testdox Update with an existing one
     */
    public function testUpdate()
    {
        $fiche_def=$this->getFiche();
        $fiche=$this->getFiche();
        $fiche->load($fiche);
        $name="test ".microtime();
        $this->assertFalse($fiche->getAttribut(1)==$name, 'name different');
        $fiche->setAttribut(1, $name);
        $aProperty=$fiche->to_array();
        $this->assertEquals($name, $aProperty['av_text1'], 'name identical in array');

        Card_Property::update($fiche);

        Card_Property::load($fiche);
        $this->assertEquals(trim($name), trim($fiche->strAttribut(1)), 'name identical in DB');
        $this->assertEquals(trim($name), trim($fiche->getName()), 'name identical in DB');
    }

    public function testInput()
    {
        $fiche=$this->getFiche();
        foreach ($fiche->attribut as $row)
        {
            $result=$row->print();
            $this->assertStringStartsWith("<TR>", $result, "does not start with TR");
            $this->assertStringEndsWith("</TR>", $result, "does not end with TR");
        }
    }

    /**
     * @testdox Test load with a new card and a existing one
     */
    public function testLoad()
    {
        global $g_connection;
        $fiche=$this->getFiche();
        Card_Property::load($fiche);
        $this->assertEquals(count($fiche->attribut), 35, 'there are not 35 attributes');
        $fiche=new Fiche($g_connection);
        Card_Property::load($fiche);
        $this->assertTrue(empty($fiche->attribut),'Card property must be equals to 0 (unknown category ');
        $fiche->set_fiche_def(5);
        Card_Property::load($fiche);
        $nb_attribut=$g_connection->get_value("select count(*) from jnt_fic_attr where fd_id=$1",[5]);
        $this->assertTrue(count($fiche->attribut)==$nb_attribut,
                'count of card properties must be the same than the card category (fiche_def )');
    }

}
