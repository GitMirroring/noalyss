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

/*!
 * \file
 * \brief print a listing of financial
 */
/*!
 * \class Print_Ledger_Financial
 * \brief print a listing of financial
 */

class Print_Ledger_Financial extends Print_Ledger
{
    private $rap_amount; /* amount from begining exercice */
    private $tp_amount; /* amount total page */
    private $jrn_type; //!< $jrn_type (VEN,ACH,ODS,FIN) ledger type
    
    function __construct(Database $p_cn,  Acc_Ledger $p_jrn,$p_from,$p_to)
    {
        parent::__construct($p_cn,'P','mm','A4',$p_jrn,$p_from,$p_to,'all');
        $this->jrn_type=$p_jrn->get_type();
        
        // report from begin exercice
        $this->rap_amount=0; 
        
        // total page
        $this->tp_amount=0;
        
        $amount=$this->get_ledger()->previous_amount($this->get_from());
        $this->rap_amount=$amount['amount'];
    }
    function Header()
    {
        //Arial bold 12
        $this->SetFont('DejaVu', 'B', 12);
        //Title
        $this->Cell(0,10,$this->dossier, 'B', 0, 'C');
        //Line break
        $this->SetFont('DejaVu', 'B', 7);
        $this->Ln(10);
        $this->Cell(40,6,_('report'),0,0,'R');
        $this->Cell(40,6,nbm($this->rap_amount),0,0,'R');
        $this->Ln(6);
        $this->SetFont('DejaVu', 'B', 7);
        $this->Cell(15,6,_('Piece'));
        $this->Cell(10,6,_('Date'));
        $this->Cell(10,6,_('Interne'));
        $this->Cell(40,6,_('Dest/Orig'));
        $this->Cell(60,6,_('Commentaire'));
        $this->Cell(20,6,_('Devise'),0,0,'R');
        $this->Cell(20,6,_('Montant'),0,0,'R');
        $this->Ln(6);
        
    }
    function Footer()
    {
        $this->SetFont('DejaVu', 'B', 7);

        $this->Cell(40,6,_('Total page'),0,0,'R');
        $this->Cell(40,6,nbm($this->tp_amount),0,0,'R');
        bcscale(2);
        $this->rap_amount=bcadd($this->rap_amount,$this->tp_amount);
        $this->Cell(40,6,_('Total à reporter'),0,0,'R');
        $this->Cell(40,6,nbm($this->rap_amount),0,0,'R');
        $this->tp_amount=0;
        //Position at 2 cm from bottom
        $this->SetY(-20);
        //Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        //Page number
        $this->Cell(0,8,'Date '.$this->date." - Page ".$this->PageNo().'/{nb}',0,0,'C');
        $this->Ln(3);
        // Created by NOALYSS
        $this->Cell(0,8,'Created by NOALYSS, online on https://www.noalyss.eu',0,0,'C',false,'https://www.noalyss.eu');

    }
    /**
     *@brief print the pdf for a financial ledger
     */
    function export()
    {
        $ledger=$this->get_ledger();
        $a_jrn=$ledger->get_operation($this->get_from(),$this->get_to());

        $this->SetFont('DejaVu', '', 6);
        if ( $a_jrn == null ) return;
        bcscale(2);
        
        $prepare=new Prepared_Query($this->cn);
        $prepare->prepare_currency();
        
        for ( $i=0;$i<count($a_jrn);$i++)
        {
            $row=$a_jrn[$i];
            $this->write_cell(15,5,$row['pj']); 
            $this->write_cell(10,5,$row['date_fmt']);
            $this->write_cell(10,5,$row['internal']);

            $name=$ledger->get_tiers($this->jrn_type,$row['id']);
            $this->write_cell(40,5,$name,0,0,'L');

            $this->write_multi(60,3,$row['comment'],0,'L');
            $amount=$this->cn->get_value('select qf_amount from quant_fin where jr_id=$1',array( $row['id']));
            $ret_amount_cur=$this->cn->execute("amount_cur",array($row['id']));

            if ( $this->cn->count($ret_amount_cur) == 1) {
                
                $amount_cur=Database::fetch_result($ret_amount_cur, 0,1);
                $this->write_cell(20,5,sprintf('%s %s',nbm($amount_cur),$row['cr_code_iso']),0,0,'R');
            } else {

                $this->write_cell(20,5,"",0,0,'R');
            }
            $this->write_cell(20,5,sprintf('%s',nbm($amount)),0,0,'R');
            $this->line_new(5);
            $this->tp_amount=bcadd($this->tp_amount,$amount);

        }
    }
}
