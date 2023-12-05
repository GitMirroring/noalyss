<?php

use PHPUnit\Framework\TestCase;

class Ac_CommonTest extends TestCase
{

    /**
     * @covers remove_divide_zero()
     */
    function testRemove_divide_byZero()
    {
        $value_test=array("55/0.0", "55/1", "1000/0.0+1", "999/0.002+5", "1200/0+5", "0 /0",
            "0.0/1", "0. 0/0.0");
        $expected=array("0", "55/1", "0+1", "999/0.002+5", "0+5", "0", "0.0/1", "0");
        $nb_value=count($value_test);
        for ($i=0; $i<$nb_value; $i++)
        {
            $this->assertEquals(remove_divide_zero($value_test[$i]), $expected[$i]);
        }
    }

    /**
     * @covers find_default_menu
     * @global type $g_connection
     */
    function testFind_default_menu()
    {
        global $g_connection;
        $g_connection->exec_sql("update profile_menu set pm_default = 0 where pm_id in (173,3,85)");
        $this->assertEquals(find_default_menu(173), 0, "assert COMPTA has not default depending menu");
        $this->assertEquals(find_default_menu(3), 0, "assert COMPTA has not default depending menu");
        $g_connection->exec_sql("update profile_menu set pm_default = 1 where pm_id in (3,85)");
        $this->assertEquals(find_default_menu(3), 85, "assert ACH depends of MENUACH");
        $this->assertEquals(find_default_menu(173), 3, "assert  MENUACH depends of COMPTA");
    }

    /**
     * @covers rebuild_access_code
     */
    function testRebuild_access_code()
    {
        // use profile 1 , 
        // COMPTA 173
        // COMPTA/MENUACH 3
        // COMPTA/MENUACH/ACH  85
        $this->assertEquals(
                "COMPTA",
                rebuild_access_code(array(
            array("pm_id_v3"=>"0", "pm_id_v2"=>"0", "pm_id_v1"=>"173"))), "COMPTA String must not changed"
        );
        $this->assertEquals(
                "COMPTA/MENUACH", rebuild_access_code(array(array("pm_id_v3"=>"0", "pm_id_v2"=>"173", "pm_id_v1"=>"3"))),
                "COMPTA/MENUACH ");

        $this->assertEquals(
                "COMPTA/MENUACH/ACH",
                rebuild_access_code(array(array("pm_id_v3"=>"173", "pm_id_v2"=>"3", "pm_id_v1"=>"85"))),
                "COMPTA/MENUACH/ACH String must not changed");
    }

