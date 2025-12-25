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
// Copyright Author Dany dany  5 déc. 2025
/*
 * 
 */

/**
 * @file pdf.class
 * @brief answer to an inplace object
 */
namespace Noalyss\XMLDocument;

class PDF extends \PDF_Core
{
    function Header()
    {
       
    }
    function Footer()
    {
        $date=date('d.m.y h:i');
       //Position at 20 cm from bottom
        $this->SetY(-20);
        //Arial italic 8
        $this->SetFont('Arial', '', 8);
        //Page number
        parent::Cell(0,8,'Date '.$date." - Page ".$this->PageNo().'/{nb}',0,0,'C');
        parent::Ln(3);
        // Created by NOALYSS
        parent::Cell(0,8,'Created by NOALYSS, online on https://www.noalyss.eu',0,0,'C',false,'https://www.noalyss.eu');
        parent::Ln(3);
    }
}