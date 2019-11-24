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
 * \brief this class extends PDF and let you export the detailled printing
 *  of any ledgers
 */
require_once NOALYSS_INCLUDE.'/class/print_ledger.class.php';
require_once NOALYSS_INCLUDE.'/lib/http_input.class.php';

class Print_Ledger_Simple extends  \Print_Ledger
{
    public function __construct ($p_cn,  Acc_Ledger $p_jrn,$p_from,$p_to,$p_filter_operation)
    {

        parent::__construct($p_cn,'L', 'mm', 'A4',$p_jrn,$p_from,$p_to,$p_filter_operation);
        
        $this->a_Tva=$this->get_ledger()->existing_vat();
        foreach($this->a_Tva as $line_tva)
        {
            //initialize Amount TVA
            $tmp1=$line_tva['tva_id'];
            $this->rap_tva[$tmp1]=0;
        }
        $this->jrn_type=$p_jrn->get_type();
        //----------------------------------------------------------------------
        /* report
         *
         * get rappel to initialize amount rap_xx
         *the easiest way is to compute sum from quant_
         */
        $from_periode=$this->get_from();
        $this->previous=$this->get_ledger()->previous_amount($from_periode);

        /* initialize the amount to report */
        foreach($this->previous['tva'] as $line_tva)
        {
            //initialize Amount TVA
            $tmp1=$line_tva['tva_id'];
            $this->rap_tva[$tmp1]=$line_tva['sum_vat'];
        }

        $this->rap_htva=$this->previous['price'];
        $this->rap_tvac=bcadd($this->previous['price'],$this->previous['vat']);
        $this->rap_tvac=bcadd($this->rap_tvac,$this->previous['tva_nd']);
        $this->rap_tvac=bcsub($this->rap_tvac,$this->previous['tva_np']);
        $this->rap_tvac=bcsub($this->rap_tvac,$this->previous['reversed']);
        $this->rap_priv=$this->previous['priv'];
        $this->rap_nd=$this->previous['tva_nd'];
        $this->rap_tva_np=$this->previous['tva_np'];
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
        //----------------------------------------------------------------------
        // Show column header, if $flag_tva is false then display vat as column
        foreach($this->a_Tva as $line_tva)
        {
            //initialize Amount TVA
            $tmp1=$line_tva['tva_id'];
            $this->rap_tva[$tmp1]=(isset($this->rap_tva[$tmp1]))?$this->rap_tva[$tmp1]:0;
        }
        $this->Cell(15,6,_('Pièce'));
        $this->Cell(10,6,_('Date'));
        $this->Cell(13,6,_('ref'));
        if ( $this->jrn_type=='ACH')
            $this->Cell(40,6,_('Client'));
        else
            $this->Cell(40,6,_('Fournisseur'));

        $flag_tva=(count($this->a_Tva) > 4)?true:false;
        if ( !$flag_tva )      $this->Cell(65,6,_('Description'));

        $this->Cell(15,6,_('HTVA'),0,0,'R');
        if ( $this->jrn_type=='ACH')
        {
            $this->Cell(15,6,_('Priv/DNA'),0,0,'R');
            $this->Cell(15,6,_('TVA ND'),0,0,'R');
        }
        $this->Cell(15,6,_('TVA NP'),0,0,'R'); // Unpaid TVA --> autoliquidation, NPR
        foreach($this->a_Tva as $line_tva)
        {
            $this->Cell(15,6,$line_tva['tva_label'],0,0,'R');
        }
        $this->Cell(15,6,'TVAC',0,0,'R');
        $this->Ln(5);

        $this->SetFont('DejaVu','',6);
        // page Header
        if ( ! $flag_tva)
            $this->Cell(143,6,'report',0,0,'R');
        else 
            $this->Cell(78,6,'report',0,0,'R');
        $this->Cell(15,6,nbm($this->rap_htva),0,0,'R'); /* HTVA */
        if ( $this->jrn_type != 'VEN')
        {
            $this->Cell(15,6,nbm($this->rap_priv),0,0,'R');  /* prive */
            $this->Cell(15,6,nbm($this->rap_nd),0,0,'R');  /* Tva ND */
        }
        $this->Cell(15,6,nbm($this->rap_tva_np),0,0,'R');  /* Tva ND */
        foreach($this->rap_tva as $line_tva)
        $this->Cell(15,6,nbm($line_tva),0,0,'R');
        $this->Cell(15,6,nbm($this->rap_tvac),0,0,'R'); /* Tvac */

        $this->Ln(6);
        //total page
        $this->tp_htva=0.0;
        $this->tp_tvac=0.0;
        $this->tp_priv=0;
        $this->tp_nd=0;
        $this->tp_tva_np=0;
        foreach($this->a_Tva as $line_tva)
        {
            //initialize Amount TVA
            $tmp1=$line_tva['tva_id'];
            $this->tp_tva[$tmp1]=0.0;
        }
    }
    /**
     *@brief write the Footer
     */
    function Footer()
    {
        //Position at 3 cm from bottom
        $this->SetY(-20);
        /* write reporting  */
        $flag_tva=(count($this->a_Tva) > 4)?true:false;
        if ( ! $flag_tva)
            $this->Cell(143,6,'Total page ','T',0,'R'); /* HTVA */
        else
            $this->Cell(78,6,'Total page ','T',0,'R'); /* HTVA */
        
        $this->Cell(15,6,nbm($this->tp_htva),'T',0,'R'); /* HTVA */
        if ( $this->jrn_type !='VEN')
        {
            $this->Cell(15,6,nbm($this->tp_priv),'T',0,'R');  /* prive */
            $this->Cell(15,6,nbm($this->tp_nd),'T',0,'R');  /* Tva ND */
        }
        $this->Cell(15,6,nbm($this->tp_tva_np),'T',0,'R');  /* Tva Unpaid */
        foreach($this->a_Tva as $line_tva)
        {
            $l=$line_tva['tva_id'];
            $this->Cell(15,6,nbm($this->tp_tva[$l]),'T',0,'R');
        }
        
        $this->Cell(15,6,nbm($this->tp_tvac),'T',0,'R'); /* Tvac */
        $this->Ln(2);
        $flag_tva=(count($this->a_Tva) > 4)?true:false;
        
        if ( ! $flag_tva)
            $this->Cell(143,6,'report',0,0,'R');
        else 
            $this->Cell(78,6,'report',0,0,'R');
        
        $this->Cell(15,6,nbm($this->rap_htva),0,0,'R'); /* HTVA */
        if ( $this->jrn_type !='VEN')
        {
            $this->Cell(15,6,nbm($this->rap_priv),0,0,'R');  /* prive */
            $this->Cell(15,6,nbm($this->rap_nd),0,0,'R');  /* Tva ND */
        }
        $this->Cell(15,6,nbm($this->rap_tva_np),0,0,'R');  /* Tva ND */
        
        foreach($this->a_Tva as $line_tva)
        {
            $l=$line_tva['tva_id'];
            $this->Cell(15,6,nbm($this->rap_tva[$l]),0,0,'R');
        }
        $this->Cell(15,6,nbm($this->rap_tvac),0,0,'R'); /* Tvac */
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
        bcscale(4);
        $ledger=$this->get_ledger();
        $ledger_history=Acc_Ledger_History::factory($this->cn, 
                                        array($ledger->id), 
                                        $this->get_from(), 
                                        $this->get_to(), 
                                        'D', 
                                        $this->get_filter_operation());
        $ledger_history->get_row();
        $a_jrn=$ledger_history->get_data();

        if ( $a_jrn == null ) return;
        
        // Count the number of VAT cols
        $flag_tva=(count($this->a_Tva) > 4)?true:false;
        
        // Prepare the query for reconcile date
        $prepared_query=new Prepared_Query($ledger->db);
        $prepared_query->prepare_reconcile_date();
        
        for ( $i=0;$i<count($a_jrn);$i++)
        {
            /* initialize tva */
            for ($f=0;$f<count($this->a_Tva);$f++)
            {
                $l=$this->a_Tva[$f]['tva_id'];
                $atva_amount[$l]=0;
            }

            // retrieve info from ledger
            $aAmountVat=$ledger->vat_operation($a_jrn[$i]['jr_grpt_id']);

            // put vat into array
            for ($f=0;$f<count($aAmountVat);$f++)
            {
                $l=$aAmountVat[$f]['tva_id'];
                $atva_amount[$l]=bcadd($atva_amount[$l],$aAmountVat[$f]['sum_vat']);
                $this->tp_tva[$l]=bcadd($this->tp_tva[$l],$aAmountVat[$f]['sum_vat']);
                $this->rap_tva[$l]=bcadd($this->rap_tva[$l],$aAmountVat[$f]['sum_vat']);
                
            }
            $row=$a_jrn[$i];
            $ret_reconcile=$ledger->db->execute('reconcile_date',array($row['jr_id']));
            $this->LongLine(15,5,($row['jr_pj_number']),0);
            $this->write_cell(10,5,$row['str_date_short'],0,0);
            $this->write_cell(13,5,$row['jr_internal'],0,0);
            list($qc,$name)=$this->get_tiers($row['jr_id'],$this->jrn_type);
            $this->LongLine(40,5,"[".$qc."]".$name,0,'L');

            if ( !$flag_tva )    {        
            $this->LongLine(65,5,mb_substr($row['jr_comment'],0,150),0,'L');
            }

            /* get other amount (without vat, total vat included, private, ND */
            $other=$ledger->get_other_amount($a_jrn[$i]['jr_grpt_id']);
            
            $this->write_cell(15,5,nbm($other['price']),0,0,'R');
            
            if ( $ledger_history->get_ledger_type() !='VEN')
            {
	      $this->write_cell(15,5,nbm($other['priv']),0,0,'R');
	      $this->write_cell(15,5,nbm($other['tva_nd']),0,0,'R');
            }
            
	    $this->write_cell(15,5,nbm($other['tva_np']),0,0,'R');
            
            foreach ($atva_amount as $row_atva_amount)
            {
                    $this->write_cell(15, 5, nbm($row_atva_amount), 0, 0, 'R');
            }

	    $l_tvac=bcadd($other['price'], bcsub($other['vat'],$other['tva_np']));
	    $l_tvac=bcadd($l_tvac,$other['tva_nd']);
            $this->write_cell(15,5,nbm($l_tvac),0,0,'R');
            $this->line_new(2);
            // Add the payment information on another row
            $max=Database::num_row($ret_reconcile);
            $str_payment="";
            if ($max > 0) {
                $this->write_cell(15,5, _("Payé par"));
                $sep="";
                for ($e=0;$e<$max;$e++) {
                    $row=Database::fetch_array($ret_reconcile, $e);
                    $msg=( $row['qcode_bank'] != "")?"[".$row['qcode_bank']."]".$row['qcode_name']:$row['jr_internal'];
                    $str_payment=$row['jr_date'].$msg.$sep;
                    $sep=' , ';
                }
                $this->write_cell (120,5,$str_payment);
            }
            
            
            $this->line_new(3);
            // Total page
            $this->tp_htva=bcadd($this->tp_htva,$other['price']);
            $this->tp_tvac=bcadd($this->tp_tvac,$other['price']);
            $this->tp_tvac=bcadd($this->tp_tvac,$other['vat']);
            $this->tp_tvac=bcadd($this->tp_tvac,$other['tva_nd']);
            $this->tp_tvac=bcsub($this->tp_tvac,$other['tva_np']);
            $this->tp_tva_np=bcadd($this->tp_tva_np,$other['tva_np']);
            $this->tp_priv=bcadd($this->tp_priv,$other['priv']);
            $this->tp_nd=bcadd($this->tp_nd,$other['tva_nd']);
            
            // Total report
            $this->rap_htva=bcadd($this->rap_htva,$other['price']);
            $this->rap_tvac=bcadd($this->rap_tvac,$other['price']);
            $this->rap_tvac=bcadd($this->rap_tvac,$other['vat']);
            $this->rap_tvac=bcsub($this->rap_tvac,$other['tva_np']);
            $this->rap_tvac=bcadd($this->rap_tvac,$other['tva_nd']);
            $this->rap_priv=bcadd($this->rap_priv,$other['priv']);
            $this->rap_nd=bcadd($this->rap_nd,$other['tva_nd']);
            $this->rap_tva_np=bcadd($this->rap_tva_np,$other['tva_np']);

        }
    }

}
