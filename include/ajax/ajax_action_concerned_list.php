<?php

/* 
 * Copyright (C) 2020 Dany De Bontridder <dany@alchimerys.be>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
$http=new HttpInput();
try {
    // follow_up id
    $ag_id=$http->request("ag_id","number");
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
require_once 'class/follow_up_other_concerned.class.php';
ob_start();
echo HtmlInput::title_box(_("Liste Autres Fiches"), "action_concerned_list_dv");

$follow=new Follow_Up_Other_Concerned($cn,$ag_id);
$follow->display_table();

echo $follow->button_action_add_concerned_card( );
$csv="export.php?".
        http_build_query(["gDossier"=>Dossier::id(),
            "act"=>"CSV:FollowUpContactOption",
            "ag_id"=>$ag_id]);
echo HtmlInput::button_anchor(_("Export CSV"), $csv,"",' title="Export Contacts options"','smallbutton');

echo HtmlInput::button_close("action_concerned_list_dv");
$response = ob_get_clean();

if ( headers_sent() && DEBUGNOALYSS > 0) {
    echo $response;
} 
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
