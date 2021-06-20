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

// Copyright 2014 Author Dany De Bontridder danydb@aevalys.eu
/**
 *@file
 *@brief insert concerned operation , call from follow up
 */

// require_once '.php';
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
/**
 * Insert into follow-up the card (f_id) for the action_gestion (ag_id)
 */
$http=new HttpInput();
try {
    // follow_up id
    $ag_id=$http->request("ag_id","number");
    $ctl=$http->request("ctl");
    $selected_card=$http->request("selected_card","array",[]);
} catch (Exception $ex) {
     record_log(__FILE__.$ex->getMessage().$ex->getTraceAsString());
     return;
}
/*
 * security Who can do it ?
 */
if ( ! $g_user->can_write_action($ag_id)  ) {
    record_log(__FILE__."security : access refused");
    return;
}

$follow=new Follow_Up_Other_Concerned($cn,$ag_id);
$nb_card=count($selected_card);
for ($i=0;$i< $nb_card;$i++)
{
    $elt=$selected_card[$i];
    if (isNumber($elt)) {
        $follow->insert_linked_card($elt); 
    }
}
/**
 * Display all the linked card
 */

ob_start();
$follow->display_linked_count();
echo $follow->button_action_add_concerned_card( );
$response = ob_get_clean();
$html = escape_xml($response);
header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl>$ctl</ctl>
<code>$html</code>
</data>
EOF;
?>        