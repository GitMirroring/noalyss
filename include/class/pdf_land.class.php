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

// Copyright Author Dany De Bontridder danydb@noalyss.eu

require_once NOALYSS_INCLUDE.'/class/pdf.class.php';


class PDFLand extends PDF
{

    public function __construct ($p_cn = null, $orientation = 'P', $unit = 'mm', $format = 'A4')
    {

        if($p_cn == null) die("No database connection. Abort.");
        $this->bigger=0;

        parent::__construct($p_cn,'L', $unit, $format);
        date_default_timezone_set ('Europe/Paris');
        $this->AddFont('DejaVu','','DejaVuSans.ttf',true);
        $this->AddFont('DejaVu','B','DejaVuSans-Bold.ttf',true);
        $this->AddFont('DejaVu','BI','DejaVuSans-BoldOblique.ttf',true);
        $this->AddFont('DejaVuCond','','DejaVuSansCondensed.ttf',true);
        $this->AddFont('DejaVuCond','B','DejaVuSansCondensed-Bold.ttf',true);
        $this->AddFont('DejaVuCond','I','DejaVuSansCondensed-Oblique.ttf',true);

        $this->cn  = $p_cn;
        $this->own = new Noalyss_Parameter_Folder($this->cn);
        $this->soc = $this->own->MY_NAME;
        $this->date = date('d.m.Y');
    }
    function Header()
    {
        //Arial bold 12
        $this->SetFont('DejaVu', 'B', 10);
        //Title
        $this->Cell(0,10,$this->dossier, 'B', 0, 'C');
        //Line break
        $this->Ln(20);
    }
    function Footer()
    {
        //Position at 2 cm from bottom
        $this->SetY(-20);
        //Arial italic 8
        $this->SetFont('DejaVuCond', 'I', 8);
        //Page number
        $this->Cell(0,8,'Date '.$this->date." - Page ".$this->PageNo().'/{nb}',0,0,'C');
        $this->Ln(3);
        // Created by NOALYSS
        $this->Cell(0,8,'Created by NOALYSS, online on http://www.noalyss.eu',0,0,'C',false,'http://www.noalyss.eu');

    }
}