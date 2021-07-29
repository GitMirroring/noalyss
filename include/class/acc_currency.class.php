<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

require_once NOALYSS_INCLUDE."/database/v_currency_last_value_sql.class.php";
/**
 * @file
 * @brief display currency , convert to euro , and save them if used
 */

/**
 * @class Acc_Currency
 * @brief display currency , convert to euro , and save them if used. The default currency has the id 0 and is
 * normally EUR
 */
class Acc_Currency
{

    private $cn; //!< database conx
    private $currency; //!< v_currency_last_value_sql

    function __construct(Database $p_cn, $p_id=-1)
    {
        $this->cn=$p_cn;
        $this->currency=new V_Currency_Last_Value_SQL($p_cn, $p_id);
    }

    /**
     * Retrieve a V_Currency_Last_Value_SQL thanks its iso_code
     * @param string $p_iso
     */
    function get_by_iso($p_iso)
    {
        $p_iso=trim(strtoupper($p_iso));
        $id=$this->cn->get_value("select currency_id from v_currency_last_value where cr_code_iso=$1", [$p_iso]);
        if ($id!="")
        {
            $this->currency->setp("currency_id", $id);
            $this->currency->load();
        }
    }

    /**
     * Retrieve a V_Currency_Last_Value_SQL thanks its id
     * @param number $p_id
     */
    function set_id($p_id)
    {
        $this->currency->setp("currency_id", $p_id);
        $this->currency->load();
    }
    /**
     * Check if the record is found
     * @return boolean
     */
    function is_found() {
        if ($this->currency->currency_id==-1)
        {
            return FALSE;
        }
        return TRUE;
    }
    /**
     * return the rate of the currency
     */
    function get_rate()
    {
        return $this->currency->getp("ch_value");
        
    }
    /**
     * return the iso code of the currency
     */
    function get_code()
    {
        return $this->currency->getp("cr_code_iso");
        
        
    }
    /**
     * return the id of the currency
     */
    function get_id()
    {
        return $this->currency->getp("currency_id");
        
    }
    /**
     * Create an object iselect to select the currency, 
     * @return \ISelect
     */
    function select_currency()
    {
        $select=new ISelect('p_currency_code');
        $a_currency=$this->cn->make_array("select currency_id,cr_code_iso from v_currency_last_value order by cr_code_iso");
        $select->value=$a_currency;
        $select->selected=$this->currency->cr_code_iso;
        
        return $select;
    }
    /**
     * Return sum of the w/o VAT and VAT or an operation
     */
    function sum_amount($p_jr_id)
    {
        $sql = " 
            select sum(round(oc_amount,2)) +
		sum(round(oc_vat_amount,2)) 
	from operation_currency 
	where 
            j_id in (
                    select j_id 
                    from jrnx 
                    where 
                    j_grpt in ( select jr_grpt_id from jrn where jr_id=$1))";
        $sum=$this->cn->get_value($sql, [$p_jr_id]);
        return $sum;

    }
    /**
     * Return the rate used at a certain date or -1 if date if not in the format DD.MM.YYYY or -2 if no value is found
     * @param date $p_date
     */
    function get_rate_date($p_date)
    {
        if ( $this->get_id() == 0 ) { return 1;}
        
        if (isDate($p_date) == null ) {
            throw new Exception(_("Date invalide"));
        }
        
        $sql="select ch_value from currency_history 
            where
            ch_from=(select max(ch_from) from currency_history where ch_from <= to_date($1,'DD.MM.YYYY') and currency_id=$2)
            and currency_id=$2";
        $value=$this->cn->get_value($sql,[$p_date,$this->get_id()]);
        if ($value == "") {
            throw new Exception(_("Aucun taux à cette date , aller sur CFGCURRENCY"));
        }
        return $value;
    }
    

}
