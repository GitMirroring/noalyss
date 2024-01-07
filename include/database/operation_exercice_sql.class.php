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
 * @brief abstract of the table public.operation_exercice
 */

/**
 * @class
 * @brief  abstract of the table public.operation_exercice
 */
class Operation_Exercice_SQL extends Table_Data_SQL
{

    function __construct(DatabaseCore $p_cn, $p_id = -1)
    {
        $this->table = "public.operation_exercice";
        $this->primary_key = "oe_id";
        /*
         * List of columns
         */
        $this->name = array(
            "oe_id" => "oe_id",
            "oe_date" => "oe_date",
            "oe_text" => "oe_text",
            "oe_type" => "oe_type",
            "tech_user" => "tech_user",
            "tech_date" => "tech_date",
            "oe_dossier_id" => "oe_dossier_id",
            "oe_exercice" => "oe_exercice",
            "oe_transfer_date"=>"oe_transfer_date"

        );
        /*
         * Type of columns
         */
        $this->type = array(
            "oe_id" => "number",
            "oe_date" => "date",
            "oe_text" => "text",
            "oe_type" => "text",
            "tech_user" => "text",
            "tech_date" => "date",
            "oe_dossier_id" => "number",
            "oe_exercice" => "number",
            "oe_transfer_date"=>"date"
        );


        $this->default = array(
            "oe_id" => "oe_id",
            "tech_user" => "tech_user",
            "tech_date" => "tech_date",
        );

        $this->date_format = "DD.MM.YYYY";
        parent::__construct($p_cn, $p_id);
    }


}