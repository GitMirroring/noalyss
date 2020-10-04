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
if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
/**
 * @file
 * @brief add tag , used for follow up
 * @see Tag
 */
require_once NOALYSS_INCLUDE.'/class/tag.class.php';
//Single Tag
ob_start();
if ($_GET['clear']==1)
{
    /* Add a clear button */
    echo Tag::add_clear_button($_GET['pref']);
}
if ($http->request("obj")=='t')
{

    $tag=new Tag($cn, $http->get("id", "number"));
    $tag->update_search_cell($http->get("pref"));
}
elseif ($http->request("obj")=="g")
{
    // Add all the tag from the group 
    $aTag=$cn->get_array("select t_id,t_tag from jnt_tag_group_tag jtgt  join tags on (tag_id=t_id) where tag_group_id=1 order by 2 ");
    $nb_atag=count($aTag);
    $pref=$http->get("pref");
    if ( $nb_atag > 0) {
        for ($i=0;$i<$nb_atag;$i++){
            $tag=new Tag($cn,$aTag[$i]['t_id']);
            $tag->update_search_cell($pref);
        }
    }
    
}
$response=ob_get_clean();
$html=escape_xml($response);
header('Content-type: text/xml; charset=UTF-8');
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl></ctl>
<html>$html</html>
</data>
EOF;
exit();
?>

