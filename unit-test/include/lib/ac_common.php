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
     */
    function testSQL_filter_per()
    {
        global $g_connection;

        $result="jr_tech_per in (select p_id from parm_periode  where ".
                "p_start >= to_date('01.07.2018','DD.MM.YYYY') and p_end <= to_date('31.08.2018','DD.MM.YYYY'))";
        $this->assertEquals(
                $result, trim(sql_filter_per($g_connection, "01.07.2018", "31.08.2018", "date", "jr_tech_per")));

        $result="j_tech_per in (select p_id from parm_periode  where ".
                "p_start >= to_date('01.07.2018','DD.MM.YYYY') and p_end <= to_date('31.08.2018','DD.MM.YYYY'))";
        $this->assertEquals(
                $result, trim(sql_filter_per($g_connection, "01.07.2018", "31.08.2018", "date", "j_tech_per")));

        $result="j_tech_per = (select p_id from parm_periode  where ".
                " p_start = to_date('01.07.2018','DD.MM.YYYY'))";
        $this->assertEquals(
                $result, trim(sql_filter_per($g_connection, "01.07.2018", "01.07.2018", "date", "j_tech_per")));

        $result="j_tech_per in (select p_id from parm_periode  where ".
                "p_start >= to_date('01.07.2018','DD.MM.YYYY') and p_end <= to_date('31.08.2018','DD.MM.YYYY'))";
        $this->assertEquals(
                $result, trim(sql_filter_per($g_connection, 98, 99, "p_id", "j_tech_per")));
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
}
