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
/**
 * class_action_gestion_sql.php
 *
 * @file
 * @brief abstract of the table public.action_gestion */


/**
 * @class Acc_Other_Tax_SQL
 * @brief  ORM public.acc_other_tax
 */

class Acc_Other_Tax_SQL extends Table_Data_SQL
{

    function __construct(DatabaseCore $p_cn, $p_id=-1)
    {
        $this->table="public.acc_other_tax";
        $this->primary_key="ac_id";
        /*
         * List of columns
         */
        $this->name=array(
            "ac_id"=>"ac_id",
            "ac_label"=>"ac_label",
            "ac_rate"=>"ac_rate",
            "ajrn_def_id"=>"ajrn_def_id",
            "ac_accounting"=>"ac_accounting"
        );
        /*
         * Type of columns
         */
        $this->type=array(
            "ac_id"=>"numeric",
            "ac_label"=>"text",
            "ac_rate"=>"numeric",
            "ajrn_def_id"=>"array",
            "ac_accounting"=>"text"
        );


        $this->default=array(
            "ac_id"=>"auto"

        );
        parent::__construct($p_cn, $p_id);
    }

}
