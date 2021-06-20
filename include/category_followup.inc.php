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
 * \brief this file is used for the follow up of the customer (mail, meeting...)
 *  - sb = detail
 *  - sc = sv
 *  - sd = this parameter is used here
 *  - $cn = database connection
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
global $http;
/**
 *\note problem with ShowActionList, this function is local
 * to the file action.inc.php. And this function must different for each
 *  follow-up
 */
$sub_action=$http->request('sa',"string","list");

$ag_id=$http->request("ag_id","string","0");
if (! isset($_GET['submit_query'])) {$_REQUEST['closed_action']=1;$_GET['closed_action']=1;}

$p_action=$http->request('ac');
$base="do.php?".http_build_query(["ac"=>$p_action,
                                "sc"=>"sv",
                                "sb"=>"detail",
                                "f_id"=>$http->request("f_id","number"),
                                "gDossier"=>Dossier::id()
                                ])."&amp;"
        ;
$retour=HtmlInput::button_anchor(_('retour'),$base);

$fiche=new Fiche($cn,$http->request("f_id","number"));

$_GET['qcode']=$fiche->get_quick_code();
$_REQUEST['qcode'] = $fiche->get_quick_code();

echo '<div class="content">';
require_once NOALYSS_INCLUDE.'/action.common.inc.php';
echo '</div>';
