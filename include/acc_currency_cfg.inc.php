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

if (!defined('ALLOWED')) {    die('Appel direct ne sont pas permis'); }

/**
 * @file
 * @brief CFGCURRENCY Manage the currency and the rate 
 * @example test_currency_mtable.php
 */

require_once NOALYSS_INCLUDE."/class/currency_mtable.class.php";
require_once NOALYSS_INCLUDE."/lib/manage_table_sql.class.php";
require_once NOALYSS_INCLUDE.'/database/v_currency_last_value_sql.class.php';

$currency=new V_Currency_Last_Value_SQL($cn);



$currency_table=new Currency_MTable($currency);

$currency_table->set_callback("ajax_misc.php");
$currency_table->add_json_param("op", "CurrencyManage");
echo '<div class="content">';
$currency_table->create_js_script();
$currency_table->display_table();
echo '</div>';