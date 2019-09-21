<?php

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
 * @brief Test file for Noalyss_SQL , which is an abstract class
 */
use PHPUnit\Framework\TestCase;

require_once NOALYSS_INCLUDE."/database/poste_analytique_sql.class.php";

class Noalyss_SQLTest extends TestCase
{

    /**
     * @var Poste_analytique_SQL
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        global $g_connection;
        $this->object=new Poste_analytique_SQL($g_connection);
        $a_sql=[
            'delete from poste_analytique',
            "delete from  plan_analytique",
            "INSERT INTO public.plan_analytique
(pa_id, pa_name, pa_description)
VALUES(1, 'ACTIVITE', 'Activité commerciale Alchimerys sprl');
",
            "INSERT INTO poste_analytique (po_id,po_name,pa_id,po_amount,po_description,ga_id) VALUES 
(1,'PHPCOMPTA',1,0.0000,'E/S pour le projet PHPCOMPTA',NULL)
,(2,'CONSULTING',1,0.0000,'Consulting',NULL)
,(3,'DEVELOPPEMENT',1,0.0000,'Développement',NULL)
,(4,'DOCUMENTATION',1,0.0000,NULL,NULL)"];

        for ($i=0; $i<count($a_sql); $i++)
        {
            $g_connection->exec_sql($a_sql[$i]);
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    //@covers Noalyss_SQL::save
    public function testsave()
    {
        global $g_connection;
        // Create a new accounting
        $this->object->set("po_name", "Nouveau poste");
        $this->object->set("pa_id", "1");
        $this->object->set("po_amount", "0");
        $this->object->save();
        $id=$this->object->get("po_id");
        $this->assertFalse(empty($id));
        $this->assertGreaterThan(4, $id);
        $this->assertTrue(is_numeric($id));
        // update a accounting
        $obj=new Poste_analytique_SQL($g_connection, 2);
        $this->assertEquals($obj->get("po_name"), 'CONSULTING');
        $obj->set("po_description", "testing update");
        $obj->update();
        $obj_compare=new Poste_analytique_SQL($g_connection, 2);
        $this->assertEquals($obj_compare->get("po_description"), 'testing update');
    }

    //@covers Noalyss_SQL::get
    public function testget()
    {
        $this->object->set("po_id", 3);
        $this->object->load();
        $this->assertEquals($this->object->get("po_name"), "DEVELOPPEMENT");
    }

    //@covers Noalyss_SQL::set
    public function testset()
    {
        $this->object->set("po_id", 3);
        $this->object->load();
        $this->assertEquals($this->object->get("po_name"), "DEVELOPPEMENT");
    }

    //@covers Noalyss_SQL::getp
    public function testgetp()
    {
        $this->object->set("po_id", 3);
        $this->object->load();
        $this->assertEquals($this->object->get("po_name"), "DEVELOPPEMENT");
    }

    //@covers Noalyss_SQL::setp
    public function testsetp()
    {
        $this->object->set("po_id", 3);
        $this->object->load();
        $this->assertEquals($this->object->get("po_name"), "DEVELOPPEMENT");
    }

    //@covers Noalyss_SQL::insert
    public function testinsert()
    {
        global $g_connection;
        // Create a new accounting
        $this->object->set("po_name", "Nouveau poste");
        $this->object->set("pa_id", "1");
        $this->object->set("po_amount", "1250");
        $this->object->save();
        $id=$this->object->get("po_id");
        $this->assertFalse(empty($id));
        $this->assertGreaterThan(4, $id);
        $this->assertTrue(is_numeric($id));
    }

    //@covers Noalyss_SQL::delete
    public function testdelete()
    {
        $this->object->set("po_id", 2);
        $this->object->load();
        $id=$this->object->get("po_id");
        $this->assertEquals($id, 2);
        $this->object->delete();
        $this->object->load();
        $id=$this->object->get("po_id");
        $this->assertEquals($id, -1);
    }

    //@covers Noalyss_SQL::update
    public function testupdate()
    {
        global $g_connection;
        // update a accounting
        $obj=new Poste_analytique_SQL($g_connection, 2);
        $this->assertEquals($obj->get("po_name"), 'CONSULTING');
        $obj->set("po_description", "testing update");
        $obj->update();
        $obj_compare=new Poste_analytique_SQL($g_connection, 2);
        $this->assertEquals($obj_compare->get("po_description"), 'testing update');
    }

    //@covers Noalyss_SQL::set_pk_value
    public function testset_pk_value()
    {
        $this->object->set_pk_value("pa_id");
        $this->assertEquals($this->object->get_pk_value(), 'pa_id');
    }

    //@covers Noalyss_SQL::get_pk_value
    public function testget_pk_value()
    {
        $pk=$this->object->get_pk_value();
        $this->assertEquals(-1, $pk);
    }

    //@covers Noalyss_SQL::load
    public function testload()
    {
        $this->object->set("po_id", 2);
        $this->object->load();
        $id=$this->object->get("po_id");
        $this->assertEquals($id, 2);
    }

    //@covers Noalyss_SQL::get_info
    public function testget_info()
    {
        $str=$this->object->get_info();
        $this->assertFalse(empty($str));
        $this->assertEquals(var_export($this->object, true), $str);
    }

    //@covers Noalyss_SQL::verify
    public function testverify()
    {
        $this->assertEquals($this->object->verify(), 0);
    }

    //@covers Noalyss_SQL::from_array
    public function testfrom_array()
    {
        $this->object->from_array(["po_id"=>6, "po_name"=>"Test Unitaire", "po_description"=>"", "po_amount"=>0, "pa_id"=>1]);
        $this->object->insert();
        $this->object->load();
        $this->assertEquals($this->object->get("po_name"), "TESTUNITAIRE");
    }

    //@covers Noalyss_SQL::to_array
    public function testto_array()
    {
        $this->object->set("po_id", "4");
        $this->object->load();
        $array=$this->object->to_array();

        $this->assertTrue(is_array($array));
        $this->assertFalse(empty($array["po_name"]));
        $this->assertEquals($array["po_name"], "DOCUMENTATION");
    }

    //@covers Noalyss_SQL::seek
    function testseek()
    {
        $this->assertFalse(empty($this->object->seek("order by po_id")));
    }

    //@covers Noalyss_SQL::next
    public function testnext()
    {
        $ret=$this->object->seek("order by po_name");
        $nb_count=Database::num_row($ret);
        $this->assertEquals($nb_count, 4);
        $po_name=["CONSULTING", "DEVELOPPEMENT", "DOCUMENTATION", "PHPCOMPTA"];
        for ($i=0; $i<$nb_count; $i++)
        {
            $array=$this->object->next($ret, $i);
            $this->assertEquals($po_name[$i], $array->get('po_name'));
        }
    }

    //@covers Noalyss_SQL::get_object
    public function testget_object()
    {
        $ret=$this->object->seek("order by po_name");
        $nb_count=Database::num_row($ret);
        $this->assertEquals($nb_count, 4);
        $po_name=["CONSULTING", "DEVELOPPEMENT", "DOCUMENTATION", "PHPCOMPTA"];
        for ($i=0; $i<$nb_count; $i++)
        {
            $array=$this->object->get_object($ret, $i);
            $this->assertEquals($po_name[$i], $array->get('po_name'));
        }
    }

    //@covers Noalyss_SQL::collect_objects
    function testcollect_objects()
    {
        $array=$this->object->collect_objects(" where po_id > $1 ",[2]);
        $this->assertFalse(empty($array));
        $this->assertTrue(is_array($array));
        $this->assertEquals(count($array),2);
    }

    //@covers Noalyss_SQL::count
    public function testcount()
    {
        $cn=$this->object->count(" where po_id > $1 ",[2]);
        $this->assertEquals($cn,2);
    }

    //@covers Noalyss_SQL::exist
    public function testexist()
    {
        $this->object->set("po_id", "3");
        $this->object->load();
        $this->assertEquals($this->object->exist(),1);
        $this->object->set("po_id", "7");
        $this->object->load();
        $this->assertEquals($this->object->exist(),0);
    }

    //@covers Noalyss_SQL::build_query
    public function testbuild_query()
    {
        $str=$this->object->build_query();
        $expected=" select po_id,po_name,pa_id,po_amount,po_description,ga_id from public.poste_analytique where po_id = $1";
        $this->assertEquals($str,$expected);
    }

    //@covers Noalyss_SQL::get_all_to_array
    public function testget_all_to_array()
    {
        $array=$this->object->get_all_to_array("po_name");
        $this->assertFalse(empty($array));
        $this->assertEquals(count($array),4);
        $po_name=["CONSULTING", "DEVELOPPEMENT", "DOCUMENTATION", "PHPCOMPTA"];

        for ($i=0;$i<count($array);$i++)
        {
            $this->assertTrue(isset($array[$po_name[$i]]));
        }

    }

}
