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
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief build the SQL and fetch data currency operation from database , 
 */

/**
 * @class
 * @brief build the SQL and fetch data of data currency operation from database , 
 */
class Data_Currency_Operation
{

    private $dbconx;
    private $currency_id;
    private $from_date;
    private $to_date;

    function __construct($cn, $from_date="", $to_date="", $currency_id=0)
    {
        global $g_user;
        $this->dbconx=$cn;
        $this->currency_id=$currency_id;

        $periode_limit=$g_user->get_limit_current_exercice();
        $this->from_date=($from_date=="")?$periode_limit[0]:$from_date;
        $this->to_date=($to_date=="")?$periode_limit[1]:$to_date;
    }
    /**
     * @brief retrieve the currency code
     * @return string ISO CODE
     */
    public function to_currency_code()
    {
        $currency_code=$this->dbconx->get_value("select cr_code_iso from currency where id=$1",[$this->currency_id]);
        return $currency_code;
    }
    /**
     * @brief retrieve currency.od
     */
    public function getCurrency_id()
    {
        return $this->currency_id;
    }

    public function getFrom_date()
    {
        return $this->from_date;
    }

    public function getTo_date()
    {
        return $this->to_date;
    }

    public function setFrom_date($from_date)
    {
        $this->from_date=$from_date;
    }

    public function setTo_date($to_date)
    {
        $this->to_date=$to_date;
    }
    /**
     * @brief retrieve currency.od
     */

    public function setCurrency_id($currency_id)
    {
        $this->currency_id=$currency_id;
    }
    /**
     * Database connection
     */
    public function getDbconx()
    {
        return $this->dbconx;
    }

    public function setDbconx($dbconx)
    {
        $this->dbconx=$dbconx;
        return $this;
    }

    /**
     * @brief build the SQL to execute
     */
    function build_SQL()
    {
        $sql=" select jr_id,
            j_date,
            j_montant,
            coalesce(oc_amount,j_montant) oc_amount,
            j_poste,
            jr_comment,
            jr_internal,
            jr_pj_number,
            jrn.currency_id,
            (select cr_code_iso from currency c where c.id=jrn.currency_id) as currency_code_iso,
            coalesce (currency_rate,1) currency_rate,
            coalesce (currency_rate_ref,1) currency_rate_ref,
            f_id,
            (select ad_value from fiche_detail fd1 where fd1.f_id=jrnx.f_id and ad_id=23) as fiche_qcode
        from jrnx
            join jrn on (jr_grpt_id=jrnx.j_grpt)
            left join operation_currency oc using (j_id)
            join jrn_def on (jr_def_id=jrn_def.jrn_def_id) 
            ";
        $sql.=$this->SQL_Condition();

        $sql.="     order by j_poste,j_date  ";

        return $sql;
    }
    /**
     * 
     * @brief build the  SQL condition
     * @return SQL condition
     */
    function SQL_Condition()
    {
        global $g_user;

        return " where jrn.currency_id = $1 and ".$g_user->get_ledger_sql('ALL', 3)
                ." and jr_date >= to_date($2,'DD.MM.YYYY')"
                ." and jr_date <= to_date($3,'DD.MM.YYYY')"
        ;
    }
    /**
     * @brief returns data 
     * @return array 
     */
    function get_data()
    {
        $sql=$this->build_SQL();
        $aArray=$this->dbconx->get_array($sql, [$this->getCurrency_id(), $this->getFrom_date(),$this->getTo_date()]);

        return $aArray;
    }

}

?>
