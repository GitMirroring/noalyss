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
 * @brief  test \Document
 */
require DIRTEST.'/global.php';

class DocumentTest extends TestCase
{

    /**
     * Document 
     */
    private $document;

    public static function setUpBeforeClass():void
    {
        // insert a document_modele
        $cn=Dossier::connect();
        $cn->start();
        $md_id=$cn->get_next_seq('document_modele_md_id_seq');
        $sql="insert into document_modele(md_id,md_name,md_filename,md_mimetype,md_type,md_affect)
                  values ($1,$2,$3,$4,$5,$6)";
        $cn->exec_sql($sql, array($md_id, "Balise", "all-tags.odt", "application/vnd.oasis.opendocument.text", "4", "GES"));
        $oid=$cn->lo_import(__DIR__."/data/all_tags.odt");
        $cn->exec_sql("update document_modele set md_lob = $1 where md_id=$2", [$oid, $md_id]);
               $cn->exec_sql("update  parameter set pr_value='Dossier Test' where pr_id=$1",
                ['MY_NAME']);
        $cn->exec_sql("update  parameter set pr_value='BE00112233' where pr_id=$1",
                ['MY_TVA']);
        $cn->exec_sql("update  parameter set pr_value='My street' where pr_id=$1",
                ['MY_STREET']);
      
        $cn->commit();
    }

    public static function tearDownAfterClass():void
    {
        // clean database
        $cn=Dossier::connect();
        $cn->start();
        $md_id=$cn->get_value("select max(md_id) from document_modele where md_name='Balise'");
        $oid=$cn->get_value("select md_lob from document_modele where md_id=$1", [$md_id]);
        if ($oid!="")
        {
            $cn->lo_unlink($oid);
        }
        $cn->exec_sql("delete from document_modele where md_id=$1", [$md_id]);
        $cn->exec_sql("delete from document where ag_id=1");
        $cn->commit();
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp():void
    {
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown():void
    {
        
    }
    function testBlank()
    {
        $cn=Dossier::connect();
        $document=new Document($cn);
        $document->ag_id=1;
        $document->md_type=4;
        $document->blank();
        $this->assertTrue($document->d_id>-1,"Document inserted")       ;
        $this->assertTrue(isset($document->d_number) &&($document->d_number>0),"Document inserted")       ;
                
    }
    function testCompute_filename()
    {
        $cn=Dossier::connect();
        $document=new Document($cn);
        $filename="N<ew file name (21-01-01).odt";
        $new_filename=$document->compute_filename("ACH'->42", $filename);
        $this->assertEquals("n-ew-file-name-21-01-01-ach-42.odt",$new_filename,"Sanitize filename");
    }
    function testReplace()
    {
        $cn=Dossier::connect();
        global $g_parameter;
        $g_parameter=new Noalyss_Parameter_Folder($cn);
        $document=new Document($cn);
        $array=[];
        $array['e_client']='CLIENT';
 
        $this->assertTrue ( $document->replace('MY_NAME',array()) == 'Dossier Test','MY_NAME');
        $this->assertTrue ( $document->replace('MY_TVA',array()) == 'BE00112233','MY_TVA');
        $this->assertTrue ( $document->replace('MY_STREET',array()) == 'My street','MY_STREET');
        $this->assertTrue ( $document->replace('CUST_NAME',$array) == 'Client 1','CUST_NAME');
    }
    /**
     * @testdox Generate Document::generate(), Document::parseDocument(),Document::replace(); require  unoconv -l in another session
     * @covers Document::generate(), Document::parseDocument(),Document::replace();
     */
    function testGenerate()
    {
        require_once 'global.php';
        $cn=Dossier::connect();
        $md_id=$cn->get_value('select max(md_id) md_id from document_modele where md_name=$1',['Balise']);
        $array['e_client']='CLIENT';
        $array['e_date']='21.03.2020';
        $document=new Document($cn,$md_id);
        $document->ag_id=2;
        $document->md_id=$md_id;
        $cnt_before=$cn->get_value("select count(*) from document");
        $document->generate($array);
        
        $this->assertEquals($document->d_filename ,'all-tags.odt','Generated File ');
        $cnt_after=$cn->get_value("select count(*) from document");
        $this->assertTrue ($cnt_after == $cnt_before+1,"One file generated");
    }
    
    /**
     * @testdox test $_ENV['TMP'] 
     */
    function testGenerateTmp()
    {
          $cn=Dossier::connect();
        $md_id=$cn->get_value('select max(md_id) md_id from document_modele where md_name=$1',['Balise']);
        $array['e_client']='CLIENT';
        $array['e_date']='21.03.2020';
        $document=new Document($cn,$md_id);
        $document->ag_id=2;
        $document->md_id=$md_id;
        $cnt_before=$cn->get_value("select count(*) from document");
        $tEnv=$_ENV['TMP'];
        $_ENV['TMP']='/not.exist';
        $this->assertStringContainsString ('échoué',$document->generate($array));
        $_ENV['TMP']= $tEnv;
    }
        

    /**
     * @testdox ExtractPdf export PDF tests Document::export_pdf, Document::transform2pdf()
     * 
     */
    function testExtractPdf()
    {
        require "global.php";
        $cn=Dossier::connect();
        $d_id=$cn->get_value('select max(d_id) d_id from document');
        $document=new Document($cn);
        $document->d_id=$d_id;
        $document->get();
        $this->assertEquals($document->d_filename,'all-tags.odt','get document row');
        $file=$document->transform2pdf();
        echo "Please check $file ";
        $this->assertTrue (file_exists($file),"File exist");
        
    }
}
