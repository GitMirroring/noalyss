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

if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
/**
 *@file
 *@brief remove concerned operation , call from follow up
 */
$http=new HttpInput();
try
{
    $ag_id=$http->get("ag_id", "number");
    $f_id=$http->get("f_id", "number");
}
catch (Exception $exc)
{
    echo $exc->getMessage();
    error_log($exc->getTraceAsString());
    return;
}
/*
 * security Who can do it ?
 */
if ( ! $g_user->can_read_action($ag_id)  ) {
    record_log(__FILE__."security : access refused");
    return;
}

$follow=new Follow_Up_Other_Concerned($cn,$ag_id);

ob_start();
$action_person_id=$cn->get_value(" select ap_id from action_person where ag_id =$1 and f_id =$2 ",[$ag_id,$f_id]);

// row id to update
$ctl= "other_".$action_person_id;

$follow->remove_linked_card($f_id);
$follow->display_linked_count();
echo $follow->button_action_add_concerned_card( );

$response = ob_get_clean();

if (headers_sent() && DEBUGNOALYSS > 0) {
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