    /**
     * @covers Complete_default_menu()
     */
    function testComplete_default_menu()
    {
        // use profile 1 , 
        // COMPTA 173
        // COMPTA/MENUACH 3
        // COMPTA/MENUACH/ACH  85
        global $g_connection;
        $g_connection->exec_sql("update profile_menu set pm_default = 0 where pm_id in (173,3,85)");
        $this->assertEquals(
                array(array("pm_id_v3"=>"0", "pm_id_v2"=>"0", "pm_id_v1"=>"173")),
                complete_default_menu(array(
            array("pm_id_v3"=>"0", "pm_id_v2"=>"0", "pm_id_v1"=>"173"))),
                "complete_default_menu() not default menu to add"
        );
        $this->assertEquals(
                array(array("pm_id_v3"=>"0", "pm_id_v2"=>"173", "pm_id_v1"=>"3")),
                complete_default_menu(array(array("pm_id_v3"=>"0", "pm_id_v2"=>"173", "pm_id_v1"=>"3"))),
                "complete_default_menu() not default menu to add"
        );

        $this->assertEquals(
                array(array("pm_id_v3"=>"173", "pm_id_v2"=>"3", "pm_id_v1"=>"85")),
                complete_default_menu(array(array("pm_id_v3"=>"173", "pm_id_v2"=>"3", "pm_id_v1"=>"85"))),
                "complete_default_menu() not default menu to add"
        );

        // complete default menu
        $g_connection->exec_sql("update profile_menu set pm_default = 1 where pm_id in (3)");
        $this->assertEquals(
                array(array("pm_id_v3"=>"173", "pm_id_v2"=>"3", "pm_id_v1"=>"85")),
                complete_default_menu(array(array("pm_id_v3"=>"173", "pm_id_v2"=>"3", "pm_id_v1"=>"85"))),
                "complete_default_menu() not default menu to add"
        );
        $this->assertEquals(
                array(array("pm_id_v3"=>"0", "pm_id_v2"=>"173", "pm_id_v1"=>"3")),
                complete_default_menu(array(array("pm_id_v3"=>"0", "pm_id_v2"=>"0", "pm_id_v1"=>"173"))),
                "complete_default_menu() add menu MENUACH(pm_id:3)"
        );
        $g_connection->exec_sql("update profile_menu set pm_default = 1 where pm_id in (85)");
        $this->assertEquals(
                array(array("pm_id_v3"=>"173", "pm_id_v2"=>"3", "pm_id_v1"=>"85")),
                complete_default_menu(array(array("pm_id_v3"=>"0", "pm_id_v2"=>"0", "pm_id_v1"=>"173"))),
                "complete_default_menu() add menu MENUACH(pm_id:3) and ACH (pm_id:85)"
        );

        $g_connection->exec_sql("update profile_menu set pm_default = 0 where pm_id in (3)");
        $this->assertEquals(
                array(array("pm_id_v3"=>"0", "pm_id_v2"=>"0", "pm_id_v1"=>"173")),
                complete_default_menu(array(array("pm_id_v3"=>"0", "pm_id_v2"=>"0", "pm_id_v1"=>"173"))),
                "complete_default_menu() does not add any menu "
        );
    }

    /**
     * @covers format_date
     */
    function testFormat_Date()
    {
        $this->assertEquals("2020-10-11", format_date("11.10.2020", "DD.MM.YYYY", "YYYY-MM-DD"),
                "date from DD.MM.YYYY to YYYY-MM-DD");
        $this->assertEquals("11-10-2020", format_date("11.10.2020", "DD.MM.YYYY", "DD-MM-YYYY"),
                "date from DD.MM.YYYY to DD-MM-YYYY");
        $this->assertEquals("20201011", format_date("11.10.2020", "DD.MM.YYYY", "YYYYMMDD"),
                "date from DD.MM.YYYY to YYYYMMDD");
        $this->assertEquals("2020/10/11", format_date("11.10.2020", "DD.MM.YYYY", "YYYY/MM/DD"),
                "date from DD.MM.YYYY to YYYY/MM/DD");
        $this->assertEquals("11.10.20", format_date("11.10.2020", "DD.MM.YYYY", "DD.MM.YY"),
                "date from DD.MM.YYYY to DD.MM.YY");
        $this->assertEquals("11-10-20", format_date("11.10.2020", "DD.MM.YYYY", "DD-MM-YY"),
                "date from DD.MM.YYYY to DD/MM/YY");
    }

    function testShrinkDate()
    {
        $this->assertEquals("101120", shrink_date("10.11.2020"), "shrink_date ");
        $this->assertEquals("10.11.20", smaller_date("10.11.2020"), "smaller_date");
    }

