<?php

use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 * @coversDefaultClass Acc_Ledger_Sold
 */
class Acc_Ledger_SoldTest extends TestCase
{

    /**
     * @var Acc_Ledger_Sold
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
    protected function setUp()
    {
        include 'global.php';
        $this->object=new Acc_Ledger_Sold($g_connection, 2);
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
            "view_invoice"=>"Enregistrer");
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }
    private function clean_operation()
    {
        global $g_connection;
        $g_connection->exec_sql("delete from jrn where jr_mt=$1", ["1572714478.3155"]);
        $g_connection->exec_sql("delete from jrnx where j_grpt not in (select jr_grpt_id from jrn)");
        $g_connection->exec_sql("alter sequence  s_jrn_pj2 restart with 40");
    }
    /**
     * @covers Acc_Ledger_Sold::verify
     */
    public function testVerify()
    {
        $this->object->verify_operation($this->array);
        $this->assertTrue(TRUE);
    }

    /**
     * @covers Acc_Ledger_Sold::insert
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
        $this->assertEquals(254.6250,$g_connection->get_value("select qs_vat ".$sql));
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
     * @covers Acc_Ledger_Sold::confirm
     */
    public function testConfirm()
    {
        $array=$this->array;
        $res=$this->object->confirm($array);
        \Noalyss\Facility::save_file(__DIR__."/file"
                , "acc_ledger_sold_confirm.html"
                , \Noalyss\Facility::page_start().$res);
        $this->assertContains(
                '<input type="button" class="button" value="Vérifiez Imputation Analytique" onClick="verify_ca(\'\');">',
                $res);
        $this->assertContains('id="e_march1_tva_id" NAME="e_march1_tva_id" VALUE="1"', $res);
        $this->assertContains("anc_key_choice(25,'t1',1212.5,'');", $res);
    }



    /**
     * @covers Acc_Ledger_Sold::input
     * @todo   Implement testInput().
     */
    public function testInput()
    {
        global $g_connection;
        $_REQUEST['ac']='VEN';
        $object=new Acc_Ledger_Sold($g_connection, 2);
        
        $info=$object->input($this->array);
        if (!is_string($info))
        {
            $this->assertTrue(FALSE);
        }
        \Noalyss\Facility::save_file(__DIR__."/file", "acc_ledger_sold_input.html",
                \Noalyss\Facility::page_start().
                $info);
        $this->assertContains(
                'NAME="e_client" ID="e_client" VALUE="CLIENT" SIZE="20"  ondblclick="fill_ipopcard(this);" ', $info);
        $this->assertContains(
                '<INPUT TYPE="TEXT"  class="input_text"  id="e_pj" name="e_pj" value="VEN10" placeholder="" title=""',
                $info);
        $this->assertContains('ID="add_item" VALUE="Ajout article"  onClick="ledger_add_row()">', $info);
    }


    /**
     * @covers Acc_Ledger_Sold::heading_detail_sale
     * @todo   Implement testHeading_detail_sale().
     */
    public function testHeading_detail_sale()
    {
        $a=$this->object->heading_detail_sale();
        $this->assertEquals(28,count($a));
    }
    /**
     * @covers Acc_Ledger_Sold::get_detail_sale
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
}
