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
 * @brief  filter data in currency froqm datase , inherit from Data_Currency_Operation, filter on 
 * a specific card 
 */

/**
 * @class
 * @brief  filter data in currency froqm datase , inherit from Data_Currency_Operation, filter on 
 * a specific card 
 */
class Filter_Data_Currency_Card extends Data_Currency_Operation
{

    private $card; //!< qcode of a card

    function __construct($cn, $from_date, $to_date, $currency_id, $card)
    {
        parent::__construct($cn, $from_date, $to_date, $currency_id);
        $this->card=$card;
    }

    public function getCard()
    {
        return $this->card;
    }

    public function setCard($card)
    {
        $this->card=$card;
    }

    /**
     * 
     * @brief build the  SQL condition
     * @return SQL condition
     */
    public function SQL_Condition()
    {
        $sql=parent::SQL_Condition();
        $sql.=" and f_id=$4";
        return $sql;
    }

    /**
     * @brief returns data 
     * @return array 
     */
    public function get_data()
    {
        $sql=$this->build_SQL();
        $card_id=$this->getDbconx()->get_value("select f_id from fiche_detail where ad_id=23 and ad_value=$1",
                [trim(strtoupper($this->card))]);
        if (empty($card_id))
        {
            throw new Exception(_("Fiche non trouvée"));
        }
        $aArray=$this->getDbconx()->get_array($sql,
                [$this->getCurrency_id(),
                    $this->getFrom_date(),
                    $this->getTo_date(),
                    $card_id]);

        return $aArray;
    }

}