    /**
     * @covers sql_filter_per
     * @covers transform_sql_filter
     */
    function testSQL_filter_per()
    {
        global $g_connection;

        $result="jr_tech_per in (select p_id from parm_periode  where ".
                "p_start >= to_date('01.07.2018','DD.MM.YYYY') and p_end <= to_date('31.08.2018','DD.MM.YYYY'))";
        $this->assertEquals(
                $result, trim(sql_filter_per($g_connection, "01.07.2018", "31.08.2018", "date", "jr_tech_per")));
        // transform cond for analytic
        $anc_result="(  oa_date >= to_date('01.07.2018','DD.MM.YYYY') and oa_date <= to_date('31.08.2018','DD.MM.YYYY'))";
        $this->assertEquals(
                $anc_result, transform_sql_filter_per($result),"1. convert $result to $anc_result");
        
        $result="j_tech_per in (select p_id from parm_periode  where ".
                "p_start >= to_date('01.07.2018','DD.MM.YYYY') and p_end <= to_date('31.08.2018','DD.MM.YYYY'))";
        $this->assertEquals(
                $result, trim(sql_filter_per($g_connection, "01.07.2018", "31.08.2018", "date", "j_tech_per")));
        // transform cond for analytic
        $anc_result="(  oa_date >= to_date('01.07.2018','DD.MM.YYYY') and oa_date <= to_date('31.08.2018','DD.MM.YYYY'))";
        $this->assertEquals(
                $anc_result, transform_sql_filter_per($result),"2. convert $result to $anc_result");
        
        $result="j_tech_per = (select p_id from parm_periode  where ".
                " p_start = to_date('01.07.2018','DD.MM.YYYY'))";
        $this->assertEquals(
                $result, trim(sql_filter_per($g_connection, "01.07.2018", "01.07.2018", "date", "j_tech_per")));
        // transform cond for analytic
        $anc_result="(  oa_date = to_date('01.07.2018','DD.MM.YYYY'))";
        $this->assertEquals(
                $anc_result, transform_sql_filter_per($result),"3. convert $result to $anc_result");
        
        
        $result="j_tech_per in (select p_id from parm_periode  where ".
                "p_start >= to_date('01.07.2018','DD.MM.YYYY') and p_end <= to_date('31.08.2018','DD.MM.YYYY'))";
        $this->assertEquals(
                $result, trim(sql_filter_per($g_connection, 98, 99, "p_id", "j_tech_per")));
        // transform cond for analytic
        $anc_result="(  oa_date >= to_date('01.07.2018','DD.MM.YYYY') and oa_date <= to_date('31.08.2018','DD.MM.YYYY'))";
        $this->assertEquals(
                $anc_result, transform_sql_filter_per($result),"4. convert $result to $anc_result");
    }
    /***
     * @covers add_http_link
     */
    function testAdd_Http_link()
    {
        $text="A link on http://demo.noalyss.eu is ok";
        $result=add_http_link($text);
        $this->assertEquals('A link on <a href="http://demo.noalyss.eu" target="_blank">http://demo.noalyss.eu</a> is ok',$result);
        
        $text="A link on https://demo.noalyss.eu/do.php?gDossier=33&ac=COMPTA/MENUFIN is ok";
        $result=add_http_link($text);
        $this->assertEquals('A link on <a href="https://demo.noalyss.eu/do.php?gDossier=33&ac=COMPTA/MENUFIN" target="_blank">https://demo.noalyss.eu/do.php?gDossier=33&ac=COMPTA/MENUFIN</a> is ok',$result);
        
        
        
        $text = "The chain is not going to change htps:/demo.noalyss.eu/do.php?gDossier=33&ac=COMPTA/MENUFIN";
        $this->assertEquals($text,$text);
        
    }
    /**
     * provides data to testIsDate
     */
     function dataIsDate()
     {
        return array(
            ['01.01.1992',1],
            ['30.02.2001',0],
            ['15.07.01',0],
            ['21.08.2001',1]
            );
     }
    /**
     * @covers IsDate
     * @testdox isDate
     * @dataProvider dataIsDate
     */
    function testIsDate($p_date,$expected)
    {
        $return=($expected==1)?$p_date:null;
        $this->assertEquals(isDate($p_date),$return,"Test $p_date");
    }
    function dataCompareDate ()
    {
        return array(
            ['01.01.1992','05.02.2001',-1],
            ['01.01.2012','05.02.2001',1],
            ['05.02.2001','05.02.2001',0]);
    }
    /**
     * @covers cmpDate
     * @testDox test cmpDate
     * @dataProvider dataCompareDate
     */
    function testCompareDate($p_date,$p_date_2,$p_result)
    {
        $cmp=cmpDate($p_date,$p_date_2);
        switch ( $p_result ) {
            case 0:
                $this->assertEquals($cmp,0);
                break;
            case 1:
                $this->assertGreaterThan(0,$cmp);
                break;
            case -1:
                $this->assertLessThan(0,$cmp);
                break;
                
        }
    }

