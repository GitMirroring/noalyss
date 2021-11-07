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
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief 
 */
/* !
 * \class 
 * \brief export  Anc_Acc_List (ANCBCC) to PDF
 */

class PDF_Anc_Acc_List extends PDF
{

    private $anc_acc_list;
    private $str_title;
    private $a_size;

    function __construct(Anc_Acc_List $p_anc_acc_list)
    {
        parent::__construct($p_anc_acc_list->db);
        $this->anc_acc_list=$p_anc_acc_list;
        $a_title=array(
            1=>_('Comptabilité Analytique Fiche/Activité'),
            2=>_("Comptabilité Analytique Poste comptable/Activité"),
            3=>_('Comptabilité Analytique Activité/Fiche'),
            4=>_('Comptabilité Analytique Activité/Poste Comptable'));
        if (!isset($a_title[$p_anc_acc_list->card_poste]))
        {
            throw new Exception("PAAL.46 : invalid object");
        }
        $this->str_title=$a_title[$p_anc_acc_list->card_poste];
    }
    /**
     * @brief Display the total amount
     * @param type $p_pdf
     * @param type $p_total
     */
    private function put_total($a_size,$p_pdf, $p_total)
    {
        $p_pdf->SetFont('DejaVu', 'B', 7);
        $p_pdf->write_cell($a_size['po_name']+$a_size['po_description'], 5, _("Total"));
        $p_pdf->write_cell($a_size['po_amount'], 5, nbm($p_total, 2), 0, 0, 'R');
        $p_pdf->line_new(5);
    }
    /**
     * @brief Print the title
     * @param PDF $p_pdf
     * @param int $n_row_idx , idx of anc_acc_list->arow
     */
    private function put_title($p_pdf, $n_row_idx)
    {
        $p_pdf->SetFont('DejaVu', 'BU', 10);
        switch ($this->anc_acc_list->card_poste)
        {
            case 1:

                $p_pdf->write_cell(190, 12,
                        $this->anc_acc_list->arow[$n_row_idx]['j_qcode']." ".
                        $this->anc_acc_list->arow[$n_row_idx]['name']);

                break;
            case 2:

                $p_pdf->write_cell(190, 12,
                        $this->anc_acc_list->arow[$n_row_idx]['j_poste']." ".
                        $this->anc_acc_list->arow[$n_row_idx]['name']);

                break;
            case 3:

                $p_pdf->write_cell(190, 12,
                        $this->anc_acc_list->arow[$n_row_idx]['po_name']." ".
                        $this->anc_acc_list->arow[$n_row_idx]['po_description']);

                break;
            case 4:

                $p_pdf->write_cell(190, 12,
                        $this->anc_acc_list->arow[$n_row_idx]['po_name']." ".
                        $this->anc_acc_list->arow[$n_row_idx]['po_description']);

                break;
            default:
                break;
        }
        $p_pdf->line_new();
    }
    /**
     * @brief Return the previous item : card or account or activity depending of the crossing method
     * @param type $n_row_idx
     * @return type
     */
    private function get_previous($n_row_idx)
    {
        switch ($this->anc_acc_list->card_poste)
        {
            case 1:
                return $this->anc_acc_list->arow[$n_row_idx]['f_id'];
                break;
            case 2:
                return $this->anc_acc_list->arow[$n_row_idx]['j_poste'];
                break;
            case 3:
                return $this->anc_acc_list->arow[$n_row_idx]['po_id'];
                break;
            case 4:
                return $this->anc_acc_list->arow[$n_row_idx]['po_id'];
                break;
        }
    }
    /**
     * @brief 
     * @return \PDF
     */
    private function pdf_card()
    {
        $pdf=new PDF($this->anc_acc_list->db);
        $pdf->setDossierInfo(_("Balance croisée A/C"));
        $pdf->SetTitle($this->str_title);
        $pdf->AddPage();
        $pdf->SetFont('DejaVu', 'B', 12);
        $pdf->write_cell(190, 12, $this->str_title, 1);
        $pdf->AliasNbPages();

        $nb_row=count($this->anc_acc_list->arow);
        $a_size=array("po_name"=>60, "po_description"=>100, "po_amount"=>30);
        $pdf->line_new(20);
        $pdf->SetFont('DejaVu', '', 7);
        $tot_card=0; $tot_glob=0;
        for ($i=0; $i<$nb_row; $i++)
        {
            $fill=$pdf->is_fill($i+1);
            if ($i==0)
            {
                $prev=$this->get_previous($i);
                $this->put_title($pdf, $i);
                $pdf->SetFont('DejaVu', '', 7);
            }
            if ($prev!=$this->get_previous($i))
            {
                $prev=$this->get_previous($i);
                $this->put_total($a_size,$pdf,$tot_card);
                
                $this->put_title($pdf, $i);
                $pdf->SetFont('DejaVu', '', 7);
                $tot_card=0;
            }
            $this->anc_acc_list->arow[$i]['sum_amount']=($this->anc_acc_list->arow[$i]['sum_amount']=="")?0:
                    $this->anc_acc_list->arow[$i]['sum_amount'];

            $tot_card=bcadd($tot_card, $this->anc_acc_list->arow[$i]['sum_amount'], 2);
            $tot_glob=bcadd($tot_glob, $this->anc_acc_list->arow[$i]['sum_amount'], 2);
            if ($this->anc_acc_list->card_poste<3)
            {
                $pdf->write_cell($a_size['po_name'], 4, $this->anc_acc_list->arow[$i]['po_name'], 0, 0, 'L', $fill);
                $pdf->write_cell($a_size['po_description'], 4, $this->anc_acc_list->arow[$i]['po_description'], 0, 0,
                        'L', $fill);
                $pdf->write_cell($a_size['po_amount'], 4, nbm($this->anc_acc_list->arow[$i]['sum_amount'], 2), 0, 0,
                        'R', $fill);
            }
            elseif ($this->anc_acc_list->card_poste==3)
            {
                $pdf->write_cell($a_size['po_name'], 4, $this->anc_acc_list->arow[$i]['j_qcode'], 0, 0, 'L', $fill);
                $pdf->write_cell($a_size['po_description'], 4, $this->anc_acc_list->arow[$i]['name'], 0, 0, 'L', $fill);
                $pdf->write_cell($a_size['po_amount'], 4, nbm($this->anc_acc_list->arow[$i]['sum_amount'], 2), 0, 0,
                        'R', $fill);
            }
            elseif ($this->anc_acc_list->card_poste==4)
            {
                $pdf->write_cell($a_size['po_name'], 4, $this->anc_acc_list->arow[$i]['j_poste'], 0, 0, 'L', $fill);
                $pdf->write_cell($a_size['po_description'], 4, $this->anc_acc_list->arow[$i]['name'], 0, 0, 'L', $fill);
                $pdf->write_cell($a_size['po_amount'], 4, nbm($this->anc_acc_list->arow[$i]['sum_amount'], 2), 0, 0,
                        'R', $fill);
            }
            $pdf->line_new();
        }
        $this->put_total($a_size,$pdf,$tot_card);
        $pdf->SetFont('DejaVu', '', 7);
        
        $pdf->line_new(5);
        $pdf->write_cell($a_size['po_name']+$a_size['po_description'], 10, _("Total Global"),'LBT');
        $pdf->write_cell($a_size['po_amount'], 10, nbm($tot_glob, 2), 'RBT', 0, 'R');
        $pdf->line_new();
        return $pdf;
    }

