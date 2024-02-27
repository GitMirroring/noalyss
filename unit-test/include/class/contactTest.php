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

require DIRTEST.'/global.php';
/**
 * @backupGlobals enabled
 * @coversDefaultClass \Contact
 */
class ContactTest extends TestCase
{

    /**
     * @var Fiche
     */
    protected $object;
    protected $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test method is executed.
     */
    protected function setUp():void
    {

        $this->object = new stdClass();
        $this->object->fiche_def=0;
        $this->object->card_to_clean=array();
        $this->connection=Dossier::connect();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test method is executed.
     */
    protected function tearDown():void
    {
        if ( ! is_object($this->object->fiche_def)) return;
        include_once DIRTEST.'/global.php';
        $g_connection=Dossier::connect();
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
        include_once DIRTEST.'/global.php';

        $g_connection=Dossier::connect();
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
        include_once DIRTEST.'/global.php';

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
            $fiche=new Fiche($this->connection);
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
        include_once DIRTEST.'/global.php';
        global $g_connection;
        if ( $this->object->fiche_def == 0) {
            $this->createContactCard();
          }
        $contact=new Contact($g_connection);
        $_SERVER['REQUEST_URI']="?";
        $_SERVER['PHP_SELF']=__FILE__;
        put_global(array(
                    ["key"=>"offset","value"=>0],
                    ["key"=>"ac","value"=>"CONTACT"],
                    ["key"=>"page","value"=>1]));
        ob_start();
        $contact->summary();
        $r=ob_get_contents();
        ob_end_clean();
        $path=__DIR__."/file/";
        $filename="contact-summary-1.html";
        \Noalyss\Facility::save_file($path,$filename,$r);
        print "File saved into $path/$filename";
        $filesize=filesize ($path."/".$filename);
        $this->assertTrue($filesize > 3500 && $filesize < 3600  ," File not valide (1) $filename size $filesize");
        $this->assertEquals(5 , preg_match_all('/<tr class="/',$r)," 1. Missing card");
        $contact->filter_company(' fourni ');
        ob_start();
        $contact->summary();
        $r=ob_get_contents();
        ob_end_clean();
        $filename="contact-summary-2.html";
        \Noalyss\Facility::save_file($path,$filename,$r);

        print "File saved into $path/$filename";
        $filesize=filesize ($path."/".$filename);
        $this->assertTrue($filesize > 2240 && $filesize < 2260 ," File $filename not valide (2)");
        $this->assertEquals(3 , preg_match_all('/<tr class="/',$r), 'not found all the contacts from FOURNI');


        ob_start();
        $contact->summary('william');
        $r=ob_get_contents();
        ob_end_clean();
        $filename="contact-summary-3.html";
        \Noalyss\Facility::save_file($path,$filename,$r);
        print "File saved into $path/$filename";
        $filesize=filesize ($path."/".$filename);
        $this->assertTrue($filesize>919 && $filesize < 939 ," File not valide (3)");
        $this->assertEquals(1, preg_match_all('/<tr class="/',$r), 'Search does not filter');
        
    }

    /**
     * @testdox test the filters
     * @return void
     */
    public function testFilter()
    {
        $contact=new Contact($this->connection);
        $this->assertEquals(array(),$contact->getFilter(),' Filter not empty');
        $contact->filter_search("search_sql");
        $this->assertEquals(['search'=>'search_sql'],$contact->getFilter(),'Search not set');

        $contact->filter_search("other_sql");
        $this->assertEquals(['search'=>'other_sql'],$contact->getFilter(),'Search not replaced');

        $contact->filter_company("company_sql");
        $this->assertEquals(['search'=>'other_sql','company'=>'COMPANY_SQL'],$contact->getFilter(),'company not set');

        $contact->filter_category(1);
        $this->assertEquals(['search'=>'other_sql','company'=>'COMPANY_SQL','category'=>1],
            $contact->getFilter(),'category not set');

        $contact->filter_category(null);
        $this->assertEquals(['search'=>'other_sql','company'=>'COMPANY_SQL'],$contact->getFilter(),'category not removed');

    }

    /**
     * @depends testFilter
     * @return void
     */
    function testBuildSQL()
    {
        $contact=new Contact($this->connection);
        $contact->filter_category(1);
        $contact->filter_search("search_sql");
        $expected=strtoupper(preg_replace("/\s+/",'',"SELECT f_id,contact_fname, 
               contact_name, 
               contact_qcode, 
               contact_company, 
               contact_mobile, 
               contact_phone, 
               contact_email, 
               contact_fax
        FROM public.v_contact 
        where  f_id in (select distinct f_id from fiche_detail where ad_value ilike '%search_sql%') and fd_id=1"));
        $this->assertEquals($expected,strtoupper(preg_replace("/\s+/",'',$contact->build_sql([]))),' SQL Incorrect');

    }

    /**
     * @testdox Test SQL V_Contact_SQL
     * @return void
     */
    function testObjectSQL()
    {
        if ( $this->object->fiche_def == 0) {
            $this->createContactCard();
        }
        // take a card
        $f_id=$this->object->card_to_clean[0];
        $contact_sql=new V_Contact_SQL($this->connection,$f_id);
        $this->assertTrue($contact_sql->load()," Cannot load existing card");
        $this->assertTrue($contact_sql->contact_name=='Chantal','Data not updated');
        $this->assertTrue($contact_sql->getp("contact_name")=='Chantal','Data not updated');
    }


}