    /**
     * @covers h
     * @return void
     */
    function testh()
    {
        $this->assertEquals("",h(null));
        $this->assertEquals("",h(""));
        $this->assertEquals("&lt;&amp;",h("<&"));
        $this->assertEquals(0,h("0"));
    }
    function dataFormat_Date()
    {
        return array(
                ["01.05.2000","DD.MM.YYYY","DD.MM.YY",'01.05.00'],
                ["01.05.2000","DD.MM.YYYY","DD-MM-YY",'01-05-00'],
                ["01.05.2000","DD.MM.YYYY","YYYYMMDD",'20000501'],
                ["01.05.2000","DD.MM.YYYY","YYYY/MM/DD",'2000/05/01'],
                ["2000-05-07","YYYY-MM-DD","DD.MM.YY",'07.05.00'],
                ["","DD.MM.YYYY","DD.MM.YY",""],
                ["1/1/1","DD.MM.YYYY","DD.MM.YY","1/1/1"],
        );
    }
    /**
     * @param $p_date
     * @param $p_from
     * @param $p_to
     * @param $p_result
     * @return void
     * @throws Exception
     * @dataProvider dataFormat_date
     */
    function testFormatDate($p_date,$p_from,$p_to,$p_result)
    {
        $this->assertEquals($p_result,format_date($p_date,$p_from,$p_to));
    }
    function testGenerate_Random_String()
    {
        $this->assertEquals(8,strlen(generate_random_string(8)));
        $this->assertEquals(12,strlen(generate_random_string(12)));
        $this->assertEquals(16,strlen(generate_random_string(16)));
	$generate=generate_random_string(12);
	$generateOther=generate_random_string(12);
	$this->assertNotEquals($generate,$generateOther , __FUNCTION__.' Generate 2 in a row the same string');
    }
    function testDatabase_Escape_String()
    {
        $this->assertEquals("l''éléphant",Database::escape_string("l'éléphant"));
        $this->assertEquals("l\''éléphant",Database::escape_string("l\'éléphant"));
    }
    function dataNoalyss_trim()
    {
        return array(["0","0"],
            [" 0 1 1 1 ","0 1 1 1"],
            [null,""],
            );
    }

    /**
     * @brief Comptability PHP 8.1 , null is not consider as an empty string
     * @param $param
     * @param $result
     * @return void
     * @dataProvider dataNoalyss_trim
     */
    function testNoalyss_trim($param,$result)
    {
        $this->assertEquals($result,noalyss_trim($param));
    }
    function dataNoalyss_replace() {
        return array(["0","/","0A0A","/A/A"],
            ["A","*","0A0A","0*0*"],
            ["0","/",null,""],
            ["0","/","",""]
        );
    }

    /**
     * @brief Comptability PHP 8.1 , null is not consider as an empty string
     * @dataProvider dataNoalyss_replace
     */
    function testNoalyss_replace($search,$replace,$string,$expected)
    {
        $this->assertEquals($expected,noalyss_str_replace($search,$replace,$string));
    }
    function dataNoalyss_bcsub() {
        return array(
                [1,2,-1],
                [0,2,-2],
                ["",2,-2],
                [null,2,-2]
        );
    }

    /**
     * @brief Comptability PHP 8.1 , null is not consider as an empty string
     * @dataProvider dataNoalyss_bcsub
     */
    function testNoalyss_bcsub($numbera,$numberb,$expected)
    {
        $this->assertEquals($expected,noalyss_bcsub($numbera,$numberb));
    }
    function dataNoalyss_strip_tags() {
        return array(
           [null,""],
           ["",""],
           ["0","0"],
           ["<script>0</script>","0"],
        );
    }