    public function get_a_size()
    {
        return $this->a_size;
    }

    public function set_a_size($a_size): PDF_Anc_Acc_List
    {
        $this->a_size=$a_size;
        return $this;
    }

    public function get_anc_acc_list()
    {
        return $this->anc_acc_list;
    }

    public function get_str_title()
    {
        return $this->str_title;
    }

    public function set_anc_acc_list($anc_acc_list): PDF_Anc_Acc_List
    {
        $this->anc_acc_list=$anc_acc_list;
        return $this;
    }

    public function set_str_title($str_title): PDF_Anc_Acc_List
    {
        $this->str_title=$str_title;
        return $this;
    }
    /**
     * @brief Main function , export the ANCBCC list to PDF, crossed by activity , account or card
     * @return type
     * @throws Exception
     */
    function export_pdf()
    {

        if ($this->anc_acc_list->check()!=0)
        {
            throw new Exception(_("date invalide"));
        }
        switch ($this->anc_acc_list->card_poste)
        {
            case 1:
                // Card  - Acc
                $this->anc_acc_list->load_card();
                return $this->pdf_card();
                break;
            case 2:
                // Accountancy - Analytic
                $this->anc_acc_list->load_poste();
                return $this->pdf_card();
                break;
            case 3:
                // Acc after card
                $this->anc_acc_list->load_anc_card();
                return $this->pdf_card();
                break;
            case 4:
                // Analytic - Accountancy
                $this->anc_acc_list->load_anc_account();
                return $this->pdf_card();
                break;
            default:
                throw new Exception('AAL700:unknown export');
        }
        return;
    }

}
