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
 * @brief Analytic account
 */
/**
 * @class Anc_Account
 * @brief Analytic account ; get the balance
 */
class Anc_Account
{

    private $poste_analytique_sql;

    public function __construct($p_cn, $p_id=-1)
    {
        $this->poste_analytique_sql=new Poste_analytique_SQL($p_cn, $p_id);
    }

    public function get_poste_analytique_sql()
    {
        return $this->poste_analytique_sql;
    }

    public function set_poste_analytique_sql($poste_analytique_sql): void
    {
        $this->poste_analytique_sql=$poste_analytique_sql;
    }
    /**
     * @brief Get the balance of an Analytic account 
     * @param string $p_cond_sql SQL condition to filter , usually the period
     * @see Impress::compute_amount
     * @return array key = credit , debit , solde
     */
    public function get_balance($p_cond_sql)
    {
        if ( DEBUGNOALYSS > 1) 
          {
              tracedebug("impress.debug.log", "$p_cond_sql", 'Anc_Account->get_balance \$p_cond_sql');
          }
        $cn=$this->poste_analytique_sql->cn;
        $detail=$cn->get_row("
              select  ( select coalesce (sum(oa1.oa_amount),0) 
              from operation_analytique oa1  
              where oa1.po_id=$1 
                and oa1.oa_debit='t' 
                $p_cond_sql 
                    )  as debit,
                ( select coalesce (sum(oa2.oa_amount),0)  
                from operation_analytique oa2  
                    where oa2.po_id=$1 
                    and oa2.oa_debit='f'
                    $p_cond_sql 
                 ) as credit
                 ",[$this->poste_analytique_sql->getp("po_id")]);
         if ( DEBUGNOALYSS > 1) 
          {
              tracedebug("impress.debug.log", $cn->sql, 'Anc_Account->get_balance SQL executed');
          }
        $tdetail['credit']=$detail['credit'];
        $tdetail['debit']=$detail['debit'];
        $tdetail['solde']=abs(bcsub($detail['debit'],$detail['credit']));
        return $tdetail;
        
        
    }
    /**
     * @brief retrieve the Analytic account thanks its code
     * @param type $p_code
     * @return type
     */
    public function load_by_code($p_code)
    {
        $cn=$this->poste_analytique_sql->cn;
        $p_id= $cn->get_value("select po_id from poste_analytique where po_name=$1",
                [ trim(strtoupper($p_code))]);
        $p_id=(empty($p_id))?-1:$p_id;
        $this->poste_analytique_sql->setp('po_id',$p_id);
        $this->poste_analytique_sql->load();
        return $this->poste_analytique_sql;
    }

}
