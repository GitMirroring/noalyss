<?php

/*
 *   This file is part of NOALYSS.
 *
 *   NOALYSS is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   NOALYSS is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23
use PHPUnit\Framework\TestCase;

/**
 * @file
 * @brief New syntax for Table_Data_SQL
 */

class Currency_SQL extends \Table_Data_SQL
{
     function __construct(Database $p_cn, $p_id=-1)
    {
        $this->table="public.currency";
        $this->primary_key="id";
        /*
         * List of columns
         */
        $this->name=array(
             "id"
            , "cr_code_iso"
            ,"cr_name"
        );
        /*
         * Type of columns
         */
        $this->type=array(
            "id"=>"numeric"
            , "cr_code_iso"=>"text"
            , "cr_name"=>"text"
        );


        $this->default=array(
            "id"=>"auto"
        );

        $this->date_format="DD.MM.YYYY";
        parent::__construct($p_cn, $p_id);
    }
}
class Data_NewSQLTest extends TestCase 
{
    /**
     * @testdox create object currency_sql
     */
    function testBuildObject()
    {
        $cn=new \Database(DOSSIER);
        $currency_sql = new Currency_SQL($cn,0);
        $this->assertEquals($currency_sql->cr_code_iso,'EUR','invalid ISO Code');
    }
    /**
     * @testdox Insert , update and delete row
     */
    function testDMLObject()
    {
        /* insert */
        $cn=new \Database(DOSSIER);
        $currency_sql = new Currency_SQL($cn);
        $currency_sql->cr_code_iso = 'XIU';
        $currency_sql->cr_name = 'Currency for UNIT TEST';
        $currency_sql->insert();
        $this->assertTrue($currency_sql->id > 0,' row not created');
         /* update */
        $update_sql = new Currency_SQL($cn,$currency_sql->id);
        $this->assertTrue($update_sql->cr_code_iso=='XIU'," update can not find row");
        $update_sql->cr_name='xxxx';
        $update_sql->save();
        $check_sql=new Currency_SQL($cn,$currency_sql->id);
        $this->assertEquals($check_sql->get('cr_name'),'xxxx','get row not updated');
        $this->assertEquals($check_sql->cr_name,'xxxx',' __get row not updated');
        $this->assertEquals($check_sql->getp('cr_name'),'xxxx',' getp row not updated');
        /**
         * delete
         */
        $check_sql->delete();
        $update_sql->load();
        $this->assertTrue($update_sql->id < 0,' row not deleted');
    }
    /**
     * @testdox  __get an unknow col 
     */
    function testGetUnknowCol1()
    {
        try {
            $cn=new \Database(DOSSIER);
            $currency_sql = new Currency_SQL($cn,0);
            $a=$currency_sql->dummy;
            $currency_sql->save();
            
        } catch (Exception $ex) {
                $this->assertEquals($ex->getCode(), EXC_DATA_SQL,' get unknow column fails');
        }
    }
      /**
     * @testdox get an unknow col 
     */
    function testGetUnknowCol2()
    {
        try {
            $cn=new \Database(DOSSIER);
            $currency_sql = new Currency_SQL($cn,0);
            $currency_sql->get("dummy");
            $currency_sql->save();
            
        } catch (Exception $ex) {
                $this->assertEquals($ex->getCode(), EXC_DATA_SQL,' get unknow column fails');
        }
    }
      /**
     * @testdox getp an unknow col 
     */
    function testGetUnknowCol3()
    {
        try {
            $cn=new \Database(DOSSIER);
            $currency_sql = new Currency_SQL($cn,0);
            $currency_sql->getp("dummy");
            $currency_sql->save();
            
        } catch (Exception $ex) {
                $this->assertEquals($ex->getCode(), EXC_DATA_SQL,' get unknow column fails');
        }
    }
     /**
     * @testdox  __set an unknow col 
     */
    function testSetUnknowCol1()
    {
        try {
            $cn=new \Database(DOSSIER);
            $currency_sql = new Currency_SQL($cn,0);
            $currency_sql->dummy=1;
            $currency_sql->save();
            
        } catch (Exception $ex) {
                $this->assertEquals($ex->getCode(), EXC_DATA_SQL,' get unknow column fails');
        }
    }
      /**
     * @testdox set an unknow col 
     */
    function testSetUnknowCol2()
    {
        try {
            $cn=new \Database(DOSSIER);
            $currency_sql = new Currency_SQL($cn,0);
            $currency_sql->set("dummy",1);
            $currency_sql->save();
            
        } catch (Exception $ex) {
                $this->assertEquals($ex->getCode(), EXC_DATA_SQL,' get unknow column fails');
        }
    }
      /**
     * @testdox setp an unknow col 
     */
    function testSetUnknowCol3()
    {
        try {
            $cn=new \Database(DOSSIER);
            $currency_sql = new Currency_SQL($cn,0);
            $currency_sql->setp("dummy",1);
            $currency_sql->save();
            
        } catch (Exception $ex) {
                $this->assertEquals($ex->getCode(), EXC_DATA_SQL,' get unknow column fails');
        }
    }
}