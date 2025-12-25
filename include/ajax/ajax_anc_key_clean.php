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
 * @file
 * @brief show the available distribution keys for analytic activities. Expected
 * parameter are 
 *  - t for the table id
 *  - amount is the amount to distributed
 *
 */
// Copyright (2014) Author Dany De Bontridder danydb@aevalys.eu
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

$http=new HttpInput();

try
{
    $amount=$http->get("amount", "number");
    $table_id=$http->get("t");
    $ledger=$http->get('led',"number");
    $jrnx_id=$http->get("jrnx_id","string","");
    $sequence=$http->get("p_seq","number");
}
catch (Exception $exc)
{
    record_log($exc);
    return;
}


global $g_user;

if ($g_user->get_ledger_access($ledger)=='W')
{
    ob_start();
    $anc_op = new Anc_Operation($cn);
    $anc_op->j_id = $jrnx_id;
    $anc_op->in_div= $t ;

    /* compute total price */
    bcscale(2);
//    echo $anc_op->display_table(1, $amount, $t);
    echo $anc_op->display_form_plan(NULL,1,1,$sequence,$amount,$table_id,FALSE);

    $response = ob_get_clean();
} else {
    $response = _("Interdit");
}
$html = escape_xml($response);
header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl></ctl>
<code>$html</code>
</data>
EOF;
?>        
