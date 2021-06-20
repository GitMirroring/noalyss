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


class PDFBalance_simple extends PDF
{
    /**
     *@brief set_info(dossier,from poste,to poste, from periode, to periode)
     *@param $p_from_poste start = poste
     *@param $p_to_poste   end   = poste
     *@param $p_from       periode start
     *@param $p_to         periode end
     */
    function set_info($p_from_poste,$to_poste,$p_from,$p_to)
    {
        $this->dossier=sprintf(_('Balance simple %s'),dossier::name());
        $this->from_poste=$p_from_poste;
        $this->to_poste=$to_poste;
        $this->from=$p_from;
        $this->to=$p_to;
    }
    function Header()
    {
        parent::Header();
        $this->SetFont('DejaVu','B',8);
        $titre=sprintf(_("Balance simple poste %s %s date %s %s"),
            $this->from_poste,
            $this->to_poste,
            $this->from,
            $this->to);
        $this->Cell(0,7,$titre,1,0,'C');

        $this->Ln();
        $this->SetFont('DejaVu','',6);

        $this->Cell(110,7,_('Poste Comptable'),'B');
        $this->Cell(20,7,_('Débit'),'B',0,'L');
        $this->Cell(20,7,_('Crédit'),'B',0,'L');
        $this->Cell(20,7,_('Solde'),'B',0,'L');
        $this->Cell(20,7,_('D/C'),'B',0,'L');
        $this->Ln();

    }
}
