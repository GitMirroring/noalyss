<?php

/*
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
 * Copyright (C) 2025 Dany De Bontridder <dany@alchimerys.be>
 * 
 */

/**
 * @file
 * @brief noalyss
 */
use PHPUnit\Framework\TestCase;

require DIRTEST . '/global.php';

/**
 * @testdox Class xxx : used for ...
 * @backupGlobals enabled
 * @coversDefaultClass
 */
class Acc_DocumentTest extends TestCase {

    /**
     * @var Fiche
     */
    protected $object;
    protected $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test method is executed.
     */
    protected function setUp(): void {
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test method is executed.
     */
    protected function tearDown(): void {
        /**
         * example
         * if ( ! is_object($this->object->fiche_def)) return;
         * include_once DIRTEST.'/global.php';
         * $g_connection=Dossier::connect();
         * $sql=new ArrayObject();
         * $sql->append("delete from fiche_detail where f_id in (select f_id from fiche where fd_id =\$1 )");
         * $sql->append("delete from fiche where f_id not in (select f_id from fiche_detail where \$1=\$1)");
         * $sql->append("delete from jnt_fic_attr where fd_id  = \$1 ");
         * $sql->append("delete from fiche_def where fd_id = \$1");
         * foreach ($sql as $s) {
         * $g_connection->exec_sql($s,[$this->object->fiche_def->id]);
         * }
         */
    }

    /**
     * the setUpBeforeClass() template methods is called before the first test of the test case
     *  class is run
     */
    public static function setUpBeforeClass(): void {
        //        include 'global.php';
    }

    /**
     * @brief Data simulation

     * @return array
     */
    function dataExample() {
        return array(
            ['0A', 4]
            , ['0B', 6]
            , [6, 6]
            , ['NONE', -1]
            , [14, -1]
            , ["  ", -1]
            , [null, -1]
        );
    }
 static function get_variable() {
        $request = array(
            "ac" => "VEN",
            "gDossier" => 25,
            "ag_ref" => "FACTUR4-25",
            "dt_id" => 4,
            "e_client" => "CLIENT1",
            "ag_timestamp" => "19.06.2025",
            "ag_hour" => "",
            "ag_remind_date" => "",
            "ag_state" => 2,
            "ag_priority" => 2,
            "ag_dest" => 1,
            "operation" => "",
            "action" => "",
            "ag_title" => "Facture",
            "ag_description" => "",
            "nb_item" => 5,
            "e_march0" => "DOCUME",
            "e_march0_label" => "Documentation",
            "e_march0_price" => 121,
            "e_quant0" => 1,
            "e_march0_tva_id" => 6,
            "e_march0_tva_amount" => 0,
            "tvac_march0" => 121,
            "tva_march0" => 0,
            "htva_march0" => 121,
            "ad_id0" => 0,
            "e_march1" => "",
            "e_march1_label" => "",
            "e_march1_price" => "",
            "e_quant1" => 1,
            "e_march1_tva_id" => "",
            "e_march1_tva_amount" => 0,
            "tvac_march1" => 0,
            "tva_march1" => 0,
            "htva_march1" => 0,
            "ad_id1" => 0,
            "e_march2" => "",
            "e_march2_label" => "",
            "e_march2_price" => "",
            "e_quant2" => 1,
            "e_march2_tva_id" => "",
            "e_march2_tva_amount" => 0,
            "tvac_march2" => 0,
            "tva_march2" => 0,
            "htva_march2" => 0,
            "ad_id2" => 0,
            "e_march3" => "",
            "e_march3_label" => "",
            "e_march3_price" => "",
            "e_quant3" => 1,
            "e_march3_tva_id" => "",
            "e_march3_tva_amount" => 0,
            "tvac_march3" => 0,
            "tva_march3" => 0,
            "htva_march3" => 0,
            "ad_id3" => 0,
            "e_march4" => "",
            "e_march4_label" => "",
            "e_march4_price" => "",
            "e_quant4" => 1,
            "e_march4_tva_id" => "",
            "e_march4_tva_amount" => 0,
            "tvac_march4" => 0,
            "tva_march4" => 0,
            "htva_march4" => 0,
            "ad_id4" => 0,
            "6853eaa02e5af_ledger" => "O",
            "6853eaa02e5af" => 1,
            "d_id" => 0,
            "ag_id" => 3,
            "f_id_dest" => "",
            "sa" => "save_action_st2",
            "save_action_st2" => "Enregistrer",
            "e_ech"=>'',
            'p_currency_code'=>0,
            'p_currency_rate'=>1,
            'bon_comm'=>'N/A',
            'e_date'=>'14.08.2020'
            
        );
        return $request;
    }
    /**
     *  tearDownAfterClass() template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass(): void {
        //        include 'global.php';
    }

//
//    public function dataExample()
//    {
//        return array([1], [2], [3]);
//    }

    /**
     * @testdox description of the test
     * @covers Acc_Balance::summary_add
     * @depend Acc_Balance::summary_init
     * @backupGlobals enabled
     * @dataProvider dataExample
     * @global $g_connection
     */
    function testFunction() {
        
    }
    /**
     * @testdox Test the standard invoice, check file
     * @covers Document::generate
     */
    function testGenerateStandard()
    {
        $cn=Dossier::connect();
        $document=new \Acc_Document($cn);
        $document->md_id=INVOICE_STD;
        $document->d_id=160;
        $array=Acc_DocumentTest::get_variable();
        $array['e_pj']='phpunit';
        $ret=$document->generate($array);
        $this->assertStringContainsString("export.php",$ret,"Incorrect URL");
        $this->assertTrue($document->d_filename=='inv-std-phpunit.pdf'
                ,'filename incorrect');
        $this->assertTrue($document->d_mimetype=="application/pdf"
                ,"incorrect mimetype [{$document->d_mimetype}]");
        
        
    }
}
