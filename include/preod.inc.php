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
require_once NOALYSS_INCLUDE.'/class/operation_predef_mtable.class.php';

if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
global $http;

$prd_op=new Op_predef_SQL($cn);

$operation_predef_mtable=new Operation_Predef_MTable($prd_op);
$operation_predef_mtable->set_json(json_encode(array(   "ac"=>$http->request("ac"),
                                                        "op"=>"save_predf",
                                                        "gDossier"=>Dossier::id()
                                                     )));
$operation_predef_mtable->create_js_script();
echo '<p>';
 $operation_predef_mtable->display_button_add();
echo '</p>';
$operation_predef_mtable->display_table();
echo '<p>';
$operation_predef_mtable->display_button_add();
echo '</p>';