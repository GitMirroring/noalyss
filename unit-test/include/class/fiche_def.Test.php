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
 * @brief PHPUNIT test the class fiche_def
 */

/**
 * @covers Fiche_Def
 * @backupGlobals enabled
 */
class Fiche_DefTest extends Testcase
{

    protected $fiche_def;

    static function setUpBeforeClass():void
    {
        require_once 'global.php';

        global $g_connection;
        $g_connection=Dossier::connect();
        $fd_id=$g_connection->get_value("select fd_id from fiche_def where fd_label=$1", ["Test.card"]);
        if ($g_connection->count()>0)
        {
            // clean 
            $g_connection->exec_sql("delete from fiche_detail 
                      where f_id in (select f_id from fiche where fd_id=$1)", [$fd_id]);
            $g_connection->exec_sql("delete from fiche where fd_id=$1", [$fd_id]);
            $g_connection->exec_sql("delete from jnt_fic_attr where fd_id=$1", [$fd_id]);
            $g_connection->exec_sql("delete from fiche_def where fd_id=$1", [$fd_id]);
        }

        // add to this new categorie all the possible attributes
    }
    /**
     * @brief clean after testing it
     * @global type $g_connection
     */
    static function tearDownAfterClass():void
    {
       require_once 'global.php';
        global $g_connection;
        $g_connection=Dossier::connect();
        $fd_id=$g_connection->get_value("select fd_id from fiche_def where fd_label=$1", ["Test.card"]);
        if ($g_connection->count()>0)
        {
            // clean 
            $g_connection->exec_sql("delete from fiche_detail 
                      where f_id in (select f_id from fiche where fd_id=$1)", [$fd_id]);
            $g_connection->exec_sql("delete from fiche where fd_id=$1", [$fd_id]);
            $g_connection->exec_sql("delete from jnt_fic_attr where fd_id=$1", [$fd_id]);
            $g_connection->exec_sql("delete from fiche_def where fd_id=$1", [$fd_id]);
        } 
    }
    protected function setUp():void
    {
        include 'global.php';
    }

    function testCreateNewCategory()
    {
        global $g_connection;
        // create a category of card, type Charges
        $fiche_def=new Fiche_Def($g_connection);
        $aParam=["nom_mod"=>"Test.card", "fd_description"=>'PHPUNIT test', 'class_base'=>'600', 'FICHE_REF'=>3,
            'create'=>'on'];
        $this->assertEquals($fiche_def->id, 0, 'Before created');

        $fiche_def->add($aParam);

        $this->assertLessThan($fiche_def->id, 0, 'After created');

        $this->assertEquals($g_connection->get_value("select count(*) from fiche_def where fd_id=$1", [$fiche_def->id]),
                1, "Category created");
        $this->fiche_def=$fiche_def;
    }
    function getFicheDef()
    {
        global $g_connection;
        $fd_id=$g_connection->get_value("select fd_id from fiche_def where fd_label=$1", ["Test.card"]);
        $this->assertNotEquals($g_connection->count(),0,"Card category NOT found") ;
        $fiche_def=new Fiche_Def($g_connection,$fd_id);
        $this->fiche_def=$fiche_def;
        return $fiche_def;
        
    }
    /**
     * @covers hasAttribute
     * @testdox Test the Fiche_Def->hasAttribute function
     */
    function testHasAttribute()
    {
        global $g_connection;
        $fiche_def=$this->getFicheDef();
        $aProperty=$g_connection->get_array("select ad_id from jnt_fic_attr where fd_id=$1", [$fiche_def->id]);
        $this->assertFalse(empty($aProperty),"default attribute created");
        $this->assertGreaterThan(0, count($aProperty));
        foreach ($aProperty as $row)
        {
            $this->assertTrue($this->fiche_def->HasAttribute($row['ad_id']),
                    $row['ad_id'] ."this attribute is supposed to exist");
        }
        $aProperty=$g_connection->get_array("select distinct ad_id from jnt_fic_attr where     
                ad_id not in ( select ad_id from jnt_fic_attr where
                fd_id=$1)",
                [$this->fiche_def->id]);
        foreach ($aProperty as $row)
        {
            $this->assertFalse($this->fiche_def->HasAttribute($row['ad_id']), 
                    $row['ad_id'] ." this attribute does not exist");
        }
    }
    /**
     *
     * @testdox Add new Attributes and remove them
     *
     */
    function testAddNewAttribute()
    {
        global $g_connection;
        $fiche_def=$this->getFicheDef();
         $aProperty=$g_connection->get_array("select distinct ad_id from jnt_fic_attr where     
                ad_id not in ( select ad_id from jnt_fic_attr where
                fd_id=$1)",
                [$this->fiche_def->id]);
        foreach ($aProperty as $row)
        {
            $this->assertFalse($this->fiche_def->HasAttribute($row['ad_id']), $row['ad_id'] ." not existing attribute");
	    $fiche_def->insertAttribut($row['ad_id']);
	    $this->assertTrue($this->fiche_def->HasAttribute($row['ad_id']), $row['ad_id'] ." is now an  existing attribute");
	    
        }
	foreach($aProperty as $row) {
	  $fiche_def->removeAttribut([ $row['ad_id'] ]);
	  $this->assertFalse($this->fiche_def->HasAttribute($row['ad_id']), $row['ad_id'] ." cannot be removed ");
	}
	
    }
    /**
     * @testdox getAttribut
     */
    function testGetAttribut()
    {
      global $g_connection;
      $fiche_def=$this->getFicheDef();

      $aProperty=$g_connection->get_array("select ad_id from jnt_fic_attr where fd_id=$1", [$fiche_def->id]);
      $this->assertEquals(count($fiche_def->getAttribut()),count($aProperty)," number of property different in db and function getAttribut");
    }
    /**
     * @testdox if we insert a attribut ; all the cards from this category will have these attributes
     */
    function testCardAttribute()
    {
      global $g_connection;
      $fiche_def=new Fiche_Def($g_connection,5);
        $fiche_def->RemoveAttribut([20, 21, 22, 51, 52, 53]);
	$nbProperty=$g_connection->get_value("select count(ad_id) from jnt_fic_attr where fd_id=$1", [$fiche_def->id]);
	$nbCard=$g_connection->get_value("select count(f_id) from fiche where fd_id=$1", [$fiche_def->id]);
	
        // percent deductible
        $fiche_def->InsertAttribut(20);
        $fiche_def->InsertAttribut(21);
        $fiche_def->InsertAttribut(22);

        // accouting for not deductible
        $fiche_def->InsertAttribut(51);
        $fiche_def->InsertAttribut(52);
        $fiche_def->InsertAttribut(53);

        // check that all card has these attributes
	$nbAttribut=$nbProperty*$nbCard+$nbCard*6;
        $this->assertEquals($nbAttribut,
                $g_connection->get_value("select count(*) from fiche_detail join fiche using (f_id)
                where fd_id=$1",[$fiche_def->id]), "must have  6 new attributes");
			    
         $fiche_def->RemoveAttribut([20, 21, 22, 51, 52, 53]);		    
    }
}
