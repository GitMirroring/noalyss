<?php
/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2024) Author Dany De Bontridder <dany@alchimerys.be>
/**
 * @file
 * @brief : widget agenda,
 * @note this widget is  included in Noalyss Core and a part of the code (javascript + css + ajax)
 *   are still included in NOALYSS Code
 */
namespace Noalyss\Widget;

/*!
 * \class Agenda
 * \brief : widget agenda,
 * \note this widget is  included in Noalyss Core and a part of the code (javascript + css + ajax)
 *  are still included in NOALYSS Code, this code should move here and will be part of a "cleansing code" process
*/
class Agenda extends Widget
{

    function display()
    {
        global $g_user;
        /* others report */
        $cal=new \Calendar();
        $cal->get_preference();

        $obj=sprintf("{gDossier:%d,invalue:'%s',outdiv:'%s','distype':'%s'}",
            \Dossier::id(),'per','calendar_zoom_div','cal');

       $this->open_div();
        echo \HtmlInput::title_box(_('Calendrier'),'cal_div','zoom',"calendar_zoom($obj)",'n');
        echo $cal->display('short',0);
        $this->close_div();
    }



}