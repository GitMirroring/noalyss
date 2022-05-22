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
 *  1/03/20
*/
/**
 * @file
 *  @brief Compute , display additional tax for Sale and Purchase,
 *
 */
// Copyright Author Dany De Bontridder danydb@noalyss.eu 2022

class Acc_Tax_Purchase_Sale
{
    private $jrn_tax_sql;

    function __construct($p_cn, $p_id = -1)
    {
        $this->jrn_tax_sql = new Jrn_Tax_SQL($p_cn, $p_id);
    }

    /**
     * @return Jrn_Tax_SQL
     */
    public function getJrnTaxSql(): Jrn_Tax_SQL
    {
        return $this->jrn_tax_sql;
    }

    /**
     * @param Jrn_Tax_SQL $jrn_tax_sql
     */
    public function setJrnTaxSql(Jrn_Tax_SQL $jrn_tax_sql)
    {
        $this->jrn_tax_sql = $jrn_tax_sql;
        return $this;
    }

    /**
     * @brief retrieve Jrn_Tax_SQL thx its jr_id
     * @param $p_jrid
     */
    public function get_by_operation_id($p_jrid)
    {
        $jt_id= $this->jrn_tax_sql->get_cn()->get_value(
                "select jt_id from jrn_tax where jr_id=$1",
                [$p_jrid]);
        $jt_id=(empty($jt_id))?-1:$jt_id;
        $this->jrn_tax_sql->set("jt_id",$jt_id);
        if ($jt_id == -1 ) return;
        $this->jrn_tax_sql->load();
    }

    /**
     * @brief display in detail
     */
    public function display()
    {
        echo_warning(__FILE__.":".__LINE__.__CLASS__."::".__FUNCTION__."not implemented");
    }
    public function save()
    {
        if ($this->jrn_tax_sql->get("jt_id") == -1 ) {
            $this->jrn_tax_sql->insert();
            return;
        }
        $this->jrn_tax_sql->update();
    }

}