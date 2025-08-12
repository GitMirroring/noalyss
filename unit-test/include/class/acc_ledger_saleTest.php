<?php

use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 * @coversDefaultClass Acc_Ledger_Sale
 */
#[\AllowDynamicProperties]
class Acc_Ledger_SaleTest extends TestCase
{

    /**
     * @var Acc_Ledger_Sale
     */
    protected $object;

    /**
     * Data to include 
     * @var type 
     */
    private $array;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp():void
    {
        require DIRTEST.'/global.php';
        $this->object=new Acc_Ledger_Sale($g_connection, 2);
        $this->array=array(
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
            "e_pj"=>"VEN10",
            "e_pj_suggest"=>"VEN10",
            "e_comm"=>"Vente Service",
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
            "view_invoice"=>"Enregistrer");
        // create accounting for reversed VAT
        $g_connection->exec_sql("
        INSERT INTO public.tmp_pcmn (pcm_val,pcm_lib,pcm_val_parent,pcm_type,pcm_direct_use) VALUES
	 ('4119999','TVA Test UNIT','411','ACT','Y') on conflict  do nothing");
        // create accounting for reversed VAT with neg. amount
     $this->array1=array(   'e_client' => 'CLIENT1',
                          'nb_item' => '2',
                          'p_jrn' => '2',
                          'jrn_note_input' => '',
                          'mt' => '1734720448.0036',
                          'p_currency_rate' => '1',
                          'p_currency_code' => '0',
                          'e_comm' => '',
                          'e_date' => '27.06.2020',
                          'e_ech' => '',
                          'e_pj' => 'VEN41',
                          'e_pj_suggest' => 'VEN41',
                          'e_mp' => '0',
                          'jrn_type' => 'VEN',
                          'e_march0' => 'DEPLAC',
                          'e_march0_price' => '20',
                          'e_march0_tva_id' => '5',
                          'e_march0_tva_amount' => '0',
                          'e_quant0' => '1',
                          'e_march1' => 'DEPLAC',
                          'e_march1_price' => '-5',
                          'e_march1_tva_id' => '5',
                          'e_march1_tva_amount' => '0',
                          'e_quant1' => '1',
                          'ac' => 'COMPTA/VENMENU/VEN',
                          'bon_comm' => '',
                          'other_info' => '',
                          'opd_name' => '',
                          'od_description' => '',
                          'reverse_date' => '',
                          'ext_label' => '',
                          'jr_optype' => 'NOR',
                          'action_gestion' => '',
                          'record' => 'Enregistrement');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown():void
    {
        
    }
    private function clean_operation()
    {
        global $g_connection;
        $mt="1572714478.3155";
        //delete reconcilied operations
        $g_connection->exec_sql("
            delete from jrn 
            where jr_id in (select jr2.jr_id 
                        from jrn_rapt ra1 join jrn jr2 on (ra1.jr_id=jr2.jr_id)
                        where jr2.jr_mt=$1)",[$mt]);

        $g_connection->exec_sql("
            delete from jrn 
            where jr_id in (select jr2.jr_id 
                        from jrn_rapt ra1 join jrn jr2 on (ra1.jra_concerned=jr2.jr_id)
                        where jr2.jr_mt=$1)",[$mt]);

        $g_connection->exec_sql("delete from jrn where jr_mt=$1", [$mt]);
        $g_connection->exec_sql("delete from jrnx where j_grpt not in (select jr_grpt_id from jrn)");

        $g_connection->exec_sql("alter sequence  s_jrn_pj2 restart with 40");
        // set TVA_RATE by default
        $g_connection->exec_sql("update tva_rate set tva_poste='41142,45142' where tva_id=5");
        $g_connection->exec_sql("update tva_rate set tva_reverse_account=null where tva_id=5");


    }
    /**
     * @covers Acc_Ledger_Sale::verify
     */
    public function testVerify()
    {
        $this->object->verify_operation($this->array);
        $this->assertTrue(TRUE);
    }

    /**
     * @covers Acc_Ledger_Sale::insert
     */
    public function testInsert()
    {
        global $g_connection;
        $array=$this->array;
        $array["pa_id"]=array(2);
        $array["op"]=array(0, 1);
        $array["amount_t0"]=24.2;
        $array["hplan"]=array(array(-1), array(-1));
        $array["val"]=array(array(24, 2), array(1212.5));
        $array["mt"]="1572714478.3155";
        $this->clean_operation();
        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",["1572714478.3155"]);
        $this->assertEquals(0,$cnt);
        $this->object->insert($array);
        
        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",["1572714478.3155"]);
        $this->assertEquals(1,$cnt);
        $this->clean_operation();
        
        // If some data are corruptes
        $sql="
            from quant_sold 
                  join jrnx using(j_id)  
                   join jrn on (jr_grpt_id=j_grpt)
                where 
                   jr_mt='1572714478.3155'
                   and j_qcode='MARCHA'
                ";
        // Test space in e_quant1 instead of zero
        $array=$this->array;
        $array["pa_id"]=array(2);
        $array["op"]=array(0, 1);
        $array["amount_t0"]=24.2;
        $array["hplan"]=array(array(-1), array(-1));
        $array["val"]=array(array(24, 2), array(1212.5));
        $array["mt"]="1572714478.3155";
        $array['e_quant1']="";
        $this->object->insert($array);
        $this->assertEquals(0,$g_connection->get_value("select count(*)  ".$sql));
        $this->clean_operation();
      
        // Test space in e_march1_price instead of zero must be 
        $array=$this->array;
        $array["pa_id"]=array(2);
        $array["op"]=array(0, 1);
        $array["amount_t0"]=24.2;
        $array["hplan"]=array(array(-1), array(-1));
        $array["val"]=array(array(24, 2), array(1212.5));
        $array["mt"]="1572714478.3155";
        $array['e_march1_tva_amount']="";
        $this->object->insert($array);
        $this->assertEquals(254.63,$g_connection->get_value("select qs_vat ".$sql));
        $this->clean_operation();
       
        // Test space in e_march1_tva_amount instead of zero must be calculated
        $array=$this->array;
        $array["pa_id"]=array(2);
        $array["op"]=array(0, 1);
        $array["amount_t0"]=24.2;
        $array["hplan"]=array(array(-1), array(-1));
        $array["val"]=array(array(24, 2), array(1212.5));
        $array["mt"]="1572714478.3155";
        $array['tva_march1']="";
        $this->object->insert($array);
        $this->assertEquals(254.63,$g_connection->get_value("select qs_vat ".$sql));
        $this->clean_operation();
        
    }
    /**
     * @testdox Reverse VAT1 : find out the accounting when there is 2 accountings
     * and column tva_reverse_account is null
     * @covers Acc_Ledger_Sale::insert
     */
    public function testInsertReverseVAT1()
    {
        global $g_connection;

        $array=$this->array;
        // item 0 uses the tva_id = 5
        $array['e_march0_tva_id']=5;
        $array["pa_id"]=array(2);
        $array["op"]=array(0, 1);
        $array["amount_t0"]=24.2;
        $array["hplan"]=array(array(-1), array(-1));
        $array["val"]=array(array(24, 2), array(1212.5));
        $array["mt"]="1572714478.3155";
        $this->clean_operation();
        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",["1572714478.3155"]);
        $this->assertEquals(0,$cnt);
        $this->object->insert($array);

        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",["1572714478.3155"]);
        $this->assertEquals(1,$cnt);
        // check that the accounting for reverse VAT is 41142 and 45142
        $sql="
        select count(*)
        from jrnx j1 join jrn j2 on (j1.j_grpt=j2.jr_grpt_id)
        where 
        j2.jr_mt ='1572714478.3155'
        and j1.j_poste ='41142'
        and j1.j_debit ='t'
        ";
        $this->assertEquals(1, $g_connection->get_value($sql),'fails : reversed account debit is wrong');

        // check that the accounting for reverse VAT is 41142 and 45142
        $sql="
        select count(*)
        from jrnx j1 join jrn j2 on (j1.j_grpt=j2.jr_grpt_id)
        where 
        j2.jr_mt ='1572714478.3155'
        and j1.j_poste ='45142'
        and j1.j_debit ='f'
        ";
        $this->assertEquals(1, $g_connection->get_value($sql),'fails : reversed account credit is wrong');

        $this->clean_operation();


    }
    /**
     * @testdox Reverse VAT2 : find out the accounting when there is only 1 accounting
     * and column tva_reverse_account is null
     * @covers Acc_Ledger_Sale::insert
     */
    public function testInsertReverseVAT2()
    {
        global $g_connection;

        $array=$this->array;
        // item 0 uses the tva_id = 5
        $array['e_march0_tva_id']=5;


        $array["pa_id"]=array(2);
        $array["op"]=array(0, 1);
        $array["amount_t0"]=24.2;
        $array["hplan"]=array(array(-1), array(-1));
        $array["val"]=array(array(24, 2), array(1212.5));
        $array["mt"]="1572714478.3155";
        $this->clean_operation();
        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",["1572714478.3155"]);
        $this->assertEquals(0,$cnt);

        $g_connection->exec_sql("update tva_rate set tva_poste='#,45142' where tva_id=5");
        $this->object->insert($array);

        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",["1572714478.3155"]);
        $this->assertEquals(1,$cnt);


        // check that the accounting for reverse VAT is only 45142
        $sql="
        select count(*)
        from jrnx j1 join jrn j2 on (j1.j_grpt=j2.jr_grpt_id)
        where 
        j2.jr_mt ='1572714478.3155'
        and j1.j_poste ='45142'
        and j1.j_debit ='f'
        ";
        $this->assertEquals(1, $g_connection->get_value($sql),'fails : reversed account credit is wrong');

        // check that the accounting for reverse VAT is only 45142
        $sql="
        select count(*)
        from jrnx j1 join jrn j2 on (j1.j_grpt=j2.jr_grpt_id)
        where 
        j2.jr_mt ='1572714478.3155'
        and j1.j_poste ='45142'
        and j1.j_debit ='t'
        ";
        $this->assertEquals(1, $g_connection->get_value($sql),'fails : reversed account credit is wrong');

        $this->clean_operation();


    }
    /**
     * @testdox Reverse VAT3 : use value from column tva_reverse_account is null
     * @covers Acc_Ledger_Sale::insert
     */
    public function testInsertReverseVAT3()
    {
        global $g_connection;

        $array=$this->array;
        // item 0 uses the tva_id = 5
        $array['e_march0_tva_id']=5;


        $array["pa_id"]=array(2);
        $array["op"]=array(0, 1);
        $array["amount_t0"]=24.2;
        $array["hplan"]=array(array(-1), array(-1));
        $array["val"]=array(array(24, 2), array(1212.5));
        $array["mt"]="1572714478.3155";
        $this->clean_operation();
        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",["1572714478.3155"]);
        $this->assertEquals(0,$cnt);

        $g_connection->exec_sql("update tva_rate set tva_reverse_account='4119999' where tva_id=5");
        $this->object->insert($array);

        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",["1572714478.3155"]);
        $this->assertEquals(1,$cnt);


        // check that the accounting for reverse VAT is only 45142
        $sql="
        select count(*)
        from jrnx j1 join jrn j2 on (j1.j_grpt=j2.jr_grpt_id)
        where 
        j2.jr_mt ='1572714478.3155'
        and j1.j_poste ='4119999'
        and j1.j_debit ='t'
        ";
        $this->assertEquals(1, $g_connection->get_value($sql),'fails : reversed account credit is wrong');

        // check that the accounting for reverse VAT is only 45142
        $sql="
        select count(*)
        from jrnx j1 join jrn j2 on (j1.j_grpt=j2.jr_grpt_id)
        where 
        j2.jr_mt ='1572714478.3155'
        and j1.j_poste ='45142'
        and j1.j_debit ='f'
        ";
        $this->assertEquals(1, $g_connection->get_value($sql),'fails : reversed account credit is wrong');

        $this->clean_operation();

    }
    /**
     * @covers Acc_Ledger_Sale::insert
     */
    public function testInsertPayment()
    {
        global $g_connection;
        $this->clean_operation();
        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",["1572714478.3155"]);
        $this->assertEquals(0,$cnt);

        $sql="
            from quant_sold 
                  join jrnx using(j_id)  
                   join jrn on (jr_grpt_id=j_grpt)
                where 
                   jr_mt='1572714478.3155'
                   and j_qcode='MARCHA'
                ";
        $array=$this->array;
        $array["pa_id"]=array(2);
        $array["op"]=array(0, 1);
        $array["amount_t0"]=24.2;
        $array["hplan"]=array(array(-1), array(-1));
        $array["val"]=array(array(24, 2), array(1212.5));
        $array["mt"]="1572714478.3155";

        // create a payment method with a valid card
        $array['mp_date'] ="";
        $array['acompte'] = 0;
        $array['e_mp'] = 1;

        $this->object->insert($array);
        $this->assertEquals($array['htva_march1'],$g_connection->get_value("select qs_price ".$sql));

        // check payment
        $nQuant_FinId=$this->get_reconcilied_operation();

        $quant_fin=new Quant_Fin_SQL($g_connection,$nQuant_FinId);
        $nQuantFin_Amount=$quant_fin->getp("qf_amount") ;

        $this->assertTrue($nQuantFin_Amount ==bcadd( $array['tvac_march1'],$array['tvac_march0']),"error : sale {$array['tvac_march1']} and payment {$nQuantFin_Amount} not equal ");

        // check card used in bank
        $expected_bank=$g_connection->get_value("
  select jrn_def_bank 
  from 
        jrn_def jd1
        join payment_method pm1 on (jd1.jrn_def_id=pm1.mp_jrn_def_id) 
    where mp_id=$1",[$array['e_mp']]);

        $found_bank =$quant_fin->getp("qf_bank");

        $this->assertTrue($expected_bank==$found_bank,"error : payment done with a wrong card {$found_bank} instead of $expected_bank");

        $this->clean_operation();

    }

    /**
     * @brief return the reconcilied operation of this->object
     * @return mixed|string
     * @throws Exception
     */
    private function get_reconcilied_operation()
    {
        global $g_connection;
        $nValue=$g_connection->get_value("select jra_concerned
        from jrn_rapt where jr_id=$1",[$this->object->jr_id]);
        $nQuant_FinId=$g_connection->get_value("select qf_id from quant_fin where jr_id=$1",[$nValue]);
        return $nQuant_FinId;
    }
    /**
     * @covers Acc_Ledger_Sale::confirm
     */
    public function testConfirm()
    {
        $array=$this->array;
        $res=$this->object->confirm($array);
        \Noalyss\Facility::save_file(__DIR__."/file"
                , "Acc_Ledger_Sale_confirm.html"
                , \Noalyss\Facility::page_start().$res);
        $this->assertStringContainsString(
                '<input type="button" class="button" value="Vérifiez Imputation Analytique" onClick="verify_ca(\'\');">',
                $res);
        $this->assertStringContainsString('id="e_march1_tva_id" NAME="e_march1_tva_id" VALUE="1"', $res);
        $this->assertStringContainsString("anc_key_choice(25,'t1',1212.5,'');", $res);
    }



    /**
     * @covers Acc_Ledger_Sale::input
     * 
     */
    public function testInput()
    {
        global $g_connection;
        $_REQUEST['ac']='VEN';
        $object=new Acc_Ledger_Sale($g_connection, 2);
        
        $info=$object->input($this->array);

        var_dump($info);
        \Noalyss\Facility::save_file(__DIR__."/file","debug",$info);
        if (!is_string($info))
        {
            $this->assertTrue(FALSE);
        }
        if (empty($info)) {
            return;
        }
        \Noalyss\Facility::save_file(__DIR__."/file", "Acc_Ledger_Sale_input.html",
                \Noalyss\Facility::page_start().
                $info);
        echo "Save ".__DIR__."/file", "Acc_Ledger_Sale_input.html";

        $this->assertStringContainsString(
                'additional_tax_div',
                $info);

    }


    /**
     * @covers Acc_Ledger_Sale::heading_detail_sale
     */
    public function testHeading_detail_sale()
    {
        $a=$this->object->heading_detail_sale();
        $this->assertEquals(34,count($a));
    }
    /**
     * @covers Acc_Ledger_Sale::get_detail_sale
     */
    public function testget_detail_sale()
    {
        // 92 = 1.1.2018 and 103 = 31.12.2018
        $ret=$this->object->get_detail_sale(92,103,'all');
        $this->assertEquals(7,Database::num_row($ret),"all operation (no filter)");
        
        
        $ret=$this->object->get_detail_sale(92,103,'paid');
        $this->assertEquals(2,Database::num_row($ret),'only paid operations');

        $ret=$this->object->get_detail_sale(92,103,'unpaid');
        $this->assertEquals(5,Database::num_row($ret),'only unpaid operations');
    }
    /**
     * @testdox Reverse VAT4 :Use 2 different VAT Autoreverse mix negative and positive amounts
     * @covers Acc_Ledger_Sale::insert
     * @return void
     */
    function testInsertReverseVAT4() {
        global $g_connection;
        $array=$this->array1;
        $old_autoreverse=$g_connection->get_value("select tva_both_side from tva_rate where tva_id=3 ");
        // set autoreverse to 1
        $g_connection->get_value("update   tva_rate set tva_both_side = 1 where tva_id=3 ");
        $array['e_march1_tva_id']=3;
        // clean
        $g_connection->exec_sql("delete from jrn where jr_mt=$1",[1734717784.385]);
        $this->object->insert($array);

        $accounting=new \Acc_Operation($g_connection);
        $accounting->jr_id=$this->object->jr_id;
        $aResult=$accounting->get_jrnx_detail();

        $this->assertTrue(count($aResult)==7, 'Number of rows is  '.count($aResult)."instead of 7");

        foreach($aResult as $result) {
            switch ($result['j_poste']) {
                case '41142':
                    if ( $result['debit']=='D')
                        $this->assertEquals(4.20, $result['j_montant'],"erreur account {$result['j_poste']}");
                    else
                        $this->assertEquals(1.05, $result['j_montant'],"erreur account {$result['j_poste']}");
                    break;
                case '7000005':
                    if ( $result['debit']=='D')
                        $this->assertEquals(5, $result['j_montant'],"erreur account {$result['j_poste']}");
                    else
                        $this->assertEquals(20, $result['j_montant'],"erreur account {$result['j_poste']}");
                    break;
                case '4000005':
                    $this->assertEquals(15, $result['j_montant'],"erreur account {$result['j_poste']}");
                    break;
            }
        }

        // cancel change
        $g_connection->get_value("update   tva_rate set tva_both_side = $1 where tva_id=3 ",[$old_autoreverse]);
        $g_connection->exec_sql("delete from jrn where jr_mt=$1",[$array['mt']]);
        $g_connection->exec_sql('delete from jrnx where jrnx.j_grpt  not in (select jr_grpt_id from jrn)');

    }
    /**
     * @testdox Reverse VAT5 :Use 2 same VAT Autoreverse mix negative and positive amounts
     * @covers Acc_Ledger_Sale::insert
     * @return void
     */
    function testInsertReverseVAT5() {
        global $g_connection;
        $array=$this->array1;

        $array['e_march1_tva_id']=5;

        // clean0
        $g_connection->exec_sql("delete from jrn where jr_mt=$1",[1734717784.385]);
        $this->object->insert($array);

        $accounting=new \Acc_Operation($g_connection);
        $accounting->jr_id=$this->object->jr_id;
        $aResult=$accounting->get_jrnx_detail();

        $this->assertTrue(count($aResult)==7, 'Number of rows is  '.count($aResult)."instead of 7");

        foreach($aResult as $result) {
            switch ($result['j_poste']) {
                case '41142':
                    if ( $result['debit']=='D')
                        $this->assertEquals(4.20, $result['j_montant'],"erreur account {$result['j_poste']}");
                    else
                        $this->assertEquals(1.05, $result['j_montant'],"erreur account {$result['j_poste']}");
                    break;
                case '7000005':
                    if ( $result['debit']=='D')
                        $this->assertEquals(5, $result['j_montant'],"erreur account {$result['j_poste']}");
                    else
                        $this->assertEquals(20, $result['j_montant'],"erreur account {$result['j_poste']}");
                    break;
                case '4000005':
                    $this->assertEquals(15, $result['j_montant'],"erreur account {$result['j_poste']}");
                    break;
            }
        }

        // cancel change

        $g_connection->exec_sql("delete from jrn where jr_mt=$1",[$array['mt']]);
        $g_connection->exec_sql('delete from jrnx where jrnx.j_grpt  not in (select jr_grpt_id from jrn)');

    }

}
