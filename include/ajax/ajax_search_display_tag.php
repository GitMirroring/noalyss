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
 *@see Tag
 */

require_once NOALYSS_INCLUDE.'/class/tag_action.class.php';
require_once NOALYSS_INCLUDE.'/class/tag_operation.class.php';

try {
    $caller_obj=$http->request("caller_obj");
    $prefix=$http->request("pref");
} catch (Exception $ex) {
    echo $ex->getMessage();
    return;
}

if ( $caller_obj == 'Tag_Action' ) { 
    $tag=new Tag_Action($cn);
}elseif ($caller_obj=='Tag_Operation')
{
    $tag=new Tag_Operation( $cn);
} else {
    throw new Exception("AJD001 invalid caller [$caller_obj]");
}

$response=  $tag->select_search($pref);

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
