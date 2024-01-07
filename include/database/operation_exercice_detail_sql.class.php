<?php

/**
 *   This file is part of NOALYSS
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
 * copyright Dany WM De Bontridder($(YEAR))
 *
 *  $(DATE)
 */

/**
 * @file
 * @brief abstract of the table
 */

/**
 * @class
 * @brief  abstract of the table
 */
class Operation_Exercice_Detail_SQL extends Table_Data_SQL
{

    function __construct(DatabaseCore $p_cn, $p_id = -1)
    {
        $this->table = "operation_exercice_detail";
        $this->primary_key = "oed_id";
        /*
         * List of columns
         */
        $this->name = array(
            'oed_id' => 'oed_id'
        , 'oe_id' => 'oe_id'
        , 'oed_poste' => 'oed_poste'
        , 'oed_qcode' => 'oed_qcode'
        , 'oed_label' => 'oed_label'
        , 'oed_amount' => 'oed_amount'
        , 'oed_debit' => 'oed_debit'


        );
        /*
         * Type of columns
         */
        $this->type = array(
            'oed_id' => 'number'
        , 'oe_id' => 'number'
        , 'oed_poste' => 'text'
        , 'oed_qcode' => 'text'
        , 'oed_label' => 'text'
        , 'oed_amount' => 'number'
        , 'oed_debit' => 'text'

        );

        $this->default = array(
            "oed_id" => "oed_id"
        );



        $this->date_format = "DD.MM.YYYY";
        parent::__construct($p_cn, $p_id);
    }


}