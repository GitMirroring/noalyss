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
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

require_once NOALYSS_INCLUDE.'/lib/noalyss_sql.class.php';
require_once NOALYSS_INCLUDE.'/lib/database.class.php';

/**
 * class_currency_sql.php
 *
 * @file
 * @brief abstract of the table public.currency */
class Currency_SQL extends Noalyss_SQL
{

    function __construct(Database $p_cn, $p_id=-1)
    {
        $this->table="public.currency";
        $this->primary_key="id";
        /*
         * List of columns
         */
        $this->name=array(
            "id"=>"id"
            , "cr_code_iso"=>"cr_code_iso"
            ,"cr_name"=>"cr_name"
        );
        /*
         * Type of columns
         */
        $this->type=array(
            "id"=>"numeric"
            , "cr_code_iso"=>"text"
            , "cr_name"=>"text"
        );


        $this->default=array(
            "id"=>"auto"
        );

        $this->date_format="DD.MM.YYYY";
        parent::__construct($p_cn, $p_id);
    }

}
