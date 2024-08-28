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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 10/08/24
/*! 
 * \file
 * \brief display last operation
 */
namespace Noalyss\Widget;
/**
 * @class Last_Operation
 * @brief display last accountancy operation
 * @note this widget is  included in Noalyss Core and a part of the code (javascript + css + ajax)
 * are still included in NOALYSS Code, this code should move here and will be part of a "cleansing code" process
 *
 */
class Last_Operation extends Widget
{
    /**
     * @brief display last accounting
     * @return void
     * @throws \Exception
     */
    public function display()
    {
        global $cn;
        $this->open_div();

        echo \HtmlInput::title_box(_('Dernières opérations'),"last_operation_box_div",'zoom','popup_recherche('.\Dossier::id().')','n');
        require_once "last_operation-display.php";
        $this->close_div();
    }

}