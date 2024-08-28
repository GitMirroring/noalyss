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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 18/08/24
/*! 
 * \file
 * \brief bookmark widget
 */
namespace Noalyss\Widget;

class Bookmark extends Widget
{
    function display()
    {
        global $g_user;
        $this->open_div();


        $this->title(_("Favoris") . '&#x2728;');
        $bookmark_sql="select distinct b_id,b_action,b_order,me_code,me_description, javascript"
            . " from bookmark "
            . "join v_menu_description_favori on (code=b_action or b_action=me_code)"
            . "where "
            . "login=$1 order by me_code";
        $a_bookmark=$this->db->get_array($bookmark_sql,array($g_user->login));

        $dossier_id=\Dossier::id();
        if (count($a_bookmark) >0 ) {
            $p=0;
            foreach ($a_bookmark as $item) {
                $a_code=  explode('/',$item['b_action']);
                $idx=count($a_code);
                $code=$a_code[$idx-1];
                $url=http_build_query(array("ac"=>$item['b_action'],"gDossier"=>$dossier_id));
                $p++;
                $class=($p&1)?' odd ':'even';
                $description=h($item['me_description']);
                echo <<<EOF
<div class="row {$class}">
    <div class="col-3">
        
        
        <a class="mtitle" href="do.php?{$url}">{$code}  </a> 
    </div>
    <div class="col-7">
        
        
        <a class="mtitle" href="do.php?{$url}">{$description}</a> 
    </div>
</div>
EOF;

            }
        }
        $this->close_div();

    }

}