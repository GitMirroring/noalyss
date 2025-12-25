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
 * \brief display last follow-up
 */
namespace Noalyss\Widget;

use HtmlInput;
use Dossier;
/**
 * @class Last_Event
 * @brief display last_followup
 */
class Last_Event extends Widget
{
        /**
     * @brief display 10 last action
     * @return void
     * @throws \Exception
     */
    function display()
    {
        global $cn;
        $this->open_div();
        $this->title(_("Dernières actions du suivi"));

        $gestion=new \Follow_Up($cn);
        $array=$gestion->get_last(MAX_ACTION_SHOW);
        $len_array=count($array);
        include "last_event-display.php";
        $this->close_div();
    }

}