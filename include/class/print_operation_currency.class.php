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
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief manage the operation in currency : export CSV, export PDF , output in HTML
 */
require_once NOALYSS_INCLUDE."/class/acc_currency.class.php";

class Print_Operation_Currency
{
    
    private $from_date;
    private $to_date;
    private $currency_id;
    private $from_account;
    private $to_account;

    function __construct($cn)
    {
        
    }

    public function getFrom_date()
    {
        return $this->from_date;
    }

    public function getTo_date()
    {
        return $this->to_date;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getFrom_account()
    {
        return $this->from_account;
    }

    public function getTo_account()
    {
        return $this->to_account;
    }

    public function setFrom_date($from_date)
    {
        $this->from_date=$from_date;
    }

    public function setTo_date($to_date)
    {
        $this->to_date=$to_date;
    }

    public function setCurrency($currency)
    {
        $this->currency=$currency;
    }

    public function setFrom_account($from_account)
    {
        $this->from_account=$from_account;
    }
    public function from_get()
    {   
        $http=new HttpInput();
        $this->from_date=$http->get("from_date","date");
        $this->to_date=$http->get("to_date","date");
        $this->from_account=$http->get("from_account");
        $this->to_account=$http->get("to_account");
        $this->currency_id=$http->get("currency_id","numeric");
        
    }
    public function setTo_account($to_account)
    {
        $this->to_account=$to_account;
    }
    /**
     * @brief Return array of data
     */
    function get_date()
    {
        $aArray=$this->cn->get_array("select jr_id,
            j_date,
            j_montant,
            oc_amount,
            j_poste,
            jr_comment,
            jr_internal,
            jr_pj_number,
            currency_id,
            currency_rate,
            currency_rate_ref,
            f_id
        from jrnx
            join jrn on (jr_grpt_id=jrnx.j_grpt)
            join operation_currency oc using (j_id)
        where 
            j_poste >= $1 
            and j_poste <= $2 
            and j_date >= to_date($3 ,'DD.MM.YYYY')
            and j_date <=to_date($4 ,'DD.MM.YYYY')
            and currency_id = $5 
            order by j_poste,j_date  
        ",[$this->from_account,$this->to_account,$this->from_date,$this->to_date,$this->currency_id]);
        return $aArray;
    }
    function export_html()
    {
        
    }
    function export_csv()
    {
        
    }
    function export_pdf()
    {
        
    }
}
?>