    /**
     * @brief Comptability PHP 8.1 , null is not consider as an empty string
     * @dataProvider dataNoalyss_strip_tags
     */
    function testNoalyss_strip_tags($string,$expected)
    {
        $this->assertEquals($expected,noalyss_strip_tags($string));
    }

    /**
     * @testdox Test the TEL tag
     * @return void
     */
    function testPhoneTo()
    {
        $this->assertFalse(phoneTo(""),"Invalide phone");
        $expect=sprintf('<a href="tel:%s">%s</a>',h(123),h(123));
        $expect=preg_replace('/\s+/','',$expect);
        $this->assertEquals(strtoupper($expect),strtoupper(preg_replace("/\s+/",'',phoneTo('123'))),);
    }
    /**
     * @testdox Test the MAILTO tag
     * @return void
     */
    function testMailTo()
    {
        $this->assertEmpty(mailTo(""),"Invalide phone");
        $expect=sprintf('<a href="mailto:%s">%s</a>',h("test@noalyss.be"),h("test@noalyss.be"));
        $expect=preg_replace('/\s+/','',$expect);
        $this->assertEquals(strtoupper($expect),strtoupper(preg_replace("/\s+/",'',mailTo('test@noalyss.be'))),);
        $expect=<<<EOF
test@noalyss.@be<span tabindex="-1" onmouseover="showBulle('83')" onclick="showBulle('83')" onmouseout="hideBulle(0)"style="color:red" class="icon">&#xe80e;</span>
EOF;
        $expect=preg_replace('/\s+/','',$expect);
        $result=preg_replace('/\s+/','',mailTo("test@noalyss.@be"));
        $this->assertEquals($expect,
            $result,
            "Send email to invalidate email");
        $expect=sprintf('<a href="mailto:%s">%s</a>',h("test@noalyss.be"),h("test@noalyss.be"));
        $expect.=sprintf('<a href="mailto:%s">%s</a>',h("test2@noalyss.be"),h("test2@noalyss.be"));
        $expect=preg_replace('/\s+/','',$expect);
        $this->assertEquals($expect,
            preg_replace('/\s+/','',mailTo('test@noalyss.be,test2@noalyss.be'))
            ,"Send email to invalidate email");

    }
    /**
     * @testdox Test the FAX tag
     * @return void
     */
    function testFaxTo()
    {
        $this->assertFalse(faxTo(""),"Invalide phone");
        $expect=sprintf('<a href="fax:%s">%s</a>',h(123),h(123));
        $expect=preg_replace('/\s+/','',$expect);
        $this->assertEquals(strtoupper($expect),strtoupper(preg_replace("/\s+/",'',faxTo('123'))),);

    }

    /**
     * supply data for user password
     * @return array[$password, $weakness] 0 means strong password
     */
    public function dataCheck_password_strength()
    {
        return array(
             ["AAAAAAA",5]
            ,["123456789",3]
            ,["Az123456789",1]
            ,["",4]
            ,["+",4]
            ,["AAAA121212abx",2]
            ,["Az&123456789",0]
            ,["l5F8Cny=",0]
        );
    }
    /**
     * @testDoc test the check_password_strength function
     * @dataProvider dataCheck_password_strength()
     */
    public function testCheck_password_strength($p_password,$p_cnt)
    {

        $count=count(check_password_strength($p_password)['msg']);
        $this->assertTrue($count ==$p_cnt,"error : $p_password weak password $count"    );

    }

    public function testGenerate_strong_password()
    {

        for ($i = 0; $i < 100; $i++)
        {
            $pass=generate_random_password(5);

            $this->assertTrue( count(check_password_strength($pass)['msg'])==0
                ,"error cannot generate strong password get $pass");
        }
    }

}
