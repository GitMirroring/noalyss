<?php

use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 * @coversDefaultClass Acc_Ledger_Purchase
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

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
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
            "ac"=>"ACH"
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
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
                $g_connection->get_value ("select count(*) from jrn where jr_mt=$1",["1572704002.1732"]));
        
        $this->object->insert($array);
        $this->assertEquals(1,
                $g_connection->get_value ("select count(*) from jrn where jr_mt=$1",["1572704002.1732"]));
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
                                        "tvac_march1"=>22.05));
        
        $this->object->insert($array);
        $this->assertEquals(0,$g_connection->get_value("select count(*)  ".$sql));
        $this->clean_operation();
      
        // Test space in e_march0_price instead of zero must be 
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
                                        "e_march1_tva_amount"=>"",
                                        "tva_march1"=>3.83,
                                        "tvac_march1"=>22.05));
        $this->object->insert($array);
        $this->assertEquals(3.83,$g_connection->get_value("select qp_vat ".$sql));
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
        $this->assertEquals(22.08,$g_connection->get_value("select qp_vat ".$sql));
        $this->clean_operation();

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
        $this->assertContains(
                '<OPTION VALUE="3" SELECTED>Achat',
                $res);
        $this->assertContains('<INPUT TYPE="hidden" id="jrn_type" NAME="jrn_type" VALUE="ACH"',$res);
        $this->assertContains('<td class="num">  <span id="tvac" >0.0</span> </td>',$res);
        
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
        $this->assertContains('name="amount_t0" value="658.25"',$ret);
        $this->assertContains("value=\"Efface détail\" onClick=\"anc_key_clean('25','','658.25','','','0');",$ret);
        $this->assertContains('NAME="e_quant0" VALUE="1">',$ret);
    }
  
    private function clean_operation()
    {
        global $g_connection;
        $g_connection->exec_sql("delete from jrn where jr_mt=$1", ["1572704002.1732"]);
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
