<?php

use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 * @coversDefaultClass Acc_Ledger_Purchase
 * @covers Fiche
 * 
 */
#[\AllowDynamicProperties]
class Acc_Ledger_PurchaseTest extends TestCase
{

    /**
     * @var Acc_Ledger_Purchase
     */
    protected $object;
    private $array1;
    /**
     * @var array transmitted by _POST
     */
    private $array;
    
    static function setUpBeforeClass():void 
    {
        Acc_Ledger_PurchaseTest::setSpecialAttribute();
    }
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp():void
    {
        include 'global.php';
        $this->object=new Acc_Ledger_Purchase($g_connection, 3);
        $this->array=array
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
            "e_pj"=>"ACH6",
            "e_pj_suggest"=>"ACH6",
            "e_comm"=>"Loyer Appartement",
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
	    "p_currency_code"=>1
            
        );
        // create accounting for reversed VAT with neg. amount
        $g_connection->exec_sql("
        INSERT INTO public.tmp_pcmn (pcm_val,pcm_lib,pcm_val_parent,pcm_type,pcm_direct_use) VALUES
	 ('4119999','TVA Test UNIT','411','ACT','Y') on conflict  do nothing");
        //  variable: $array1 = used for autoreverse with 2 VAT Codes
        $this->array1=array (
            'e_client' => 'FOURNI1',
            'nb_item' => '10',
            'p_jrn' => '3',
            'jrn_note_input' => '',
            'e_comm' => 'Documentation',
            'e_date' => '30.01.2020',
            'e_ech' => '',
            'jrn_type' => 'ACH',
            'e_pj' => 'ACH53',
            'e_pj_suggest' => 'ACH53',
            'p_currency_rate' => '1',
            'p_currency_code' => '0',
            'mt' => '1734717784.385',
            'e_mp' => '0',
            'e_march0' => 'DOCUME',
            'e_march0_price' => '120',
            'e_march0_tva_id' => '5',
            'e_march0_tva_amount' => '0',
            'e_quant0' => '1',
            'e_march1' => 'DOCUME',
            'e_march1_price' => '-10',
            'e_march1_tva_id' => '3',
            'e_march1_tva_amount' => '0',
            'e_quant1' => '1',
            'e_march2' => '',
            'e_march2_price' => '',
            'e_march2_tva_id' => '',
            'e_march2_tva_amount' => '',
            'e_quant2' => '1',
            'e_march3' => '',
            'e_march3_price' => '',
            'e_march3_tva_id' => '',
            'e_march3_tva_amount' => '',
            'e_quant3' => '1',
            'e_march4' => '',
            'e_march4_price' => '',
            'e_march4_tva_id' => '',
            'e_march4_tva_amount' => '',
            'e_quant4' => '1',
            'e_march5' => '',
            'e_march5_price' => '',
            'e_march5_tva_id' => '',
            'e_march5_tva_amount' => '',
            'e_quant5' => '1',
            'e_march6' => '',
            'e_march6_price' => '',
            'e_march6_tva_id' => '',
            'e_march6_tva_amount' => '',
            'e_quant6' => '1',
            'e_march7' => '',
            'e_march7_price' => '',
            'e_march7_tva_id' => '',
            'e_march7_tva_amount' => '',
            'e_quant7' => '1',
            'e_march8' => '',
            'e_march8_price' => '',
            'e_march8_tva_id' => '',
            'e_march8_tva_amount' => '',
            'e_quant8' => '1',
            'e_march9' => '',
            'e_march9_price' => '',
            'e_march9_tva_id' => '',
            'e_march9_tva_amount' => '',
            'e_quant9' => '1',
            'ac' => 'COMPTA/MENUACH/ACH',
            'bon_comm' => '',
            'other_info' => '',
            'opd_name' => '',
            'od_description' => '',
            'reverse_date' => '',
            'ext_label' => '',
            'jr_optype' => 'NOR',
            'action_gestion' => '',
            'record' => 'Enregistrement',

        );

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown():void
    {
        
    }
    static function tearDownAfterClass():void
    {
        require 'global.php';
          global $g_connection;
        // modify attribute for card category , add VAT non ded, Tax non ded , VAT completely non ded 0%
        // category Misc Services & goods (5)
        $fiche_def=new Fiche_Def($g_connection,5);
        // prepare test , clean 
        $fiche_def->RemoveAttribut([20,21,22,50,51,52,53,31]);
    }
    /**
     * @covers Acc_Ledger_Purchase::verify
     */
    public function testVerify()
    {

        $this->object->verify_operation($this->array);
        $this->assertTrue(TRUE);

        // Test date
        try
        {
            $array=$this->array;
            $array['e_date']="g";
            $this->object->verify_operation($array);
        }
        catch (Exception $e)
        {
            $this->assertEquals(2, $e->getCode());
        }
        // Test Strict
        try
        {
            $array=$this->array;
            $array['e_date']="01.01.2018";
            $this->object->verify_operation($array);
        }
        catch (Exception $e)
        {
            $this->assertEquals(13, $e->getCode());
        }
    }

    /**
     * @covers Acc_Ledger_Purchase::insert
     */
    public function testInsert()
    {
        global $g_connection;
        $array=$this->array;
        $array["mt"]="1572704002.1732";
        $array["pa_id"]=array(2);
        $array["op"]=array(0);
        $array["amount_t0"]=658.25;
        $array['hplan']=array(array(-1));
        $array["val"]=array(array(658.25));
      
        $this->clean_operation();
        
        $this->assertEquals(0,
                $g_connection->get_value ("select count(*) from jrn where jr_mt=$1",["1572704002.1732"]),
                "avant les test verifie operation n'existe pas");
        
        $this->object->insert($array);
        $this->assertEquals(1,
                $g_connection->get_value ("select count(*) from jrn where jr_mt=$1",["1572704002.1732"]),
                "Operation Achat sauvée");
        $this->clean_operation();

                // If some data are corruptes
        $sql="
            from quant_purchase
                  join jrnx using(j_id)  
                   join jrn on (jr_grpt_id=j_grpt)
                where 
                   jr_mt='1572704002.1732'
                   and j_qcode='DOCUME'
                ";
        // Test space in e_quant0 instead of zero
        $array=$this->array;
        $array["mt"]="1572704002.1732";
        $array["nb_item"]=2;
        $array["pa_id"]=array(2);
        $array["op"]=array(0);
        $array["amount_t0"]=658.25;
        $array['hplan']=array(array(-1));
        $array["val"]=array(array(658.25));
        $array=array_merge($array, array("e_march1"=>"DOCUME",
                                        "e_march1_price"=>18.25,
                                        "e_quant1"=>"",
                                        "htva_march1"=>18.25,
                                        "e_march1_tva_id"=>1,
                                        "e_march1_tva_amount"=>22.08,
                                        "tva_march1"=>3.83,
                                        "tvac_march1"=>22.08));
        
        $this->object->insert($array);
        $this->assertEquals(0,$g_connection->get_value("select count(*)  ".$sql),"Quantite == 0 pas d'enregistrement");
        $this->clean_operation();
      
        // Test space in e_march0_price instead of zero must be 
        $array=$this->array;
        $array["mt"]="1572704002.1732";
        $array["nb_item"]=2;
        $array["pa_id"]=array(2);
        $array["op"]=array(0);
        $array["amount_t0"]=658.25;
        $array['hplan']=array(array(-1));
        $array["p_currency_code"] = 0;
        $array["p_currency_rate"] = 1;
        $array["val"]=array(array(658.25));
        $array=array_merge($array, array("e_march1"=>"DOCUME",
                                        "e_march1_price"=>18.25,
                                        "e_quant1"=>1,
                                        "htva_march1"=>18.25,
                                        "e_march1_tva_id"=>1,
                                        "e_march1_tva_amount"=>"",
                                        "tva_march1"=>3.83,
                                        "tvac_march1"=>22.08));
        $this->object->insert($array);
        $this->assertEquals(3.83,$g_connection->get_value("select qp_vat ".$sql),"Calcul TVA en EUR");
        $this->clean_operation();
       
        // Test space in e_march0_tva_amount instead of zero must be calculated
        $array=$this->array;
        $array["mt"]="1572704002.1732";
        $array["nb_item"]=2;
        $array["pa_id"]=array(2);
        $array["op"]=array(0);
        $array["amount_t0"]=658.25;
        $array['hplan']=array(array(-1));
        $array["val"]=array(array(658.25));
        $array=array_merge($array, array("e_march1"=>"DOCUME",
                                        "e_march1_price"=>18.25,
                                        "e_quant1"=>1,
                                        "htva_march1"=>18.25,
                                        "e_march1_tva_id"=>1,
                                        "e_march1_tva_amount"=>22.08,
                                        "tva_march1"=>"",
                                        "tvac_march1"=>22.05));

        $this->object->insert($array);
        // en USD , 22.08 = 20.26€ * 1.09
        $this->assertEquals(20.26,$g_connection->get_value("select qp_vat ".$sql),"Calcul TVA en USD");
        $this->clean_operation();
        
    }
    /**
     * @covers Acc_Ledger_Purchase::insert
     */
    public function testInsertPayment()
    {
        global $g_connection;
        $array=$this->array;
        $array["mt"]="1572704002.1732";
        $array["pa_id"]=array(2);
        $array["op"]=array(0);
        $array["amount_t0"]=658.25;
        $array['hplan']=array(array(-1));
        $array["val"]=array(array(658.25));
        $sql="
            from quant_purchase
                  join jrnx using(j_id)  
                   join jrn on (jr_grpt_id=j_grpt)
                where 
                   jr_mt='1572704002.1732'
                   and j_qcode='LOYER'
                ";

        $this->clean_operation();
        $array=array_merge($array, array("e_march1"=>"DOCUME",
            "e_march1_price"=>18.25,
            "e_quant1"=>"",
            "htva_march1"=>18.25,
            "e_march1_tva_id"=>1,
            "e_march1_tva_amount"=>22.08,
            "tva_march1"=>3.83,
            "tvac_march1"=>22.08
            ,"p_currency_rate"=>1
	        ,"p_currency_code"=>0
        ));

        // create a payment method with a valid card
        $payment_methodSQL=$this->insert_payment_method();
        $array['mp_date'] ="";
        $array['acompte'] = 0;
        $array['e_mp'] = $payment_methodSQL->getp("mp_id");
        $array['e_mp_qcode_'.$array['e_mp']]='CDOLLAR';
        $this->object->insert($array);

        $this->assertEquals($array['htva_march0'],$g_connection->get_value("select qp_price ".$sql));

        // check payment
        $nQuant_FinId=$this->get_reconcilied_operation();

        $quant_fin=new Quant_Fin_SQL($g_connection,$nQuant_FinId);
        $nQuantFin_Amount=$quant_fin->getp("qf_amount") ;

        $this->assertTrue($nQuantFin_Amount == -658.25,"error : purchase 658.25 and payment {$nQuantFin_Amount} not equal ");

        // check card used in bank
        $expected_bank=$g_connection->get_value("
  select jrn_def_bank 
  from 
        jrn_def jd1
        join payment_method pm1 on (jd1.jrn_def_id=pm1.mp_jrn_def_id) 
    where mp_id=$1",[$array['e_mp']]);

        $found_bank =$quant_fin->getp("qf_bank");

        $this->assertTrue($expected_bank==$found_bank,"error : payment done with a wrong card {$found_bank} instead of $expected_bank");
       $payment_methodSQL->delete();
       $this->clean_operation();

    }

    private function insert_payment_method()
    {
        global $g_connection;
        $payment_methodSQl=new Payment_method_SQL($g_connection);
        $payment_methodSQl->from_array([
            "mp_lib"=>"caisse"
            ,"mp_jrn_def_id"=>1
            ,'mp_fd_id'=>3
            ,"jrn_def_id"=>3
        ]);
        $payment_methodSQl->insert();
        return $payment_methodSQl;


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
     * @brief set special attributes to test NOT DEDUCTIBLE : private, VAT and tax
     * @global type $g_connection
     */
    public static function setSpecialAttribute()
    {
         global $g_connection;
        // modify attribute for card category , add VAT non ded, Tax non ded , VAT completely non ded 0%
        // category Misc Services & goods (5)
        $fiche_def=new Fiche_Def($g_connection,5);
        // prepare test , clean 
        $fiche_def->RemoveAttribut([20,21,22,50,51,52,53,31]);
      

        // percent deductible
        $fiche_def->InsertAttribut(ATTR_DEF_DEPENSE_NON_DEDUCTIBLE); 
        $fiche_def->InsertAttribut(ATTR_DEF_TVA_NON_DEDUCTIBLE); 
        $fiche_def->InsertAttribut(ATTR_DEF_TVA_NON_DEDUCTIBLE_RECUP);
        $fiche_def->InsertAttribut(ATTR_DEF_DEP_PRIV);
        
        // accouting for not deductible
        $fiche_def->InsertAttribut(ATTR_DEF_ACCOUNT_ND_TVA);
        $fiche_def->InsertAttribut(ATTR_DEF_ACCOUNT_ND_TVA_ND);
        $fiche_def->InsertAttribut(ATTR_DEF_ACCOUNT_ND_PERSO);
        $fiche_def->InsertAttribut(ATTR_DEF_ACCOUNT_ND);
              // check that all card has these attributes
       
    }
    public function data_no_deductible()
    {
        $aValue=array(
            [ ATTR_DEF_DEPENSE_NON_DEDUCTIBLE, 33.33 ,'qp_nd_amount',201.28,ATTR_DEF_ACCOUNT_ND_PERSO,'4890'],
            [ ATTR_DEF_TVA_NON_DEDUCTIBLE, 33.33 ,'qp_nd_tva',42.27,ATTR_DEF_ACCOUNT_ND_TVA_ND,'6740'],
            [ ATTR_DEF_TVA_NON_DEDUCTIBLE_RECUP, 33.33,'qp_nd_tva_recup',42.27 ,ATTR_DEF_ACCOUNT_ND_TVA,'6040001'],
            [ ATTR_DEF_DEP_PRIV, 33.33 ,"qp_dep_priv",201.28,ATTR_DEF_ACCOUNT_ND,'6740'],
              [ ATTR_DEF_DEPENSE_NON_DEDUCTIBLE, 20.0 ,'qp_nd_amount',120.78,ATTR_DEF_ACCOUNT_ND_PERSO,'4890'],
            [ ATTR_DEF_TVA_NON_DEDUCTIBLE, 20.0 ,'qp_nd_tva',25.36,ATTR_DEF_ACCOUNT_ND_TVA_ND,'6740'],
            [ ATTR_DEF_TVA_NON_DEDUCTIBLE_RECUP, 20.0,'qp_nd_tva_recup',25.36 ,ATTR_DEF_ACCOUNT_ND_TVA,'6040001'],
            [ ATTR_DEF_DEP_PRIV, 20.0 ,"qp_dep_priv",120.78,ATTR_DEF_ACCOUNT_ND,'6740'],
                
        );
        return $aValue;
    }
    /**
     * @testdox Purchase not deductible : VAT , TAX , PRIVATE fee
     * @dataProvider data_no_deductible
     * @parameter $p_attribut int ATTR_DEF.AD_ID $p_attribut if the no deductible attribute,
     * @parameter $p_value int is the % not deductible,
     * @parameter $p_amount float the corresponding column in quant_purchase
     * @parameter $p_accounting string is the accounting counterpart for this not deductible fee($p_counterpart)
 */
    public function testInsertPurchase_No_Ded($p_attribut , $p_value,$p_column,$p_amount,$p_counterpart,$p_accounting)
    {
       global $g_connection;
      
       
        //-- modify card 29 : ELECTR
        $fiche=new Fiche($g_connection,29);
        $fiche->set_f_enable("1");
        $fiche->set_attribute($p_attribut,$p_value);
        $fiche->set_attribute($p_counterpart,$p_accounting);
        $a_attribut=$fiche->to_array();
        $this->assertEquals($a_attribut['av_text'.$p_attribut],$p_value,"Attribut $p_attribut not set to $p_value%");
        
        $fiche->update($a_attribut);
        
        $this->assertEquals($p_value,
                $g_connection->get_value("select ad_value from fiche_detail where f_id=$1 and ad_id=$2",[29,$p_attribut]),
                "Attribut ad_id $p_attribut not inserted");
        
        $array=$this->array;
        
        $array['e_march0']='ELECTR';
        $array['e_march0_tva_id']='1';
        $array['tva_march0']=bcmul($array['e_march0_tva_amount'],0.21,2);
        $array['tvac_march0']=bcmul ($array['htva_march0'],1.21,2);
        $array['mt']='no-ded-33';
        $this->clean_operation($array['mt']);
        
        $this->object->insert($array);

        $row_quant=$g_connection->get_row("select * from quant_purchase where qp_internal in 
             ( select jr_internal from jrn where jr_mt=$1)",[$array["mt"]]);
        $this->assertFalse(empty($row_quant)," row not inserted in quant_purchase");
        
        // unit price not rounded
        $this->assertEquals(603.8990,$row_quant['qp_unit']);
        
        // rounded to 2 decimal
        $this->assertEquals(603.9000,$row_quant['qp_price']);
        $this->assertEquals($p_amount,$row_quant[$p_column]);
        
        $this->clean_operation($array['mt']);
       
        
    }
    /**
     * @testdox Purchase not deductible + autoreverse: VAT , TAX , PRIVATE fee with VAT autoreverse
     * @dataProvider data_no_deductible
     * @parameter $p_attribut int ATTR_DEF.AD_ID $p_attribut if the no deductible attribute,
     * @parameter $p_value int is the % not deductible,
     * @parameter $p_amount float the corresponding column in quant_purchase
     * @parameter $p_accounting string is the accounting counterpart for this not deductible fee($p_counterpart)
     */
    public function testInsertPurchase_No_Ded_reverse($p_attribut , $p_value,$p_column,$p_amount,$p_counterpart,$p_accounting)
    {
        global $g_connection;

        static $scenario=0;
        $scenario++;

        //-- modify card 29 : ELECTR
        $fiche=new Fiche($g_connection,29);
        $fiche->set_f_enable("1");
        $fiche->set_attribute($p_attribut,$p_value);
        $fiche->set_attribute($p_counterpart,$p_accounting);
        $a_attribut=$fiche->to_array();
        $this->assertEquals($a_attribut['av_text'.$p_attribut],$p_value,"Attribut $p_attribut not set to $p_value%");

        $fiche->update($a_attribut);

        $this->assertEquals($p_value,
                            $g_connection->get_value("select ad_value 
                                                            from fiche_detail 
                                                            where f_id=$1 and ad_id=$2",[29,$p_attribut]),
                                            "Attribut ad_id $p_attribut not inserted");

        $array=$this->array;
        $array['e_comm']="scenario [$scenario]";
        $array['e_march0']='ELECTR';
        $array['e_march0_tva_id']='5';
        $array['tva_march0']=bcmul($array['e_march0_tva_amount'],0.21,2);
        $array['tvac_march0']=bcmul ($array['htva_march0'],1.21,2);
        $array['mt']='no-ded-33'.$scenario;
        $this->clean_operation($array['mt']);

        $this->object->insert($array);

        $row_quant=$g_connection->get_row("select * from quant_purchase where qp_internal in 
             ( select jr_internal from jrn where jr_mt=$1)",[$array["mt"]]);
        $this->assertFalse(empty($row_quant)," row not inserted in quant_purchase");

        // unit price not rounded
        $this->assertEquals(603.8990,$row_quant['qp_unit']);

        // rounded to 2 decimal
        $this->assertEquals(603.9000,$row_quant['qp_price']);
        $this->assertEquals($p_amount,$row_quant[$p_column]);

        $this->clean_operation($array['mt']);


    }
    /**
     * @covers Acc_Ledger_Purchase::input 
     */
    public function testInput()
    {
        put_global([["key"=>"ac","value"=>"ACH"]]);
        $res=$this->object->input();
        \Noalyss\Facility::save_file(__DIR__."/file", 
                "acc_ledger_purchase_input.html",
                \Noalyss\Facility::page_start().$res);
        $this->assertStringContainsString(
                '<OPTION VALUE="3" SELECTED>Achat',
                $res);
        $this->assertStringContainsString('<INPUT TYPE="hidden" id="jrn_type" NAME="jrn_type" VALUE="ACH"',$res);
        $this->assertStringContainsString('<span id="tvac" >0.0</span> </td>',$res);
        
    }

    /**
     * @covers Acc_Ledger_Purchase::confirm
     */
    public function testConfirm()
    {
        $array=$this->array;
        $array["p_name"]=
        $ret=$this->object->confirm($array);
        \Noalyss\Facility::save_file(__DIR__."/file", 
                "acc_ledger_purchase_confirm.html",
                \Noalyss\Facility::page_start().$ret);
        $this->assertStringContainsString('name="amount_t0" value="658.25"',$ret);
        $this->assertStringContainsString("value=\"Efface détail\" onClick=\"anc_key_clean('25','','658.25','','','0');",$ret);
        $this->assertStringContainsString('NAME="e_quant0" VALUE="1">',$ret);
    }
  
    private function clean_operation($p_internal='1572704002.1732')
    {
        global $g_connection;
        $g_connection->exec_sql("delete from quant_purchase where j_id in ("
                . " select j_id from jrnx join jrn on (jr_grpt_id = j_grpt) where "
                . " jr_mt=$1 ) ", [$p_internal]);
        $g_connection->exec_sql("delete from jrn where jr_mt=$1", [$p_internal]);
        $g_connection->exec_sql("delete from jrnx where j_grpt not in (select jr_grpt_id from jrn)");
        $g_connection->exec_sql("alter sequence  s_jrn_pj3 restart with 52");
        // set TVA_RATE by default
        $g_connection->exec_sql("update tva_rate set tva_poste='41142,45142' where tva_id=5");
        $g_connection->exec_sql("update tva_rate set tva_reverse_account=null where tva_id=5");

    }
    /**
     * @covers ::get_detail_purchase
     */
    public function testget_detail_purchase()
    {
        // 92 = 1.1.2018 and 103 = 31.12.2018
        $ret=$this->object->get_detail_purchase(92,103,'all');
        $this->assertEquals(7,Database::num_row($ret),"all operation (no filter)");
        
        
        $ret=$this->object->get_detail_purchase(92,103,'paid');
        $this->assertEquals(2,Database::num_row($ret),'only paid operations');

        $ret=$this->object->get_detail_purchase(92,103,'unpaid');
        $this->assertEquals(5,Database::num_row($ret),'only unpaid operations');
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
        $array["mt"]="1572704002.1732";
        $this->clean_operation();
        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",[$array["mt"]]);
        $this->assertEquals(0,$cnt);
        $this->object->insert($array);

        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",[$array["mt"]]);
        $this->assertEquals(1,$cnt);
        // check that the accounting for reverse VAT is 41142 and 45142
        $sql="
        select count(*)
        from jrnx j1 join jrn j2 on (j1.j_grpt=j2.jr_grpt_id)
        where 
        j2.jr_mt ='1572704002.1732'
        and j1.j_poste ='41142'
        and j1.j_debit ='t'
        ";
        $this->assertEquals(1, $g_connection->get_value($sql),'fails : reversed account debit is wrong');

        // check that the accounting for reverse VAT is 41142 and 45142
        $sql="
        select count(*)
        from jrnx j1 join jrn j2 on (j1.j_grpt=j2.jr_grpt_id)
        where 
        j2.jr_mt ='1572704002.1732'
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
        $array["mt"]="1572704002.1732";
        $this->clean_operation();
        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",[$array["mt"]]);
        $this->assertEquals(0,$cnt);

        $g_connection->exec_sql("update tva_rate set tva_poste='41142,#' where tva_id=5");
        $this->object->insert($array);

        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",[$array["mt"]]);
        $this->assertEquals(1,$cnt);


        // check that the accounting for reverse VAT is only 41142
        $sql="
        select count(*)
        from jrnx j1 join jrn j2 on (j1.j_grpt=j2.jr_grpt_id)
        where 
        j2.jr_mt ='1572704002.1732'
        and j1.j_poste ='41142'
        and j1.j_debit ='f'
        ";
        $this->assertEquals(1, $g_connection->get_value($sql),'fails : reversed account credit is wrong');

        // check that the accounting for reverse VAT is only 45142
        $sql="
        select count(*)
        from jrnx j1 join jrn j2 on (j1.j_grpt=j2.jr_grpt_id)
        where 
        j2.jr_mt ='1572704002.1732'
        and j1.j_poste ='41142'
        and j1.j_debit ='t'
        ";
        $this->assertEquals(1, $g_connection->get_value($sql),'fails : reversed account credit is wrong');

        $this->clean_operation();


    }
    /**
     * @testdox Reverse VAT3 : use value from column tva_reverse_account is null
     * @covers Acc_Ledger_Purchase::insert
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
        $array["mt"]="1572704002.1732";
        $this->clean_operation();
        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",array($array["mt"]));
        $this->assertEquals(0,$cnt);

        $g_connection->exec_sql("update tva_rate set tva_reverse_account='4119999' where tva_id=5");
        $this->object->insert($array);

        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",array($array["mt"]));
        $this->assertEquals(1,$cnt);


        // check that the accounting for reverse VAT is only 45142
        $sql="
        select count(*)
        from jrnx j1 join jrn j2 on (j1.j_grpt=j2.jr_grpt_id)
        where 
        j2.jr_mt = $1
        and j1.j_poste ='4119999'
        and j1.j_debit ='f'
        ";
        $this->assertEquals(1, $g_connection->get_value($sql,[$array["mt"]]),'fails : reversed account credit is wrong');

        // check that the accounting for reverse VAT is only 45142
        $sql="
        select count(*)
        from jrnx j1 join jrn j2 on (j1.j_grpt=j2.jr_grpt_id)
        where 
        j2.jr_mt = $1
        and j1.j_poste ='41142'
        and j1.j_debit ='t'
        ";
        $this->assertEquals(1, $g_connection->get_value($sql,[$array["mt"]]),'fails : reversed account credit is wrong');

        $this->clean_operation();

    }

    /**
     * @testdox Reverse VAT4 :Use 2 different VAT Autoreverse mix negative and positive amounts
     * @covers Acc_Ledger_Sale::insert
     * @return void
     */
    function testInsertReverseVAT4() {
        global $g_connection;
        $array=$this->array1;
        $array['mt']='testInsertReverseVAT4';
        $old_autoreverse=$g_connection->get_value("select tva_both_side from tva_rate where tva_id=3 ");
        // set autoreverse to 1
        $g_connection->get_value("update   tva_rate set tva_both_side = 1 where tva_id=3 ");

        // clean
        $g_connection->exec_sql("delete from jrn where jr_mt=$1",[ $array['mt'] ]);
        $this->object->insert($array);

        $accounting=new \Acc_Operation($g_connection);
        $accounting->jr_id=$this->object->jr_id;
        $aResult=$accounting->get_jrnx_detail();

        $this->assertTrue(count($aResult)==7, 'Number of rows is  '.count($aResult)."instead of 7");

        foreach($aResult as $result) {
            switch ($result['j_poste']) {
                case '41142':
                    $this->assertEquals(25.20, $result['j_montant'],"erreur account {$result['j_poste']}");
                    break;
                case '6194':
                    if ( $result['debit']=='D')
                     $this->assertEquals(120, $result['j_montant'],"erreur account {$result['j_poste']}");
                    else
                        $this->assertEquals(10, $result['j_montant'],"erreur account {$result['j_poste']}");
                    break;
                case '4400005':
                    $this->assertEquals(110, $result['j_montant'],"erreur account {$result['j_poste']}");
                break;
            }
        }

        // cancel change
        $g_connection->get_value("update   tva_rate set tva_both_side = $1 where tva_id=3 ",[$old_autoreverse]);
        $g_connection->exec_sql("delete from jrn where jr_mt=$1",[$array['mt']]);


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
        $array['mt']='testInsertReverseVAT5';

        // clean
        $g_connection->exec_sql("delete from jrn where jr_mt=$1",[ $array['mt'] ]);
        $this->object->insert($array);

        $accounting=new \Acc_Operation($g_connection);
        $accounting->jr_id=$this->object->jr_id;
        $aResult=$accounting->get_jrnx_detail();

        $this->assertTrue(count($aResult)==5, 'Number of rows is  '.count($aResult)."instead of 5");

        foreach($aResult as $result) {
            switch ($result['j_poste']) {
                case '41142':
                    if ( $result['debit']=='D')
                    $this->assertEquals(23.10, $result['j_montant'],"erreur account {$result['j_poste']}");
                    else
                        $this->assertEquals(2.1, $result['j_montant'],"erreur account {$result['j_poste']}");
                    break;
                case '6194':
                    if ( $result['debit']=='D')
                        $this->assertEquals(120, $result['j_montant'],"erreur account {$result['j_poste']}");
                    else
                        $this->assertEquals(10, $result['j_montant'],"erreur account {$result['j_poste']}");
                    break;
                case '4400005':
                    $this->assertEquals(110, $result['j_montant'],"erreur account {$result['j_poste']}");
                    break;
            }
        }

        // cancel change
        $g_connection->exec_sql("delete from jrn where jr_mt=$1",[ $array['mt'] ]);


    }
}
