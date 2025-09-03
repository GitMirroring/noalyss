<?php
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Acc_Ledger
 */
class Acc_LedgerTest extends TestCase
{

    /**
     * @var Acc_Ledger
     */
    protected $object;
    protected function getDataSet()
    {
        $dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet();
        $dataSet->addTable('jrn', dirname(__FILE__)."/jrn.csv");
        return $dataSet;
    }
    /**
     * @brief Get an operation
     * @global type $g_connection
     * @return int
     */
    private function get_jrn_id($p_ledger='ODS')
    {
        global $g_connection;
        $jr_id=$g_connection->get_value("select max(jr_id) from jrn join jrn_def "
                . "on (jrn_def_id=jr_def_id) "
                . " where "
                . " jrn_def_type=$1",[$p_ledger]);
        return $jr_id;
    }
    /**
     * Return 1 if operation does exist otherwise zero
     * @global type $g_connection
     * @param type $p_jr_id
     * @return type
     */
    private function exist_operation($p_jr_id)
    {
        global $g_connection;
        $jr_id=$g_connection->get_value("select count(*) from jrn where jr_id=$1",[$p_jr_id]);
        return $jr_id;
    }
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp():void
    {
        require DIRTEST.'/global.php';
        $this->object=new Acc_Ledger($g_connection,0);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown():void
    {
        
    }

    /**
     * @backupGlobals enabled
     */
    static function tearDownAfterClass():void
    {
        global $g_connection;
        $g_connection->exec_sql("update jrn_def set jrn_def_quantity=0 where jrn_def_id=4");
    }
    /**
     * covers ::existing_vat
     * covers ::get_type
     */
    public function testExisting_vat()
    {
        $this->object->set_ledger_id(2);
        $this->assertEquals($this->object->get_type(),'VEN',"Sales ledger");
        $a_vat=$this->object->existing_vat();
        $this->assertEquals(4,count($a_vat));
        
        $this->object->set_ledger_id(3);
        $this->assertEquals($this->object->get_type(),'ACH',"Purchases ledger");
        $a_vat=$this->object->existing_vat();
        $this->assertEquals(6,count($a_vat));
    }
    /**
     * @covers Acc_Ledger::get_last_pj
     */
    public function testGet_last_pj()
    {   
        // reset sequence
        global $g_connection;
        $g_connection->exec_sql("alter sequence  s_jrn_pj2 restart with 43");
        $this->object->id=2;
        $sPj=$this->object->get_last_pj(2);
        $this->assertEquals(42,$sPj);
        $this->object->id=0;
        
        try {
            $this->object->get_last_pj();
            $this->assertTrue(FALSE,"get_last_pj exception non lancée");
        } catch (Exception $ex) {
            $this->assertTrue(TRUE,"Exception si id =0");
        }
        
    }

    /**
     * @covers Acc_Ledger::get_type
     */
    public function testGet_type()
    {
        $this->object->id=0;
        $type=$this->object->get_type();
        $this->assertEquals('GL',$type);
        
         $this->object->id=1;
        $type=$this->object->get_type();
        $this->assertEquals('FIN',$type);
        
         $this->object->id=2;
        $type=$this->object->get_type();
        $this->assertEquals('VEN',$type);
        
         $this->object->id=3;
        $type=$this->object->get_type();
        $this->assertEquals('ACH',$type);
        
        $this->object->id=999;
        $type=$this->object->get_type();
        $this->assertEquals(null,$type);
    }


    public function delete_ledger()
    {
        global $g_connection;
        // 0. clean 
        $g_connection->exec_sql("delete from jrn_def where jrn_def_name=$1",["UNITTEST"]);
        
        // a . create a ledger and delete it
        $array=["p_jrn_def_name"=>"UNITTEST","p_ech_lib"=>"","p_jrn_deb_max_line"=>7,'p_jrn_type'=>'ODS','jrn_def_pj_pref'=>'TT/','min_row'=>5,'p_description'=>'LEDGER UNIT TEST','jrn_def_negative_amount'=>0,'jrn_def_negative_warning'=>'Warning'];
        $this->object->save_new($array);
        
        // Get it 
        $last_ledger_inserted=$g_connection->get_value("select max(jrn_def_id) from jrn_def");
        $name=$g_connection->get_value("select jrn_def_name from jrn_def where jrn_def_id=$1",[$last_ledger_inserted]);
        $this->assertEquals($name,'UNITTEST');
        
        // drop it
        $this->object->delete_ledger();
        $cnt=$g_connection->get_value("select count(*) from jrn_def where jrn_def_id=$1",[$last_ledger_inserted]);
        $this->assertEquals($cnt,0);
        $ok=0;
        // Try to delete a ledger which is used
        try {
            $jr_id=$g_connection->get_value("select max(jr_id) from jrn");
            $ledger_id=$g_connection->get_value("select jr_def_id from jrn where jr_id=$1",[$jr_id]);
            $ledger=new Acc_Legder($g_connection,$ledger_id);
            $ledger->delete_ledger();
        } catch (Exception $ex) {
            $ok=1;
        }
        $this->assertEquals($ok,1);
        $cnt=$g_connection->get_value("select count(*) from jrn_def where jrn_def_id=$1",[$ledger_id]);
        $this->assertEquals($cnt,1);
        
        // 0. clean 
        $g_connection->exec_sql("delete from jrn_def where jrn_def_name=$1",["UNITTEST"]);

    }
    /**
     * @covers Acc_Ledger::display_warning
     */
    public function testDisplay_warning()
    {
        $str=$this->object->display_warning(["First Line","Second Line"], "warning");
        $this->assertEquals('<p class="notice"> warning<ol class="notice"><li>First Line</li><li>Second Line</li></ol></p>',$str);
    }

    /**
     * @covers Acc_Ledger::reverse
     */
    public function testReverse()
    {
        global $g_connection;
        $this->object->jr_id=$g_connection->get_value("select max(jr_id) from jrn ");
        $this->assertLessThan($this->object->jr_id,"0","found jr_id ".$this->object->jr_id);
        $this->assertFalse(empty($this->object->jr_id),"not found jr_id ");
        
        $this->object->id=$g_connection->get_value("select jr_def_id from jrn where jr_id=$1",[$this->object->jr_id]);
        $this->assertLessThan($this->object->id,"0","found id ".$this->object->id);
        $this->assertFalse(empty($this->object->id),"not found id ");
        $date=$g_connection->get_value ("select to_char(max(p_start),'DD.MM.YYYY') from parm_periode where p_closed='f'");
        $this->object->reverse($date,'unit test'.$date);
        $check=$g_connection->get_value("select jr_id from jrn where jr_comment=$1",["unit test".$date]);
        $this->assertFalse(empty($check),"NOT REVERSED" );
        // check that the receipt number is correct
        $receipt=$g_connection->get_value("select jr_pj_number from jrn where jr_id=$1",[$check]);
        $this->assertFalse(empty($receipt)," no receipt number computed");
        $orig_receipt=$g_connection->get_value("select jr_pj_number from jrn where jr_id=$1",[$this->object->jr_id]);
        $this->assertNotEquals($receipt, $orig_receipt,"Wrong Receipt number ");
        $this->object->jr_id=$check;
        $this->object->delete();
    }

    /**
     * @covers Acc_Ledger::get_name
     */
    public function testGet_name()
    {
        $this->object->id=3;
        $name=$this->object->get_name();
        $this->assertEquals('Achat',$name);
        
        $this->object->id=0;
        $name=$this->object->get_name();
        $this->assertEquals('Grand Livre',$name);

        $this->object->id=1000;
        $name=$this->object->get_name();
        $this->assertEquals(null,$name);
    }

    /**
     * @covers Acc_Ledger::get_rowSimple
     */
    public function testGet_rowSimple()
    {
        global $g_connection;
        $last=$g_connection->get_value("select max(p_id) from parm_periode");
        $first=$g_connection->get_value("select min(p_id) from parm_periode");
        $id=$this->object->id;
        $this->object->id=2;
        $array=$this->object->get_rowSimple($first,$last);
        $this->assertTrue(is_array($array),"get_rowSimple does not return an array");
        $this->assertGreaterThan(0,count($array));
        $this->object->id=$id;
        
    }

    /**
     * @covers Acc_Ledger::guess_pj
     */
    public function testGuess_pj()
    {
        global $g_connection;
        $g_connection->exec_sql("alter sequence  s_jrn_pj2 restart with 43");
        $this->object->id=2;
        $r=$this->object->guess_pj();
        $this->assertEquals("VEN43",$r);
    }

    /**
     * @covers Acc_Ledger::get_propertie
     */
    public function testGet_propertie()
    {
        global $g_connection;
        $this->object->id=2;
        $array=$this->object->get_propertie();
        // there are 16 columns in jrn_def
        $this->assertEquals(count($array),22);
        $this->object->id=0;
        $array=$this->object->get_propertie();
        $this->assertEquals(null,$array);
    }

    /**
     * @covers Acc_Ledger::display_negative_warning
     */
    public function testDisplay_negative_warning()
    {
        global $g_connection;
        $msg="WARNING ! WARNING !";
        $acc_ledger=new Jrn_def_SQL($g_connection,2);
        $acc_ledger_old=clone $acc_ledger;
        $acc_ledger->setp("jrn_def_negative_amount",1);
        $acc_ledger->setp("jrn_def_negative_warning",$msg);
        $acc_ledger->save();
        $this->object->set_ledger_id(2);
        $result=$this->object->display_negative_warning(-1);
        $this->assertEquals($result,"");
        $result=$this->object->display_negative_warning(1);
        $this->assertEquals($result,$msg);
        
        $acc_ledger_old->save();
        $id=$this->object->get_ledger_id();
        $ok=0;
        try {
            $this->object->set_ledger_id(0);
            $result=$this->object->display_negative_warning(1);
        }        catch (Exception $e) {
            $this->assertEquals($e->getCode(),1);
            $ok=1;
        }
        $this->assertEquals($ok,1);
        $this->object->set_ledger_id($id);
        
    }
    /**
     * @covers Acc_Ledger::get_solde
     */
    public function testGet_solde()
    {
        global $g_connection;
        $ledger=new Acc_Ledger($g_connection,2);
        $max=$g_connection->get_value("select max(p_id) from parm_periode");
        $min=$g_connection->get_value("select min(p_id) from parm_periode");
        $solde=$ledger->get_solde($min,$max);
        $this->assertEquals($solde[1],5156.4400);
        $this->assertEquals($solde[0],5156.4400);
        
    }

    /**
     * @covers Acc_Ledger::select_ledger
     */
    public function testSelect_ledger()
    {
        $select_available=$this->object->select_ledger();
        $this->assertEquals(8,count($select_available->value));
    }
    /**
     * @covers Acc_Ledger::get_fiche_def
     */
    public function testGet_fiche_def()
    {
        global $g_connection;
        $ledger=new Acc_Ledger($g_connection,0);
        $this->assertEmpty($ledger->get_fiche_def());
        $ledger=new Acc_Ledger($g_connection,2);
        $this->assertEquals(2,count($ledger->get_fiche_def()));
    }

    /**
     * @covers Acc_Ledger::get_class_def
     */
    public function testGet_class_def()
    {
        global $g_connection;
        $ledger=new Acc_Ledger($g_connection,0);
        $this->assertEmpty($ledger->get_class_def());
        $ledger=new Acc_Ledger($g_connection,2);
        $this->assertEquals(1,count($ledger->get_class_def()));
    }

    /**
     * @covers Acc_Ledger::confirm
     */
    public function testConfirm()
    {
       $array=[
            array("ac"=>"COMPTA/MENUODS/ODS"),
            "pa_id"=>array(2),
            "e_date"=>"17.11.2018",
            "desc"=>"",
            "period"=>102,
            "e_pj"=>"ODS1",
            "e_pj_suggest"=>"ODS1",
            "mt"=>1572640802.992,
            "e_comm"=>"",
            "jrn_type"=>"ODS",
            "p_jrn"=>4,
            "nb_item"=>3,
            "jrn_concerned"=>"",
            "gDossier"=>25,
            "qc_0"=>"",
            "poste0"=>601,
            "ld0"=>"Achats de fournitures",
            "ck0"=>"",
            "amount0"=>100,
            "op"=>array(0),
            "amount_t0"=>100,
            "hplan"=>array(array(-1)),
            "val"=>array(array(100)),
            "poste1"=>"4511",
            "ld1"=>"TVA à payer 21%",
            "ck1"=>"",
            "qc_1"=>"",
            "amount1"=>21,
            "qc_2"=>"FOURNI2",
            "ld2"=>"fournisseur 3",
            "amount2"=>121,
            "opd_name"=>"",
            "od_description"=>"",
            "reverse_date"=>"",
            "ext_label"=>"",
            "jr_optype"=>"NOR",
            "p_currency_code"=>0,
            "p_currency_rate" => 1,
            "save"=>"Confirmer"
        ];
       $this->object->set_ledger_id(4);
       $this->object->with_concerned=FALSE;
       $ret=$this->object->confirm($array);
       $this->assertStringContainsString('td class="num">121,00<INPUT TYPE="hidden" id="amount2" NAME="amount2" VALUE="121"></td></tr><tr  class="highlight"><td  ></td><td  >Totaux</td><td  class="num">121.00</td><td  class="num">121.00</td></tr></table><input type="button" class="button" value="verifie Imputation Analytique" onClick="verify_ca(\'\');">',$ret);
               
    }

    /**
     * @covers Acc_Ledger::get_min_row
     */
    public function testGet_min_row()
    {
       global $g_connection;
        $a_ledger=$g_connection->get_array("select jrn_def_id,jrn_deb_max_line from jrn_def");
        $nb_ledger=(empty($a_ledger))?0:count($a_ledger);
        $this->assertGreaterThan (0,$nb_ledger);
        for ($i=0;$i<$nb_ledger;$i++) {
            $ledger=new Acc_Ledger($g_connection,$a_ledger[$i]['jrn_def_id']);
            $cnt=$ledger->get_min_row();
            $this->assertEquals($a_ledger[$i]['jrn_deb_max_line'],$cnt);
        }
    }

    /**
     * @testdox Check if the column jrn_def_quantity is properly saved
     * @return void
     * @throws Exception
     */
    function testQuantity()
    {
        global $g_connection;
        $ledger=new Acc_Ledger($g_connection,4);
        $ledger->set_quantity(1);
        $ledger->update();
        $ledger->load();
        $this->assertEquals(1,$ledger->has_quantity(),'jrn_def_quantity is not saved');
        $ledger->set_quantity(0);
        $ledger->update();
        $ledger->load();
        $this->assertEquals(0,$ledger->has_quantity(),'jrn_def_quantity is not saved');

    }

    /**
     * @brief create a financial, update it
     * @return void
     */
    function testManageFinancial()
    {
        global $g_connection;
        // create a card financial
        $new_fiche = new Fiche($g_connection);
        $aProperty = array('av_text1' => 'Nom', 'av_text23' => 'BK1');

        $new_fiche->insert(3, $aProperty);

        //- create a new financial ledger
        $array=["jrn_def_id"=>-1,
            "p_jrn_name"=>"PhpUnit Fin",
            "p_ech_lib"=>"",
            "p_jrn_type"=>"FIN",
            "bank"=>$new_fiche->get_quick_code(),
            "negative_amount"=>0,
            "negative_warning"=>"",
            "p_jrn_quantity"=>0,
            "min_row"=>"5",
            "p_description"=>"",
            "jrn_def_pj_pref"=>"PU" ,
            "FIN_FICHE_DEF"=>[2,3,4],
            "defaultCurrency"=>0,
            "p_jrn_deb_max_line" => 10,
            "p_jrn_padding" => 10
        ];
        // - update it
        $acc_ledger=new Acc_Ledger($g_connection,-1);
        $acc_ledger->save_new($array);
        $this->assertTrue($acc_ledger->id > 0 ,'ledger not created' );
        $update=array (
            "p_jrn" => $acc_ledger->id,
            "sa" => "detail",
            "p_jrn_deb_max_line" => 10,
            "p_ech_lib" => "echeance",
            "p_jrn_type" => "FIN",
            "p_jrn_name"=>"PhpUnit Fin2",
            "bank"=>$new_fiche->get_quick_code(),
            "min_row" => 5,
            "p_description" => "",
            "jrn_def_pj_pref" => "A",
            "jrn_def_pj_seq" => 0,
            "jrn_enable" => 0,
            "FIN_FICHEDEB" =>array(2,3,4),
            "defaultCurrency"=>0,
            "p_jrn_padding" => 10
        );

       $acc_ledger->update($update);
       $this->assertTrue($acc_ledger->jrn_def_name=="PhpUnit Fin2"," cannot change ledger name");
       $this->assertTrue($acc_ledger->jrn_def_bank==$new_fiche->id," set bank incorrect");

       $acc_ledger->delete_ledger();
       $new_fiche->delete();

    }
    /**
     * @covers Acc_Ledger::input
     */
    public function testInput()
    {
      
      try {
          global $g_connection;
          $ledger=new Acc_Ledger($g_connection,4);
          put_global([["key"=>"ac","value"=>"ODS"]]);
          $str=$ledger->input(null,0);
          //---------------------------------------------------------------------------
          // Save it first , and test after
          // $file=fopen(__DIR__."/file/acc_ledgerTest.testInput.txt","w+");
          // fwrite($file, $str);
          // fclose($file);
          //---------------------------------------------------------------------------
          $str_fileresult=tempnam("/tmp","acc_ledger_input.txt");
          $fileresult=fopen($str_fileresult,"w+");
          fwrite($fileresult,$str);
//          fclose($fileresult);
          
          $this->assertStringContainsString(' onChange="format_number(this);checkTotalDirect()" ',
                  $str);
      } catch (Exception $ex) {
          $this->assertTrue(False);
          throw $ex;
      }
    }

    /**
     * @covers Acc_Ledger::is_closed
     */
    public function testIs_closed()
    {
        global $g_connection;
        $ledger=new Acc_Ledger($g_connection,4);
        $this->assertEquals(1,$ledger->is_closed(99));
        $this->assertEquals(0,$ledger->is_closed(101));
    }

    /**
     * @covers Acc_Ledger::verify
     */
    public function testVerify_Ledger()
    {
       global $g_connection;
       $ledger=new Acc_Ledger($g_connection,4);
       $array=[
            "p_jrn"=>"15",
            "p_jrn_deb_max_line"=>5,
            "p_jrn_name"=>"New ledger",
            "p_jrn_type"=>"ODS"
            ];
        try {
       //-----------------------------------------------
       // Must succeed
       //-----------------------------------------------

          $ledger->verify_ledger($array);
           // succeeds if negative amount  1
           $array["negative_amount"]=1;
           $ledger->verify_ledger($array);
           $this->assertTrue(true, "an unexpected exception");

        } catch (\Exception $e) {
            echo $e->getMessage();
           $this->assertTrue(false, "an unexpected exception");
        }


    }

    /**
     * @testdox verify_ledger , Wrong id
     * @return void
     */
    public function testVerifyWrongId()
    {
        global $g_connection;
        $ledger=new Acc_Ledger($g_connection,4);
        $this->expectException(\Exception::class);
        $array=[
            "p_jrn"=>"15",
            "p_jrn_deb_max_line"=>5,
            "p_jrn_name"=>"New ledger",
            "p_jrn_type"=>"ODS"
        ];

        $array["p_jrn"]="a";
        $ledger->verify_ledger($array);
    }

    /**
     * @testdox verify_ledger , Negative amount
     * @return void
     */
    public function testVerifyWrongNegativeAmount()
    {
        global $g_connection;
        $ledger=new Acc_Ledger($g_connection,4);
        $this->expectException(\Exception::class);
        $array=[
            "p_jrn"=>"15",
            "p_jrn_deb_max_line"=>5,
            "p_jrn_name"=>"New ledger",
            "p_jrn_type"=>"ODS"
        ];

        $array["negative_amount"]=2;
        $ledger->verify_ledger($array);
    }

    /**
     * @covers Acc_Ledger::compute_internal_code
     */
    public function testCompute_internal_code()
    {
        $this->object->set_ledger_id(4);
        $str=$this->object->compute_internal_code(10);
        $this->assertFalse(empty($str));
    }

    /**
     * @covers Acc_Ledger::save Acc_Ledger::delete
     */
    public function testSave()
    {
        $array=[
                "pa_id"=>array(2),
                "e_date"=>"01.09.2018",
                "desc"=>"test",
                "period"=>100,
                "e_pj"=>"ODS1",
                "e_pj_suggest"=>"ODS1",
                "mt"=>1572642748.22,
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
                "hplan"=>array( array(-1)),
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
                ];
        global $g_connection;
        $ledger=new Acc_Ledger($g_connection,4);
        $g_connection->exec_sql("delete from jrn where jr_mt=$1",[$array["mt"]]);
        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",[$array['mt']]);
        $this->assertEquals(0,$cnt);
        $ledger->save($array);
        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",[$array['mt']]);
        $this->assertEquals(1,$cnt);
        
        $ledger->delete();
        $g_connection->exec_sql("alter sequence  s_jrn_pj4 restart with 43");

        $cnt=$g_connection->get_value("select count(*) from jrn where jr_mt=$1",[$array['mt']]);
        $this->assertEquals(0,$cnt);
        
    }

    /**
     * @covers Acc_Ledger::next_number
     */
    public function testNext_number()
    {
       global $g_connection;
       $this->assertEquals(2,Acc_Ledger::next_number($g_connection, "ODS"));
               
    }

    /**
     * @covers Acc_Ledger::get_first
     */
    public function testGet_first()
    {
        global $g_connection;
        $ledger=new Acc_Ledger($g_connection,4);
        $first_ledger=$ledger->get_first("ODS");
        $this->assertEquals(4,$first_ledger["jrn_def_id"]);
    }

    /**
     * @covers Acc_Ledger::get_all_fiche_def
     * @todo
     * $this->object->set_ledger_id(3);
        $this->assertEquals($this->object->is_enable(),TRUE);   Implement testGet_all_fiche_def().
     */
    public function testGet_all_fiche_def()
    {
         global $g_connection;
        $ledger=new Acc_Ledger($g_connection,4);
        $this->assertEquals("3,2,4,5",$ledger->get_all_fiche_def());
    }

    /**
     * @covers Acc_Ledger::get_last_date
     */
    public function testGet_last_date()
    {
      global $g_connection;
      $this->object->set_ledger_id(2);
      $last_date=$this->object->get_last_date();
      
      $this->assertEquals($g_connection->get_value("select to_char(max(jr_date),'DD.MM.YYYY') from jrn where jr_def_id=2"),
              $last_date);
    }

    /**
     * @covers Acc_Ledger::get_id
     */
    public function testGet_id()
    {
        $this->assertEquals(3,$this->object->get_id("V000003"));
    }

   


    /**
     * @covers Acc_Ledger::array_cat
     */
    public function testArray_cat()
    {
        $array=Acc_Ledger::array_cat();
        $this->assertEquals(4,count($array));
    }

    /**
     * @covers Acc_Ledger::get_tiers
     */
    public function testGet_tiers()
    {
        global $g_connection;
        $jr_id=$this->get_jrn_id();
        $this->assertEquals($this->object->get_tiers("ODS", $jr_id)," ");

        $jr_id=$this->get_jrn_id("ACH");
        $this->assertTrue($this->object->get_tiers("ACH",$jr_id)!=' '); 

        $jr_id=$this->get_jrn_id("FIN");
        $this->assertTrue($this->object->get_tiers('FIN',$jr_id)!=' ');

        $jr_id=$this->get_jrn_id("VEN");
        $this->assertTrue($this->object->get_tiers("VEN",$jr_id)!=' ');
        
    }
    /**
     * @covers Acc_Ledger::get_tiers_id
     */
    public function testGet_tiers_id()
    {
        global $g_connection;
        $jr_id=$this->get_jrn_id();
        $this->assertEquals($this->object->get_tiers_id("ODS", $jr_id),0);

        $jr_id=$this->get_jrn_id("ACH");
        $this->assertGreaterThan(0,$this->object->get_tiers_id("ACH",$jr_id));

        $jr_id=$this->get_jrn_id("FIN");
        $this->assertGreaterThan(0,$this->object->get_tiers_id("FIN",$jr_id));

        $jr_id=$this->get_jrn_id("VEN");
        $this->assertGreaterThan(0,$this->object->get_tiers_id("VEN",$jr_id));
        
    }
   
    /**
     * @covers Acc_Ledger::verify_ledger
     */
    public function testVerify_Operation()
    {
        global $g_connection;
        $array = [ "p_jrn" => 4,
                    "p_jrn_predef" => 4,
                    "action" => "use_opd",
                    "jrn_type" => "ODS",
                    "e_date" => "17.11.2018",
                    "e_pj" => "ODS1",
                    "e_pj_suggest" => "ODS1",
                    "desc" => "",
                    "nb_item" => 5,
                    "qc_0" => "",
                    "poste0" => "601",
                    "ld0" => "Achats de fournitures",
                    "amount0" => 100,
                    "ck0" => "",
                    "qc_1" => "",
                    "poste1" => "4511",
                    "ld1" => "TVA à payer 21%",
                    "amount1" => 21,
                    "ck1" => "",
                    "qc_2" => "FOURNI2",
                    "poste2" => "",
                    "ld2" => "fournisseur 3",
                    "amount2" => 121,
                    "qc_3" => "",
                    "poste3" => "",
                    "ld3" => "",
                    "amount3" => "",
                    "qc_4" => "",
                    "poste4" => "",
                    "ld4" => "",
                    "amount4" =>"", 
                    "jrn_concerned" => "",
                    "summary" => "Sauvez",
                    "p_currency_rate"=>1,
                    "p_currency_code"=>0,
            ];
        $ledger=new Acc_Ledger($g_connection,4);
        
        // This test must succeed
        try {
            $ledger->verify_operation($array);
            $this->assertTrue(TRUE);
        } catch(Exception $e)
        {
            var_dump($e->getMessage());
            var_dump($e->getTraceAsString());
            $this->assertTrue(FALSE);
            throw $e;
        }
        
        // wrong date must fail
        
        $wrong_array=$array;
        $wrong_array["e_date"]="aaa";
        try {
            $ledger->verify_operation($wrong_array);
            $this->assertTrue(FALSE);
        } catch(Exception $e)
        {
            $this->assertEquals(2,$e->getCode());
        }
        
        // periode closed must fail
        $wrong_array=$array;
        $wrong_array["e_date"]="01.01.2110";
        try {
            $ledger->verify_operation($wrong_array);
            $this->assertTrue(FALSE);
        } catch(Exception $e)
        {
            $this->assertEquals(2,$e->getCode());
        }
        // periode closed must fail
        $wrong_array=$array;
        $wrong_array["e_date"]="01.07.2018";
        try {
            $ledger->verify_operation($wrong_array);
            $this->assertTrue(FALSE);
        } catch(Exception $e)
        {
            $this->assertEquals(6,$e->getCode());
        }

    }


    /**
     * @covers Acc_Ledger::input_paid
     */
    public function testInput_paid()
    {
        $r=$this->object->input_paid(0);
        $this->assertStringStartsWith('<div id="payment">',$r);
        $this->assertStringEndsWith('</div>',$r);
    }

    /**
     * @covers Acc_Ledger::input_new
     */
    public function testInput_new()
    {
        global $g_connection;
        put_global([["key"=>"ac","value"=>"ODS"]]);
        $check_fichedef=$g_connection->get_value("select  count(*) from fiche_def");
        $this->assertEquals(7, $check_fichedef,"too many fiche_def");
        ob_start();
        echo  \Noalyss\Facility::page_start();

        $this->object->set_ledger_id(4);
        $this->object->input_new();
        $result=ob_get_contents();
        ob_end_clean();
        \Noalyss\Facility::save_file(__DIR__."/file", "acc_ledger-input_new.html", $result);
        $size=filesize(__DIR__."/file/acc_ledger-input_new.html");
        $this->assertTrue($size == 16075  ," output input_new is $size not what it is expected");

    }

    /**
     * @covers Acc_Ledger::save_new
     */
    public function testODSSave_new()
    {
        global $g_connection;
        $ledger=new Acc_Ledger($g_connection,-1);
        $array=["p_jrn_name"=>"UNITTEST",
                "p_ech_lib"=>"",
                "p_jrn_deb_max_line"=>7,
                'p_jrn_class_deb'=>'4*',
                'p_jrn_type'=>'ODS',
                'jrn_def_pj_pref'=>'TT/',
                'min_row'=>5,
                'p_description'=>'LEDGER UNIT TEST',
                'negative_amount'=>0,
                "p_jrn_padding" => 10,
                'negative_warning'=>'Warning'];
        
         // clean ledger if exists
        $g_connection->exec_sql("delete from jrn_def where jrn_def_name=$1",[$array['p_jrn_name']]);
        
        $ledger->save_new($array);
        $jrn_def_id=$g_connection->get_value("select jrn_def_id from jrn_def where jrn_def_name=$1",
                [$array['p_jrn_name']]);
        $this->assertLessThan($jrn_def_id,0);
        $this->assertEquals($jrn_def_id,$ledger->jrn_def_id);
        $ledger=new Acc_Ledger($g_connection,$jrn_def_id);
        $ledger->delete_ledger();
        $jrn_def_id=$g_connection->get_value("select jrn_def_id from jrn_def where jrn_def_name=$1",
                [$array['p_jrn_name']]);
        $this->assertEquals($jrn_def_id,"");
    }
    /**
     * @testdox ComputerLedgerCode up to 2000
     * @covers Acc_Ledger::save_new , 
     * @global type $g_connection
     */
    public function testComputeLedgerCode()
    {
        global $g_connection;
         $array=["p_jrn_name"=>"UNITTEST",
                "p_ech_lib"=>"",
                "p_jrn_deb_max_line"=>7,
                'p_jrn_class_deb'=>'4*',
                'p_jrn_type'=>'ODS',
                'jrn_def_pj_pref'=>'TT/',
                'min_row'=>5,
                'p_description'=>'LEDGER UNIT TEST',
                'negative_amount'=>0,
             "p_jrn_padding" => 10,
                'negative_warning'=>'Warning'];
         
        $g_connection->exec_sql("delete from jrn_def where jrn_def_description=$1",['LEDGER UNIT TEST']);
        for ($i=0;$i<2000;$i++) {
            $array['p_jrn_name']='UNITEST'.str_pad($i,5,"0",STR_PAD_LEFT); 
            $ledger=new Acc_Ledger($g_connection,-1);
            $ledger->save_new($array);
            $this->assertEquals($ledger->jrn_def_code,strtoupper("O".str_pad(base_convert($i+2, 10, 36),2,0,STR_PAD_LEFT)));
         }
        
         // DELETE LEDGER with description = 'LEDGER UNIT TEST'
         $g_connection->exec_sql("delete from jrn_def where jrn_def_description=$1",['LEDGER UNIT TEST']);
    }
    /**
     * @covers Acc_Ledger::delete_ledger
     */
    public function testDelete_ledger()
    {
        global $g_connection;
        $array=["p_jrn_name"=>"UNITTEST",
                "p_ech_lib"=>"",
                "p_jrn_deb_max_line"=>7,
                'p_jrn_class_deb'=>'4*',
                'p_jrn_type'=>'ODS',
                'jrn_def_pj_pref'=>'TT/',
                'min_row'=>5,
                'p_description'=>'LEDGER UNIT TEST',
                'negative_amount'=>0,
                 "p_jrn_padding" => 10,
                'negative_warning'=>'Warning'];
        // clean ledger if exists
        $g_connection->exec_sql("delete from jrn_def where jrn_def_name=$1",[$array['p_jrn_name']]);
        
        // Recreate it
         $ledger=new Acc_Ledger($g_connection,-1);
        $ledger->save_new($array);
        $jrn_def_id=$g_connection->get_value("select jrn_def_id from jrn_def where jrn_def_name=$1",[$array['p_jrn_name']]);
        $this->assertLessThan($jrn_def_id,0);
        $ledger=new Acc_Ledger($g_connection,$jrn_def_id);
        $ledger->delete_ledger();
        $jrn_def_id=$g_connection->get_value("select jrn_def_id from jrn_def where jrn_def_name=$1",[$array['p_jrn_name']]);
        $this->assertEquals($jrn_def_id,"");
    }

    /**
     * @covers Acc_Ledger::get_operation_date
     */
    public function testGet_operation_date()
    {
        $res=$this->object->get_operation_date('01.01.2010','VEN','>');
        $this->assertGreaterThan(0,count($res),'Failed VEN');
        $res=$this->object->get_operation_date('01.01.2010','ACH','>');
        $this->assertGreaterThan(0,count($res),'Failed ACH ');
        $res=$this->object->get_operation_date('01.01.2000','VEN','<');
        $this->assertEquals(count($res),0,'Failed VEN before ');
        $res=$this->object->get_operation_date('01.01.2000','ACH','<');
        $this->assertEquals(count($res),0,'Failed ACH before');

    }

    /**
     * @covers Acc_Ledger::get_supplier_now
     */
    public function testGet_supplier_now()
    {
       $this->assertTrue(is_array($this->object->get_supplier_now()));
    }

    /**
     * @covers Acc_Ledger::get_supplier_late
     */
    public function testGet_supplier_late()
    {
       $this->assertTrue(is_array($this->object->get_supplier_late()));
    }

    /**
     * @covers Acc_Ledger::get_customer_now
     */
    public function testGet_customer_now()
    {
         $this->assertTrue(is_array($this->object->get_customer_now()));
    }

    /** 
     * @covers Acc_Ledger::get_customer_late
     */
    public function testGet_customer_late()
    {
        $this->assertTrue(is_array($this->object->get_customer_late()));
    }
    /**
     * @covers Acc_Ledger::set_ledger_id 
     * @covers Acc_Ledger::is_enable
     */
    public function testIs_Enable()
    {
        $this->object->set_ledger_id(83);
        $this->assertEquals($this->object->is_enable(),0);
        
        $this->object->set_ledger_id(4);
        $this->assertEquals($this->object->is_enable(),1);
        $this->object->set_ledger_id(2);
        $this->assertEquals($this->object->is_enable(),1);
        $this->object->set_ledger_id(3);
        $this->assertEquals($this->object->is_enable(),1);

    }

    /**
     * @testdox convert from FollowUp : Description is asked
     * @covers Acc_Ledger::convert_from_follow
     * @return void
     * @throws Exception
     */
    public function testConvert_from_follow_copy1()
    {
        $action_gestion_id=1;

        $result=$this->object-> convert_from_follow($action_gestion_id,1);
        $this->assertTrue($result['jrn_note_input']=='Test',print_r($result,true));
    }
    /**
     * @testdox convert from FollowUp : Description is not asked
     * @covers Acc_Ledger::convert_from_follow
     * @return void
     * @throws Exception
     */
    public function testConvert_from_follow_copy2()
    {
        $action_gestion_id=1;

        $result=$this->object-> convert_from_follow($action_gestion_id,0);
        $this->assertTrue(! isset ($result['jrn_note_input']),print_r($result,true));
    }
    /**
     *  @testdox convert from FollowUp : Description is asked and there is no description available
     * @covers Acc_Ledger::convert_from_follow
     * @return void
     * @throws Exception
     */
    public function testConvert_from_follow_copy3()
    {
        $action_gestion_id=15;

        $result=$this->object-> convert_from_follow($action_gestion_id,1);
        $this->assertTrue(! isset ($result['jrn_note_input']),print_r($result,true));
    }
}
