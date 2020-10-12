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
if ( !defined ('ALLOWED') )  die('Appel direct ne sont pas permis');
/**
 *@file
 *@brief remove tag , call from follow up
 *@see Follow_Up
 *@see Tag
 */
$http=new HttpInput();

$fl=new Follow_Up($cn);

$fl->ag_id=$http->request("ag_id","number");

if ( $g_user->can_write_action($fl->ag_id) != TRUE )  return;

if ( $http->request("isgroup") == 't') {
    $fl->tag_add($http->request('t_id',"number"));
} else {
        // Add all the tag from the group 
    $aTag=$cn->get_array("select t_id,t_tag from jnt_tag_group_tag jtgt  join tags on (tag_id=t_id) where tag_group_id=$1 order by 2 ",[$http->request("t_id","number")]);
    $nb_atag=count($aTag);
    if ( $nb_atag > 0) {
        for ($i=0;$i<$nb_atag;$i++){
             $fl->tag_add($aTag[$i]['t_id']);
        }
    }
}

ob_start();

$fl->tag_cell();

$response=  ob_get_clean();
$html=escape_xml($response);
header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl></ctl>
<code>$html</code>
</data>
EOF;
exit();


?>
