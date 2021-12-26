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
 * \brief Manage the attributs ,CCARDAT
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

$obj=new Attr_Def_SQL($cn);
$mtable=new Card_Attribut_MTable($obj);
$mtable->add_json_param("op", "card");
$mtable->add_json_param("op2", "attribute");
$mtable->set_callback("ajax_misc.php");
$mtable->create_js_script();
echo '<div class="content">';
echo $mtable->display_table("order by ad_text");
echo '</div>';

