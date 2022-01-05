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
 * @brief 
 */
use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 * @coversDefaultClass \Lettering
 */
require 'global.php';

class LetteringTest extends TestCase
{

    // const sql = find the row to letter
    const sql="select * 
                from jrnx 
                join jrn on (j_grpt=jr_grpt_id) 
                where jr_mt=$1 and j_montant=$2 and j_poste = $3";
    // const sql_letter count the row where we have j_id
    const sql_letter="select count(*) 
                        from jnt_letter 
                        join letter_cred using (jl_id)
                        join letter_deb using(jl_id) 
                        where 
                        letter_cred.j_id = $1 or letter_deb.j_id=$2";
// const sql_letter count the row where we have j_id
    const sql_letter_id="select jl_id
                        from jnt_letter 
                        join letter_cred using (jl_id)
                        join letter_deb using(jl_id) 
                        where 
                        letter_cred.j_id = $1 or letter_deb.j_id=$1";
    const number_row_jnt_letter=9;

    /**
     * @var Fiche
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test method is executed.
     */
    protected function setUp(): void
    {
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test method is executed.
     */
    protected function tearDown(): void
    {
        
    }

    static function insert_sale()
    {
        include 'global.php';

        $object=new Acc_Ledger_Sale($g_connection, 2);
        $array=array(
            "ledger_type"=>"VEN",
            "ac"=>"COMPTA/VENMENU/VEN",
            "sa"=>"p",
            "gDossier"=>25,
            "nb_item"=>2,
            "p_jrn"=>2,
            "p_jrn_predef"=>2,
            "action"=>"use_opd",
            "jrn_type"=>"VEN",
            "filter"=>"",
            "e_date"=>"24.08.2019",
            "e_ech"=>"",
            "e_client"=>"CLIENT",
            "e_pj"=>"VEN10-PHPUNIT",
            "e_pj_suggest"=>"VEN10",
            "e_comm"=>"Vente Service LetteringAccount",
            "e_march0"=>"DEPLAC",
            "e_march0_price"=>20,
            "e_quant0"=>1.21,
            "htva_march0"=>24.2,
            "e_march0_tva_id"=>1,
            "e_march0_tva_amount"=>5.08,
            "tva_march0"=>5.08,
            "tvac_march0"=>29.28,
            "e_march1"=>"MARCHA",
            "e_march1_price"=>48.5,
            "e_quant1"=>25,
            "htva_march1"=>1212.5,
            "e_march1_tva_id"=>1,
            "e_march1_tva_amount"=>254.63,
            "tva_march1"=>254.63,
            "tvac_march1"=>1467.13,
            "mp_date"=>"",
            "acompte"=>0,
            "e_comm_paiement"=>"",
            "e_mp"=>"0",
            "e_mp_qcode_1"=>"COMPTE",
            "e_mp_qcode_2"=>"",
            "p_currency_rate"=>1,
            "p_currency_code"=>0,
            "mt"=>"1-phpunit-sale-lettering",
            "view_invoice"=>"Enregistrer");
        $object->insert($array);
    }

    static function insert_purchase()
    {
        include 'global.php';
        $object=new Acc_Ledger_Purchase($g_connection, 3);
        $array=array
            (
            "gDossier"=>25,
            "nb_item"=>1,
            "p_jrn"=>3,
            "p_jrn_predef"=>3,
            "action"=>"use_opd",
            "jrn_type"=>"ACH",
            "filter"=>"",
            "e_date"=>"24.02.2018",
            "e_ech"=>"",
            "e_client"=>"FOURNI",
            "e_pj"=>"ACH6-PHPUNIT",
            "e_pj_suggest"=>"ACH6",
            "e_comm"=>"Loyer Appartement LetteringAccount",
            "e_march0"=>"LOYER",
            "e_march0_price"=>658.25,
            "e_quant0"=>1,
            "htva_march0"=>658.25,
            "e_march0_tva_id"=>4,
            "e_march0_tva_amount"=>0,
            "tva_march0"=>0,
            "tvac_march0"=>658.25,
            "p_action"=>"ach",
            "sa"=>"p",
            "e_mp"=>0,
            "view_invoice"=>"Enregistrer",
            "ac"=>"ACH",
            "p_currency_rate"=>1.09,
            "p_currency_code"=>1,
            "mt"=>"1-phpunit-purchase-lettering",
        );
        $object->insert($array);
    }

    static function insert_misc_op()
    {
        include 'global.php';
        $array=[
            "pa_id"=>array(2),
            "e_date"=>"01.09.2018",
            "desc"=>"test",
            "period"=>100,
            "e_pj"=>"ODS1",
            "e_pj_suggest"=>"ODS1",
            "e_comm"=>"test",
            "jrn_type"=>"ODS",
            "p_jrn"=>4,
            "nb_item"=>5,
            "jrn_concerned"=>"",
            "gDossier"=>25,
            "poste0"=>601,
            "ld0"=>"Achats de fournitures",
            "ck0"=>"",
            "amount0"=>100,
            "op"=>Array(0),
            "amount_t0"=>100,
            "hplan"=>array(array(-1)),
            "val"=>array(array("0"=>array("0"=>100))),
            "poste1"=>"4511",
            "ld1"=>"TVA à payer 21%",
            "amount1"=>100,
            "opd_name"=>"",
            "od_description"=>"",
            "reverse_date"=>"",
            "ext_label"=>"",
            "jr_optype"=>"NOR",
            "p_currency_rate"=>1,
            "p_currency_code"=>0,
            "mt"=>"1-phpunit-ods-lettering"
        ];
        global $g_connection;
        $ledger=new Acc_Ledger($g_connection, 4);
        $ledger->save($array);
    }

    static function insert_financial()
    {
        include 'global.php';
        $array=array
            (
            "ac"=>"COMPTA/MENUFIN/FIN",
            "pa_id"=>array("0"=>1),
            "p_jrn"=>1,
            "nb_item"=>5,
            "last_sold"=>0,
            "first_sold"=>0,
            "e_pj"=>'FIN48',
            "e_pj_suggest"=>'FIN48',
            "e_date"=>"13.06.2020",
            "mt"=>"1-phpunit-fin-lettering",
            "sa"=>"n",
            "e_other0"=>"CLIENT",
            "e_other0_comment"=>"",
            "e_other0_amount"=>1496.41,
            "e_concerned0"=>"",
            "dateop0"=>"",
            "chdate"=>1,
            "e_other1"=>"FOURNI",
            "e_other1_comment"=>"",
            "e_other1_amount"=>-603.9,
            "e_concerned1"=>"",
            "dateop1"=>"",
            "e_other2"=>"",
            "e_other2_comment"=>"",
            "e_other2_amount"=>0,
            "e_concerned2"=>'',
            "dateop2"=>'',
            "e_other3"=>'',
            "e_other3_comment"=>'',
            "e_other3_amount"=>0,
            "e_concerned3"=>'',
            "dateop3"=>'',
            "e_other4"=>'',
            "e_other4_comment"=>'',
            "e_other4_amount"=>0,
            "e_concerned4"=>"",
            "dateop4"=>'',
            "confirm"=>"Confirmer",
            "pj"=>"FIN-LET1"
        );
        $object=new Acc_Ledger_Fin($g_connection, 1);
        $object->insert($array);
    }

    /**
     * the setUpBeforeClass() template methods is called before the first test of the test case
     *  class is run 
     */
    public static function setUpBeforeClass(): void
    {
        include 'global.php';
        LetteringTest::insert_sale();
        LetteringTest::insert_purchase();
        LetteringTest::insert_financial();
        LetteringTest::insert_misc_op();
    }

    /**
     *  tearDownAfterClass() template methods is calleafter the last test of the test case class is run,
     *
     */
    static function tearDownAfterClass(): void
    {
        include 'global.php';
        $g_connection->exec_sql("delete from jrnx where j_grpt in (select jr_grpt_id 
              from jrn where jr_mt like '1-phpunit-%-lettering')");
        $g_connection->exec_sql("delete from jrn where jr_mt like '1-phpunit-%-lettering'");
    }

    /**
     * @brief save couple of operation
     * @testdox Lettering:insert_couple() 
     */
    function testInsertCouple()
    {
        global $g_connection;
        $lettering=new Lettering_Card($g_connection);

        // find j_id from sale & financial and insert_couple
        $j_id_sale=$g_connection->get_row(self::sql, ['1-phpunit-sale-lettering', 1496.41, '4000004']);
        $j_id_financial=$g_connection->get_row(self::sql, ['1-phpunit-fin-lettering', 1496.41, '4000004']);

        // insert into lettering and check
        $lettering->insert_couple($j_id_financial['j_id'], $j_id_sale['j_id']);
        $is_inserted=$g_connection->get_value(self::sql_letter, [$j_id_sale['j_id'], $j_id_sale['j_id']]);
        $this->assertEquals(1, $is_inserted, 'sale : lettering not done');

        // insert into lettering and check
        $is_inserted=$g_connection->get_value(self::sql_letter, [$j_id_financial['j_id'], $j_id_financial['j_id']]);
        $this->assertEquals(1, $is_inserted, 'fin: lettering not done');
        // find j_id from purchase & financial and insert_couple
        $j_id_purchase=$g_connection->get_row(self::sql, ['1-phpunit-purchase-lettering', 603.90, '4400004']);
        $j_id_financial2=$g_connection->get_row(self::sql, ['1-phpunit-fin-lettering', 603.90, '4400004']);

        // insert into lettering and check
        $lettering->insert_couple($j_id_financial2['j_id'], $j_id_purchase['j_id']);
        $is_inserted=$g_connection->get_value(self::sql_letter, [$j_id_purchase['j_id'], $j_id_purchase['j_id']]);
        $this->assertEquals(1, $is_inserted, 'purchase: lettering not done');

        // insert into lettering and check
        $is_inserted=$g_connection->get_value(self::sql_letter, [$j_id_financial2['j_id'], $j_id_financial2['j_id']]);
        $this->assertEquals(1, $is_inserted, 'fin: lettering not done');
        $this->clean_letter();
        $this->assertEquals(self::number_row_jnt_letter, $g_connection->get_value("select count(*) from jnt_letter"),
                'insertCouple lettering not cleaned');
    }

    function clean_letter()
    {
        global $g_connection;
        $delete="
            delete from jnt_letter 
            where jl_id in (select jl_id from letter_cred 
                            where 
                            j_id in (select j_id from jrn join jrnx on (jr_grpt_id=j_grpt) 
                                where jr_mt like '1-phpunit-%-lettering')
                         )
                 or jl_id in (select jl_id from letter_deb
                            where 
                            j_id in (select j_id from jrn join jrnx on (jr_grpt_id=j_grpt) 
                                where jr_mt like '1-phpunit-%-lettering')
                         )
                ";
        $g_connection->exec_sql($delete);
    }

    /**
     * @brief save array of date
     * @testdox Lettering:save() 
     */
    function save()
    {
        global $g_connection;
        $g_connection=Dossier::connect();
        $lettering=new Lettering_Card($g_connection);

        $j_id_sale=$g_connection->get_row(self::sql, ['1-phpunit-sale-lettering', 1496.41, '4000004']);
        $j_id_financial=$g_connection->get_row(self::sql, ['1-phpunit-fin-lettering', 1496.41, '4000004']);

        $array=array(
            "letter_j_id"=>array($j_id_sale['j_id']),
            "ck"=>array($j_id_sale['j_id']),
            'j_id'=>$j_id_financial['j_id'],
            'jnt_id'=>-2
        );
        $lettering->save($array);

        $is_inserted=$g_connection->get_value(self::sql_letter, [$j_id_sale['j_id'], $j_id_sale['j_id']]);
        $this->assertEquals(1, $is_inserted, 'sale : lettering not done');
        $key=$g_connection->get_row(self::sql_letter_id, [
            $j_id_sale['j_id']
        ]);
        // insert into lettering and check
        $is_inserted=$g_connection->get_value(self::sql_letter, [$j_id_financial['j_id'], $j_id_financial['j_id']]);
        $this->assertEquals(1, $is_inserted, 'fin: lettering not done');
        $key2=$g_connection->get_row(self::sql_letter_id, [
            $j_id_sale['j_id']
        ]);
        $this->assertTrue($key['jl_id']==$key2['jl_id'], "operation not linked together");
        

        $this->assertEquals(1,$g_connection->get_value("select count(*) from jrn_rapt 
                where jr_id=$1 or jra_concerned=$1",[$j_id_sale['jr_id']]),"Lettering::save does not reconcilied");
        $g_connection->exec_sql("delete from jrn_rapt where jr_id=$1 or jra_concerned=$1",
                [$j_id_sale['jr_id']]);
    }
    
    function insert_reconcilied()
    {
        global $g_connection;
        $j_id_sale=$g_connection->get_row(self::sql, ['1-phpunit-sale-lettering', 1496.41, '4000004']);
        $j_id_financial=$g_connection->get_row(self::sql, ['1-phpunit-fin-lettering', 1496.41, '4000004']);
        $acc_reconciliation_lettering=new Acc_Reconciliation_Lettering($g_connection);
        $acc_reconciliation_lettering->insert_reconcilied($j_id_financial['j_id'], $j_id_sale['j_id']);
        
        // not in jnt_letter 
         $is_inserted=$g_connection->get_value(self::sql_letter, [$j_id_financial['j_id'], $j_id_financial['j_id']]);
         $this->assertEquals(0,$is_inserted,"Error : also inserted in lettering");
         
         $is_inserted=$g_connection->get_value(self::sql_letter, [$j_id_sale['j_id'], $j_id_sale['j_id']]);
         $this->assertEquals(0,$is_inserted,"Error : also inserted in lettering");
         
         $this->assertEquals(1,$g_connection->get_value("select count(*) from jrn_rapt where jr_id = $1
             or jra_concerned=$1",array($j_id_sale["jr_id"]))," sale not reconcilied");

         $this->assertEquals(1,$g_connection->get_value("select count(*) from jrn_rapt where jr_id = $1
             or jra_concerned=$1",array($j_id_financial["jr_id"]))," fin not reconcilied");
                 
         
    }
    /**
     * @testdox Lettering:save() 
     */
    function testSave()
    {
        global $g_connection;
        $g_connection=Dossier::connect();
        $this->clean_letter();
        $this->assertEquals(self::number_row_jnt_letter, $g_connection->get_value("select count(*) from jnt_letter"),
                'testSave lettering not cleaned');

        $this->save();
        $this->clean_letter();
        $this->assertEquals(self::number_row_jnt_letter, $g_connection->get_value("select count(*) from jnt_letter"),
                'testSave lettering not cleaned');
    }
    
    /**
     * @testdox Acc_Reconciliation_Lettering::insert_reconcilied() 
     */
    function testInsertReconcilied()
    {
        global $g_connection;
        $g_connection=Dossier::connect();
        $this->clean_letter();
        $this->assertEquals(self::number_row_jnt_letter, $g_connection->get_value("select count(*) from jnt_letter"),
                'testSave lettering not cleaned');

        $this->insert_reconcilied();
        $this->clean_letter();
        $this->assertEquals(self::number_row_jnt_letter, $g_connection->get_value("select count(*) from jnt_letter"),
                'testSave lettering not cleaned');
    }

}
