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

/*!\file
 * \brief included file for managing the predefined operation
 */

if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
global $http;

$prd_op=new Op_predef_SQL($cn);

$operation_predef_mtable=new Operation_Predef_MTable($prd_op);
$operation_predef_mtable->set_json(json_encode(array(   "ac"=>$http->request("ac"),
                                                        "op"=>"save_predf",
                                                        "gDossier"=>Dossier::id()
                                                     )));
echo '<form method="GET">';
echo Dossier::hidden();
echo HtmlInput::hidden("ac",$http->request("ac"));
$filter_ledger=new ISelect("f_ledger");


$filter_ledger->selected=$http->request('f_ledger',"number",-1);
$sql_filter='where '.$g_user->get_ledger_sql('ALL',2);


$filter_ledger->value=$cn->make_array("select jrn_def_id ,jrn_def_name from jrn_def
$sql_filter and jrn_def_type !='FIN' order by jrn_def_name",1);
echo $filter_ledger->input();
echo HtmlInput::submit('ledgerf',_("Filter par journal"));
$operation_predef_mtable->display_button_add();

echo '</form>';
if ( $filter_ledger->selected != -1 && isNumber($filter_ledger->selected ) == 1 ) {
    $sql_filter.= ' and jrn_Def_id = '.sql_string($filter_ledger->selected);
} 
$operation_predef_mtable->create_js_script();
echo '<p>';
echo '</p>';
 $operation_predef_mtable->display_table($sql_filter." and jrn_def_id not in "
         . " ( select jrn_def_id from jrn_def where jrn_def_type ='FIN') ");
echo '<p>';
$operation_predef_mtable->display_button_add();
echo '</p>';