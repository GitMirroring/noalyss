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
 * Author : Dany De Bontridder danydb@noalyss.eu $(DATE)
 */

/**
 * @file
 * @brief noalyss
 */

use PHPUnit\Framework\TestCase;

require DIRTEST . '/global.php';

/**
 * @testdox Class Ajax_MiscTest : used for testing the AJAX calls from ajax_misc
 * @backupGlobals enabled
 */
class Ajax_MiscTest extends TestCase
{

    /**
     * @var Fiche
     */
//    protected $object;
//    protected $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test method is executed.
     */
//    protected function setUp(): void
//    {
//
//
//    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test method is executed.
     */
//    protected function tearDown(): void
//    {
//        /**
//         * example
//         * if ( ! is_object($this->object->fiche_def)) return;
//         * include_once DIRTEST.'/global.php';
//         * $g_connection=Dossier::connect();
//         * $sql=new ArrayObject();
//         * $sql->append("delete from fiche_detail where f_id in (select f_id from fiche where fd_id =\$1 )");
//         * $sql->append("delete from fiche where f_id not in (select f_id from fiche_detail where \$1=\$1)");
//         * $sql->append("delete from jnt_fic_attr where fd_id  = \$1 ");
//         * $sql->append("delete from fiche_def where fd_id = \$1");
//         * foreach ($sql as $s) {
//         * $g_connection->exec_sql($s,[$this->object->fiche_def->id]);
//         * }
//         */
//    }

    /**
     * the setUpBeforeClass() template methods is called before the first test of the test case
     *  class is run
     */
//    public static function setUpBeforeClass(): void
//    {
//        //        include 'global.php';
//    }

    /**
     *  tearDownAfterClass() template methods is calleafter the last test of the test case class is run,
     *
     */
//    static function tearDownAfterClass(): void
//    {
//        //        include 'global.php';
//    }

    private function createCard_MA1()
    {
        global $g_user, $g_connection;

        $fiche_id = $g_connection->get_value("select f_id from fiche_detail where ad_id=$1 and ad_value=$2",
            array(ATTR_DEF_QUICKCODE, 'MA1'));
        if (!empty ($fiche_id)) {
           $this->cleanCard($fiche_id);
        }


        $g_user = new Noalyss_User($g_connection);
        //insert a card
        $fiche = new Fiche($g_connection);
        // card for Merchandise
        $fiche->set_fiche_def(1);
        Card_Property::load($fiche);
        $fiche->setAttribut(1, "Inserted by PHPUNIT-" . __CLASS__ . ":" . __FUNCTION__);
        $fiche->setAttribut(ATTR_DEF_TVA, '210A');
        $fiche->setAttribut(ATTR_DEF_QUICKCODE, 'MA1');
        $fiche->insert(1, $fiche->to_array());
        return $fiche;
    }

    private function cleanCard($fiche_id)
    {
        global  $g_connection;
        $g_connection->exec_sql("delete from fiche_detail where f_id = $1", array($fiche_id));
        $g_connection->exec_sql("delete from fiche where f_id = $1", array($fiche_id));
    }
    /**
     * @testdox Call fid.php
     * @covers       ajax call to fid.php
     * @backupGlobals enabled
     */
    public function testAJAX_retrieveCard()
    {
        global $g_user, $g_connection;
        $fiche=$this->createCard_MA1();

        $query = array(
            'gDossier' => DOSSIER,
            'FID' => 'MA1',
            'l' => 'e_march4_label',
            't' => 'e_march4_tva_id',
            'p' => 'e_march4_price',
            'b' => 'e_march4_price',
            'd' => 'deb',
            'j' => '3',
            'ctl' => 'undefined'
        );

        $_REQUEST = $_POST = $_GET = $query;
        ob_start();
        require NOALYSS_HOME . '/fid.php';
        $content = ob_get_contents();
        ob_end_clean();

        $expected = <<<EOF
{"flabel":"e_march4_label","name":"Inserted by PHPUNIT-Ajax_MiscTest:createCard_MA1 ","ftva_id":"e_march4_tva_id","tva_id":"210A","fPrice_sale":"e_march4_price","sell":"0","fPrice_purchase":"e_march4_price","buy":"0","answer":"ok"}
EOF;

        $this->assertEquals($expected, $content, "Incorrect answer");

        // clean card
        $this->cleanCard($fiche->id);
    }

}