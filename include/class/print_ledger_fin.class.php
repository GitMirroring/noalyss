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
require_once NOALYSS_INCLUDE.'/lib/pdf.class.php';
class Print_Ledger_Financial extends PDF
{
    private $rap_amount; /* amount from begining exercice */
    private $tp_amount; /* amount total page */
    
    function __construct($p_cn,  Acc_Ledger $p_jrn)
    {
        parent::__construct($p_cn,'P','mm','A4');
        $this->ledger=$p_jrn;
        $this->jrn_type=$p_jrn->get_type();
        
        // report from begin exercice
        $this->rap_amount=0; 
        
        // total page
        $this->tp_amount=0;
        
        $amount=$this->ledger->previous_amount($_GET['from_periode']);
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
        $this->Cell(20,6,_('Device'),0,0,'R');
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
        $this->Cell(0,8,'Created by NOALYSS, online on http://www.noalyss.eu',0,0,'C',false,'http://www.noalyss.eu');

    }
    /**
     *@brief print the pdf for a financial ledger
     */
    function export()
    {
        $http=new HttpInput();
        $a_jrn=$this->ledger->get_operation($http->get('from_periode',"number"),
                                            $http->get('to_periode','number'));
        $this->SetFont('DejaVu', '', 6);
        if ( $a_jrn == null ) return;
        bcscale(2);
        $this->ledger->load();
        $currency=$this->ledger->get_currency()->get_code();
        $currency_id=$this->ledger->get_currency()->get_id();
        $this->cn->prepare("amount_cur",
                "select jrn2.jr_id , 
                    sum(coalesce(oc_amount,0)) as sum_ocamount,
                    sum(coalesce(oc_vat_amount,0)) as sum_ocvat_amount
             	from operation_currency
                    join jrnx using (j_id)
                    join jrn as jrn2 on (j_grpt=jrn2.jr_grpt_Id) 
             	where 
                    j_id in (select j_id from jrnx where j_grpt=jrn2.jr_grpt_id and j_debit='t')
                    and  jr_id=$1
                    group by jr_id"
                );
        for ( $i=0;$i<count($a_jrn);$i++)
        {
            $row=$a_jrn[$i];
            $this->write_cell(15,5,$row['pj']); 
            $this->write_cell(10,5,$row['date_fmt']);
            $this->write_cell(10,5,$row['internal']);

            $name=$this->ledger->get_tiers($this->jrn_type,$row['id']);
            $this->write_cell(40,5,$name,0,0,'L');

            $this->LongLine(60,5,$row['comment'],0,'L');
            $amount=$this->cn->get_value('select qf_amount from quant_fin where jr_id=$1',array( $row['id']));
            if ( $currency_id != 0) {
                $ret_amount_cur=$this->cn->execute("amount_cur",array($row['id']));
                
                if ( $this->cn->count($ret_amount_cur) == 1) {
                    $amount_cur=Database::fetch_result($ret_amount_cur, 0,1);
                    $this->write_cell(20,5,sprintf('%s %s',nbm($amount_cur),$currency),0,0,'R');
                }
            }
            $this->write_cell(20,5,sprintf('%s',nbm($amount)),0,0,'R');
            $this->line_new(5);
            $this->tp_amount=bcadd($this->tp_amount,$amount);

        }
    }
}
