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

// Copyright Author Dany De Bontridder danydb@aevalys.eu
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

require_once NOALYSS_INCLUDE."/database/payment_method_sql.class.php";
require_once NOALYSS_INCLUDE."/class/payment_method_mtable.class.php";

$payment_method_sql=new Payment_method_SQL($cn);
$payment_method_mtable=new Payment_Method_MTable($payment_method_sql);

$payment_method_mtable->add_json_param("op", "payment_method");
$payment_method_mtable->set_callback("ajax_misc.php");
$payment_method_mtable->create_js_script();

echo $payment_method_mtable->display_table();
