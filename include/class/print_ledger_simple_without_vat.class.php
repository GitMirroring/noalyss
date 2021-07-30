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
 * \brief this class extends PDF and let you export the detailled printing
 *  of any ledgers
 */
/*!
 * \class Print_Ledger_Simple_Without_Vat
 * \brief this class extends PDF and let you export the detailled printing
 *  of any ledgers
 */

class Print_Ledger_Simple_Without_Vat extends Print_Ledger
{
    public function __construct ($p_cn,$p_jrn,$p_from,$p_to,$p_filter_operation)
    {


        parent::__construct($p_cn,'L', 'mm', 'A4',$p_jrn,$p_from,$p_to,$p_filter_operation);
        $this->jrn_type=$p_jrn->get_type();
        //----------------------------------------------------------------------
        /* report
         *
         * get rappel to initialize amount rap_xx
         *the easiest way is to compute sum from quant_
         */
        $this->previous=$this->get_ledger()->previous_amount($p_from);


        $this->rap_htva=$this->previous['price'];
        $this->rap_tvac=$this->previous['price'];
        $this->rap_priv=$this->previous['priv'];

    }

    function setDossierInfo($dossier = "n/a")
    {
        $this->dossier = dossier::name()." ".$dossier;
    }
    /**
     *@brief write the header of each page
     */
    function Header()
    {
        //Arial bold 12
        $this->SetFont('DejaVu', 'B', 12);
        //Title
        $this->Cell(0,10,$this->dossier, 'B', 0, 'C');
        //Line break
        $this->Ln(20);
        $this->SetFont('DejaVu', 'B', 8);
        /* column header */
        $this->Cell(15,6,'Pièce');
        $this->Cell(15,6,'Date');
        $this->Cell(20,6,'ref');
        if ( $this->jrn_type=='ACH')
            $this->Cell(60,6,'Client');
        else
            $this->Cell(60,6,'Fournisseur');
        $this->Cell(105,6,'Commentaire');
        if ( $this->jrn_type=='ACH')
        {
            $this->Cell(15,6,'Privé',0,0,'R');
        }
        $this->Cell(15,6,'Prix',0,0,'R');

        $this->Ln(5);

        $this->SetFont('DejaVu','',6);
        // page Header
        $this->Cell(215,6,'report',0,0,'R'); /* HTVA */
        if ( $this->jrn_type != 'VEN')
        {
            $this->Cell(15,6,sprintf('%.2f',$this->rap_priv),0,0,'R');  /* prive */
        }
        $this->Cell(15,6,sprintf('%.2f',$this->rap_htva),0,0,'R'); /* HTVA */




        $this->Ln(6);
        //total page
        $this->tp_htva=0.0;
        $this->tp_tvac=0.0;
        $this->tp_priv=0;
        $this->tp_nd=0;
    }
    /**
     *@brief write the Footer
     */
    function Footer()
    {
        //Position at 3 cm from bottom
        $this->SetY(-20);
        /* write reporting  */
        $this->Cell(215,6,'Total page ','T',0,'R'); /* HTVA */
        if ( $this->jrn_type !='VEN')
        {
            $this->Cell(15,6,sprintf('%.2f',$this->tp_priv),'T',0,'R');  /* prive */
        }
        $this->Cell(15,6,sprintf('%.2f',$this->tp_htva),'T',0,'R'); /* HTVA */
        $this->Cell(0,6,'','T',0,'R'); /* line */
        $this->Ln(2);

        $this->Cell(215,6,'report',0,0,'R'); /* HTVA */
        if ( $this->jrn_type !='VEN')
        {
            $this->Cell(15,6,sprintf('%.2f',$this->rap_priv),0,0,'R');  /* prive */
        }
        $this->Cell(15,6,sprintf('%.2f',$this->rap_htva),0,0,'R'); /* HTVA */
        $this->Ln(2);

        //Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        //Page number
        $this->Cell(0,8,'Date '.$this->date." - Page ".$this->PageNo().'/{nb}',0,0,'L');
        // Created by NOALYSS
        $this->Cell(0,8,'Created by NOALYSS, online on http://www.noalyss.eu',0,0,'R',false,'http://www.noalyss.eu');
    }

    /**
     *@brief export the ledger in  PDF
     */
    function export()
    {
        
        $ledger_history=Acc_Ledger_History::factory($this->cn, 
                                                array($this->get_ledger()->id), 
                                                $this->get_from(), 
                                                $this->get_to(), 
                                                'D', 
                                                $this->get_filter_operation());
        
        $ledger_history->get_row();
        $a_jrn=$ledger_history->get_data();
        
        if ( empty($a_jrn ) ) return;
        
         // Prepare the query for reconcile date
        $prepared_query=new Prepared_Query($this->cn);
        $prepared_query->prepare_reconcile_date();
        
        $ledger=$this->get_ledger();
        
        for ( $i=0;$i<count($a_jrn);$i++)
        {

            $row=$a_jrn[$i];
            $this->LongLine(15,5,($row['jr_pj_number']),0);
            $this->write_cell(15,5,$row['str_date_short'],0,0);
            $this->write_cell(20,5,$row['jr_internal'],0,0);
            list($qc,$name)=$this->get_tiers($row['jr_id'],$this->jrn_type);
            $this->write_cell(20,5,$qc,0,0);
            $this->LongLine(40,5,$name,0,'L');

            $this->LongLine(105,5,$row['jr_comment'],0,'L');

            /* get other amount (without vat, total vat included, private, ND */
            $other=$ledger->get_other_amount($a_jrn[$i]['jr_grpt_id']);
            $this->tp_htva+=$other['price'];
            $this->tp_priv+=$other['priv'];
            $this->rap_htva+=$other['price'];
            $this->rap_priv+=$other['priv'];


            if ( $ledger_history->get_ledger_type() !='VEN')
            {
                $this->write_cell(15,5,sprintf("%.2f",$other['priv']),0,0,'R');
            }

            $this->write_cell(15,5,sprintf("%.2f",$other['price']),0,0,'R');
            $ret_reconcile=$this->cn->execute('reconcile_date',array($row['jr_id']));
            $max=Database::num_row($ret_reconcile);
            $str_payment="";
            if ($max > 0) {
                $sep="";
                for ($e=0;$e<$max;$e++) {
                    $row=Database::fetch_array($ret_reconcile, $e);
                    $msg=( $row['qcode_bank'] != "")?"[".$row['qcode_bank']."]":$row['jr_internal'];
                    $str_payment=$row['jr_date'].$msg.$sep;
                    $sep=' , ';
                }
                $this->write_cell (50,5,$str_payment);
            }
            $this->line_new(5);
        }
    }

}
