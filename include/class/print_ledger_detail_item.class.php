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
/**
 * Print detail of operation PURCHASE or SOLD plus the items
 * There is no report of the different amounts
 *
 * @author danydb
 */

class Print_Ledger_Detail_Item extends Print_Ledger
{

    protected $show_col; //!< $show_col (bool) show columns

    public function __construct (Database $p_cn,Acc_Ledger $p_jrn,$p_from,$p_to,$p_filter_operation)
    {

        parent::__construct($p_cn,'L', 'mm', 'A4',$p_jrn,$p_from,$p_to,$p_filter_operation);
        $this->show_col=true;
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
        $high=6;
        $this->SetFont('DejaVu', '', 6);
        $this->write_cell(20, $high, _('Date'),0,0,  'L', false);
        $this->write_cell(20, $high, _('Numéro interne'), 0,0,  'L', false);
        $this->write_cell(50, $high, _('Code'),0,0,'L',false);
        $this->write_cell(80, $high, _('Libellé'),0,0,'L',false);
        $this->write_cell(20, $high, _('Tot HTVA'), 0, 0,'R', false,'R', false);
        $this->write_cell(20, $high, _('Tot TVA NP'), 0, 0,'R', false, false);
        $this->write_cell(20, $high, _("Autre Tx"), 0, 0, 'R', false);
        $this->write_cell(20, $high, _('Tot TVA'), 0, 0, 'R', false);
        $this->write_cell(20, $high, _('TVAC'), 0, 0, 'R', false);
        $this->line_new(6);
        $this->show_col=true;
        
    }
    /**
     *@brief write the Footer
     */
    function Footer()
    {
        $this->Ln(2);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(50,8,' Journal '.$this->get_ledger()->get_name(),0,0,'C');
        //Arial italic 8
        //Page number
        $this->Cell(30,8,'Date '.$this->date." - Page ".$this->PageNo().'/{nb}',0,0,'L');
        // Created by NOALYSS
        $this->Cell(0,8,'Created by NOALYSS, online on https://www.noalyss.eu',0,0,'R',false,'https://www.noalyss.eu');
    }


    /**
     *@brief export the ledger in  PDF
     */
    function export()
    {
      bcscale(2);
      $jrn_type=$this->get_ledger()->get_type();

      switch ($jrn_type)
      {
          case 'VEN':
              $ledger=new Acc_Ledger_Sale($this->cn, $this->get_ledger()->jrn_def_id);
              $ret_detail=$ledger->get_detail_sale($this->get_from(),$this->get_to(), $this->filter_operation);
              break;
          case 'ACH':
                $ledger=new Acc_Ledger_Purchase($this->cn, $this->get_ledger()->jrn_def_id);
                $ret_detail=$ledger->get_detail_purchase($this->get_from(),$this->get_to(),$this->filter_operation);
              break;
          default:
              die (__FILE__.":".__LINE__.'Journal invalide');
              break;
      }
        if ( $ret_detail == null ) return;
        
        $prepared_query=new Prepared_Query($this->cn);
        $prepared_query->prepare_reconcile_date();

        $nb=Database::num_row($ret_detail);
        $this->SetFont('DejaVu', '', 6);
        $internal="";
        $this->fill_row(0);
        $high=8;
        $high_lg=8;
        for ( $i=0;$i< $nb ;$i++)
        {
            
            $row=Database::fetch_array($ret_detail, $i);
            if ($internal != $row['jr_internal'])
            {

                // Print the general info line width=270mm
                $this->write_multi(20, $high_lg, $row['jr_date'],1,  'L', true);
                $this->write_cell(20, $high,$row['jr_pj_number'].".". $row['jr_internal'], 1, 0, 'L', true);
                $this->write_multi(50, $high_lg, $row['quick_code']." ".$row['tiers_name'],1,'L',true);
                $this->write_multi(80, $high_lg, $row['jr_comment'],1,'L',true);
                $this->write_cell(20, $high, nbm($row['htva']), 1, 0, 'R', true);
                $this->write_cell(20, $high, nbm($row['tot_tva_np']), 1, 0, 'R', true);
                $this->write_cell(20, $high, nbm($row['other_tax_amount']), 1, 0, 'R', true);
                $this->write_cell(20, $high, nbm($row['tot_vat']), 1, 0, 'R', true);
                $sum=noalyss_bcadd($row['htva'],$row['tot_vat']);
                $sum=noalyss_bcadd($row['other_tax_amount'],$sum);
                $sum=noalyss_bcsub($sum,$row['tot_tva_np']);
                $this->write_cell(20, $high, nbm($sum), 1, 0, 'R', true);
                $internal=$row['jr_internal'];
                $this->line_new(6);
                // Payment info
                // Prepare the query for reconcile date
                $ret_reconcile=$this->cn->execute('reconcile_date', array($row['jr_id']));
                $max=Database::num_row($ret_reconcile);
                for ($e=0; $e<$max; $e++)
                {
                    $ret_row=Database::fetch_array($ret_reconcile, $e);
                    $msg=( $ret_row['qcode_bank']!="")?"[".$ret_row['qcode_bank']."]":$ret_row['jr_internal'];
                    $this->write_cell(200, $high,
                            sprintf(_("Paiement montant %s date %s methoded %s "), $ret_row['jr_montant'],
                                    $ret_row['jr_date'], $msg
                    ));
                    $this->line_new(6);
                }
                // on the first line, the code for each column is displaid
                if ( $this->show_col == true ) {

                    
                    // Header detail
                    $this->write_multi(30,$high_lg,_('QuickCode'));
                    $this->write_cell(30,$high,_('Poste'));
                    $this->write_multi(70,$high_lg,_('Libellé'));
                    $this->write_cell(20,$high,_('Prix/Unit'),0,0,'R');
                    $this->write_cell(20,$high,_('Quant.'),0,0,'R');
                    $this->write_cell(20,$high,_('HTVA'),0,0,'R');
                    $this->write_cell(20,$high,_('TVA NP'),0,0,'R');
                    $this->write_cell(20,$high,_('Code TVA'));
                    $this->write_cell(20,$high,_('TVA'),0,0,'R');
                    $this->write_cell(20,$high,_('TVAC'),0,0,'R');
                    $this->line_new(6);
                    $this->show_col=false;
                 } 
            }
            // Print detail sale / purchase
            $this->write_multi(30,$high_lg,$row['j_qcode']);
            $this->write_cell(30,$high,$row['j_poste']);
            $comment=($row['j_text']=="")?$row['item_name']:$row['j_text'];
            $this->write_multi(70,$high_lg,$comment);
            $this->write_cell(20,$high,nbm($row['price_per_unit']),0,0,'R');
            $this->write_cell(20,$high,nbm($row['quantity']),0,0,'R');
            $this->write_cell(20,$high,nbm($row['price']),0,0,'R');
            $this->write_cell(20,$high,nbm($row['vat_sided']),0,0,'R');
            $this->write_cell(20,$high,$row['tva_label']);
            $this->write_cell(20,$high,nbm($row['vat']),0,0,'R');
            $sum=bcadd($row['price'],$row['vat']);
            $this->write_cell(20,$high,nbm($sum),0,0,'R');
            $this->line_new(6);
            
        }
    }

}
?>
