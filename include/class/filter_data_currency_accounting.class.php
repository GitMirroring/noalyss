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

require_once NOALYSS_INCLUDE."/class/data_currency_operation.class.php";

/**
 * @file
 * @brief  filter data in currency from datase , inherit from Data_Currency_Operation, filter on 
 * a range of accounting or only one
 */

/**
 * @class
 * @brief  filter data in currency from datase , inherit from Data_Currency_Operation, filter on 
 * a range of accounting or only one
 */
class Filter_Data_Currency_Accounting extends Data_Currency_Operation
{

    private $from_accounting;
    private $to_accounting;

    function __construct($cn, $from_date, $to_date, $currency_id, $from_accounting, $to_accounting)
    {
        parent::__construct($cn, $from_date, $to_date, $currency_id);
        $this->from_accounting=$from_accounting;
        $this->to_accounting=$to_accounting;
    }
    /**
     * @brief from_accounting : lowest accounting range
     * @return accounting (string)
     */
    public function getFrom_accounting()
    {
        return $this->from_accounting;
    }
    /**
     * 
     * @brief to_accounting : highest accounting range
     * @return accounting (string)
     */
    public function getTo_accounting()
    {
        return $this->to_accounting;
    }
/**
     * 
     * @brief from_accounting : lowest accounting range
     * @return accounting (string)
     */
    public function setFrom_accounting($from_accounting)
    {
        $this->from_accounting=$from_accounting;
        return $this;
    }
  /**
     * 
     * @brief to_accounting : highest accounting range
     * @return accounting (string)
     */
    public function setTo_accounting($to_accounting)
    {
        $this->to_accounting=$to_accounting;
        return $this;
    }
    /**
     * 
     * @brief build the  SQL condition
     * @return SQL condition
     */
    public function SQL_Condition()
    {
        $sql=parent::SQL_Condition();
        $sql.=' and j_poste >= $4 and j_poste <= $5';
        return $sql;
    }
    /**
     * @brief returns data 
     * @return array 
     */
    public function get_data()
    {
        $sql=$this->build_SQL();
        $aArray=$this->getDbconx()->get_array($sql,
                [$this->getCurrency_id(),
            $this->getFrom_date(),
            $this->getTo_date(),
            $this->getFrom_accounting(),
            $this->getTo_accounting()]);

        return $aArray;
    }
    public function info()
    {
        return sprintf("currency_id %s from_date %s to_date %s from_accounting %s to_accounting %s",
                $this->getCurrency_id(),$this->getFrom_date(),$this->getTo_date(),$this->getFrom_accounting(),
                $this->getTo_accounting());
    }

}
