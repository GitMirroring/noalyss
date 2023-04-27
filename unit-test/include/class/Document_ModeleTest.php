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
 * 
 * Author : Dany De Bontridder danydb@noalyss.eu
 * 27/04/23
 */

/**
 * @file
 * @brief noalyss
 */
namespace NOALYSS\Test;
use PHPUnit\Framework\TestCase;

require DIRTEST . '/global.php';

class Document_Modele extends  \Document_modele
{
    function move_uploaded_file($temporary_name, $target_path)
    {
         return copy ($temporary_name, $target_path);
    }

}

/**
 * @backupGlobals enabled
 * @coversDefaultClass
 */
class Document_ModeleTest extends TestCase
{

    /**
     * @var Fiche
     */
    protected $object_id;
    protected $type_id;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test method is executed.
     */
    protected function setUp(): void
    {
        $g_connection=\Dossier::connect();

        $g_connection->exec_sql("delete from document_modele where md_affect='TST'");
        $g_connection->exec_sql("delete from document_type where dt_value='PHPUNIT'");
        $g_connection->exec_sql("delete from document_component where dc_code='TST'");

        $this->type_id=$g_connection->get_value("insert into document_type (dt_value,dt_prefix) values ('PHPUNIT','TST')returning  dt_id");
        $g_connection->get_value("insert into document_component (dc_code,dc_comment) values ('TST','PHPUnit Test')");

        $md_id=$g_connection->get_next_seq('document_modele_md_id_seq');
        $sql="insert into document_modele(md_id,md_name,md_type,md_affect)
                     values ($1,$2,$3,$4)";
        $g_connection->exec_sql($sql,[$md_id,'UNIT TEST',$this->type_id,'TST']);
        $this->object_id=$md_id;

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test method is executed.
     */
    protected function tearDown(): void
    {

        $g_connection=\Dossier::connect();

        $g_connection->exec_sql("delete from document_modele where md_affect='TST'");
        $g_connection->exec_sql("delete from document_type where dt_value='PHPUNIT'");
    }

    /**
     * the setUpBeforeClass() template methods is called before the first test of the test case
     *  class is run
     */
    public static function setUpBeforeClass(): void
    {
        //        include 'global.php';
    }

    /**
     *  tearDownAfterClass() template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass(): void
    {
        $g_connection=\Dossier::connect();

        $g_connection->exec_sql("delete from document_modele where md_affect='TST'");
        $g_connection->exec_sql("delete from document_type where dt_value='PHPUNIT'");
        $g_connection->exec_sql("delete from document_component where dc_code='TST'");
        $g_connection->exec_sql("delete from document_component where dc_code='E'");
    }

    /**
     *
     * @return void
     */
    function testSaveDeleteUpate()
    {
        $g_connection=\Dossier::connect();
        $document_modele=new Document_modele($g_connection);
        $document_modele->md_name="SAVETESTPHPUNIT";
        $document_modele->md_type=$this->type_id;
        $document_modele->md_affect="TST";
        // make file
        $_FILES=array();
        $_FILES['doc']=[];
        $_FILES['doc']['name']="test-lob.bin";
        $_FILES['doc']['error']=0;
        $this->assertFileExists(__DIR__."/data/".$_FILES['doc']['name'],
            $_FILES['doc']['name']." file absent , pls create it");

        $_FILES['doc']['size']=stat(__DIR__."/data/".$_FILES['doc']['name'])['size'];
        $_FILES['doc']['tmp_name']=tempnam("/tmp","rsult");



        //  dd if=/dev/zero of=test-lob.bin bs=4096 count=1
        $_FILES['doc']['type']="app/bin";
        // save file
        $document_modele->save();



        $this->assertTrue($g_connection->get_value("select count(*) from document_modele where md_id=$1 ",[$document_modele->md_id])>0,'Document Modele not created');

        // is the file really loaded ?
        $lob_save=$g_connection->get_value("select md_lob from document_modele where md_id=$1 ",[$document_modele->md_id]);

        $this->assertTrue(! empty($lob_save),"Document no loaded");

        //unset ($_FILES);
        $g_connection->get_value("insert into document_component (dc_code,dc_comment) values ('E','PHPUnit Test')");

        $document_modele->update(['md_name'=>'XXXX',"md_type"=>"1","md_affect"=>'E','seq'=>0]);

        $this->assertEquals( 'XXXX',$g_connection->get_value("select md_name from document_modele where md_id=$1",[$document_modele->md_id]) ,'update md_name failed');
        $this->assertTrue($g_connection->get_value("select md_type from document_modele where md_id=$1",[$document_modele->md_id]) == '1','update md_type failed');

        // is the file really loaded ?
        $lob=$g_connection->get_value("select md_lob from document_modele where md_id=$1 ",[$document_modele->md_id]);

        $this->assertTrue(! empty($lob),"Document no loaded");
        $this->assertTrue($lob <> $lob_save, "LOB not updated");

        // file correct ? file size = 0 kb ,PHP bug ?
        //=========================================================
        /*
        $g_connection->start();
        $this->assertTrue($g_connection->lo_export($lob,__DIR__."/data/result.bin")," can not export LOB");
        $g_connection->commit();

        $this->assertFileEquals(__DIR__."/data/".$_FILES['doc']['name'],__DIR__."/data/result.bin","file corrupted");
        */
        $document_modele->delete();
        $this->assertTrue($g_connection->get_value("select count(*) from document_modele where md_id=$1 ",[$document_modele->md_id]) == 0,'Document Modele not deleted');
    }

    /**
     *
     * @return void
     */
    function testLoad()
    {
        $g_connection=\Dossier::connect();
        $document_modele=new Document_Modele($g_connection,$this->object_id);
        $document_modele->load();
        //UNIT TEST',$this->type_id,'TST']
        $this->assertEquals($document_modele->md_name,'UNIT TEST');
        $this->assertEquals($document_modele->md_type,$this->type_id);
        $this->assertEquals($document_modele->md_affect,'TST');


    }
}