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

// Copyright 2022 Author Dany De Bontridder dany@alchimerys.be


class Additional_Tax
{
    private $tax_amount;
    private $currency_amount;
    private $currency_id;
    private $ac_id;
    private $ac_label;
    private $ac_rate;
    private $ac_accounting;

    function __construct($tax_amount,$currency_amount,$currency_id,$ac_id,$ac_label,$ac_rate,$ac_accounting)
    {
        $this->tax_amount=round($tax_amount,2);
        $this->currency_amount=round($currency_amount,4);
        $this->currency_id=$currency_id;
        $this->ac_id=$ac_id;
        $this->ac_label=$ac_label;
        $this->ac_rate=$ac_rate;
        $this->ac_accounting=$ac_accounting;
    }
/**
 * @brief create an array of Additional_Tax
 * @param $p_jrn_id
 * @param $sum_euro
 * @param $sum_currency
 * @return array
 */
    static function get_by_operation($p_jrn_id,&$sum_euro,&$sum_currency)
    {
        bcscale(4);
        global $cn;
        $array = $cn->get_array("select 
        case when j_debit is false and jn.jrn_def_type='ACH' then 0-j_montant
            when j_debit is true and jn.jrn_def_type='VEN' then 0-j_montant
            else j_montant end j_montant,
        jrn.currency_id,
      oc_amount,
        jt.ac_id,
        jrnx.j_debit,
        aot.ac_label,
        aot.ac_rate,
        aot.ac_accounting,
        jn.jrn_def_type
            from jrn_tax jt
            join jrnx using (j_id)
            join jrn on (jrnx.j_grpt=jrn.jr_grpt_id)
            join jrn_def jn on (jrn.jr_def_id=jn.jrn_def_id)
            join acc_other_tax aot on (jt.ac_id=aot.ac_id)
            left join operation_currency oc ON  (oc.j_id=jt.j_id)
                where
            jr_id=$1", [$p_jrn_id]);
        $sum_currency=0;$sum_euro=0;
        if (empty($array)) { return array();}
        $nb=count($array);
        $a_additional_tax=array();
        for ($i=0;$i<$nb;$i++) {
            $a_additional_tax[]=new Additional_Tax($array[$i]['j_montant'],
                    $array[$i]['oc_amount'],
                    $array[$i]['currency_id'],
                    $array[$i]['ac_id'],
                    $array[$i]['ac_label'],
                    $array[$i]['ac_rate'],
                    $array[$i]['ac_accounting'],
            );
            $sum_euro=bcadd($sum_euro,$array[$i]['j_montant']);
            $sum_currency=bcadd($sum_currency,$array[$i]['oc_amount']);
        }
        $sum_euro=round($sum_euro,2);
        return $a_additional_tax;
    }

    /**
     * @brief display the additional_tax in the ledger_detail for Sales and Purchase
     * @param $p_jrn_id
     * @param $sum_euro
     * @param $sum_currency
     * @param int $decalage
     */
    static function display_row($p_jrn_id,&$sum_euro,&$sum_currency,$decalage=0)
    {
        $a_additional_tax=Additional_Tax::get_by_operation($p_jrn_id,$sum_euro,$sum_currency);
        $nb=count($a_additional_tax);
        for ($i=0;$i<$nb;$i++) {
            echo '<tr>';

            echo td($a_additional_tax[$i]->ac_accounting);
            echo td($a_additional_tax[$i]->ac_label." ( ".$a_additional_tax[$i]->ac_rate." %)");
            echo td(nbm($a_additional_tax[$i]->tax_amount),'class="num"');
            echo td("").td("").td("").td("");
            for ($e=0;$e<$decalage;$e++) { echo td("");}
            echo td(nbm($a_additional_tax[$i]->tax_amount),'class="num"');
            if ( $a_additional_tax[$i]->currency_id !=0 ) {
                echo td(nbm($a_additional_tax[$i]->currency_amount),'class="num"');
            }
            echo '</tr>';
        }
    }
}
