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
 *  1/03/20
*/
/**
 * @file
 * @brief
 *
 */
// Copyright Author Dany De Bontridder danydb@noalyss.eu
require_once  NOALYSS_INCLUDE."/lib/html_input.class.php";

/**
 * @class
 * @brief
 *
 */
class Html_Input_Noalyss extends HtmlInput
{
    /**
     * Build a HTML string for adding multiple rows
     * @param string $p_ledger ledger type /Fin , Other = sale / purchase , M miscelleaneous
     */
    static function ledger_add_item($p_ledger)
    {
        //Fin , Other = sale / purchase , M miscelleaneous
        if ( ! in_array($p_ledger ,["F","O","M"]) ) {
            throw new Exception(_("LAD46 Erreur type"));
        }
        $id=uniqid();
        $num=new INum($id,0);
        $num->value=1;
        $num->size=2;
        $s_js=sprintf(' onClick="ledger_add_multiple(\'%s\')"',$id);

        $r=HtmlInput::hidden($id."_ledger",$p_ledger);
        $r.=$num->input();
        $r.=HtmlInput::button('add_item',_('ligne à ajouter'),
                $s_js);

        return $r;
    }
}