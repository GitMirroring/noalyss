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
  /**
   *@file
   *@brief Display the list of tags
   */
if ( !defined ('ALLOWED') )  die('Appel direct ne sont pas permis');

ob_start();
$tag=new Tag_Action($cn);
$tag->select();

//------------------- Propose to add a tag

$js=sprintf("onclick=\"show_tag('%s','%s','%s','j')\"",Dossier::id(),'','-1');
if ( $g_user->check_action(TAGADD) == 1) { echo HtmlInput::button("tag_add", _("Ajout d'un tag"), $js);}
echo HtmlInput::button_close("tag_div");

$response=  ob_get_clean();
if (headers_sent() && DEBUGNOALYSS > 0  ){
    echo $response;
} else {
    header('Content-type: text/xml; charset=UTF-8');
}
$html=escape_xml($response);
echo <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<data>
<ctl></ctl>
<code>$html</code>
</data>
EOF;
exit();


?>
