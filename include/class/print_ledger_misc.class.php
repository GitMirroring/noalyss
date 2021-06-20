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
 * \brief print a listing of financial
 */

class Print_Ledger_Misc extends Print_Ledger
{
    function __construct(Database $p_cn,$p_jrn,$p_from,$p_to)
    {
        parent::__construct($p_cn,'P','mm','A4',$p_jrn,$p_from,$p_to,"all");
    }
    function Header()
    {
        //Arial bold 12
        $this->SetFont('DejaVu', 'B', 12);
        //Title
        $this->Cell(0,10,$this->dossier, 'B', 0, 'C');
        //Line break
        $this->Ln(20);
        $this->SetFont('DejaVu', 'B', 7);
        $this->Cell(10,6,_('Date'));
        $this->Cell(30,6,_('Piece'));
        $this->Cell(20,6,_('Interne'));
        $this->Cell(25,6,_('Tiers'));
        $this->Cell(60,6,_('Commentaire'));
        $this->Cell(20,6,_('Devise'),0,0,'R');
        $this->Cell(15,6,_('Montant'),0,0,'R');
        $this->Ln(6);

    }
    function Footer()
    {
        //Position at 2 cm from bottom
        $this->SetY(-20);
        //Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        //Page number
        $this->Cell(0,6,'Date '.$this->date." - Page ".$this->PageNo().'/{nb}',0,0,'C');
        $this->Ln(3);
        // Created by NOALYSS
        $this->Cell(0,6,'Created by NOALYSS, online on http://www.noalyss.eu',0,0,'C',false,'http://www.noalyss.eu');
    }
    /**
     *@brief print the pdf
     *@param
     *@param
     *@return
     *@see
     */
    function export()
    {
        $a_jrn=$this->get_ledger()->get_rowSimple($this->get_from(),$this->get_to());

        $this->SetFont('DejaVu', '', 6);
        if ( $a_jrn == null ) return;
        $ledger=$this->get_ledger();
        for ( $i=0;$i<count($a_jrn);$i++)
        {
            $row=$a_jrn[$i];
            
            $this->write_cell(10,5,  smaller_date($row['date']));
            $this->LongLine(30,5,$row['jr_pj_number']);
            $this->write_cell(20,5,$row['jr_internal']);
	    $type=$this->cn->get_value("select jrn_def_type from jrn_def where jrn_def_id=$1",array($a_jrn[$i]['jr_def_id']));
	    $other=mb_substr($ledger->get_tiers($type,$a_jrn[$i]['jr_id']),0,25);
	    $this->LongLine(25,5,$other,0,'L');
            $positive=$row['montant'];
            $this->LongLine(60,5,$row['comment'],0,'L');
             if ( $type == 'FIN' ) {
	       $positive = $this->cn->get_value("select qf_amount from quant_fin  ".
					  " where jr_id=".$row['jr_id']);
             }
            $this->write_cell(20,5,nbm(bcadd($row['sum_ocvat_amount'],$row['sum_ocamount']),2).$row['cr_code_iso'],0,0,'R');
            $this->write_cell(15,5,nbm($positive),0,0,'R');
            $this->line_new(5);

        }
    }
}
