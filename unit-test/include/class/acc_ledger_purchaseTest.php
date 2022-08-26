<?php

use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 * @coversDefaultClass Acc_Ledger_Purchase
 * @covers Fiche
 * 
 */
class Acc_Ledger_PurchaseTest extends TestCase
{

    /**
     * @var Acc_Ledger_Purchase
     */
    protected $object;

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
     * Parameters : $p_attribut if the no deductible attribute, the $p_value is the % not deductible, $p_amount
     * is the corresponding column in quant_purchase and $p_accounting is the counterpart
     * for this not deductible fee($p_counterpart) 
     */
    public function testInsertPurchase_No_Ded($p_attribut , $p_value,$p_column,$p_amount,$p_counterpart,$p_accounting)
    {
       global $g_connection;
      
       
        //-- modify card 29 : ELECTR
        $fiche=new Fiche($g_connection,29);
        $fiche->set_f_enable("1");
        $fiche->setAttribut($p_attribut,$p_value);
        $fiche->setAttribut($p_counterpart,$p_accounting);
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
}
