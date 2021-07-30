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

/*!\file
 * \brief API for creating PDF, unicode, based on tfpdf
 *@see TFPDF
 */
/*!
 * \class PDF
 * \brief API for creating PDF, unicode, based on tfpdf
 *@see TFPDF
 */


class PDF extends  PDF_Core
{
    var $cn  = null;
    var $own = null;
    var $soc = "";
    var $dossier =  "n/a";
    var $date = "";

    function __construct(Database $p_cn , $orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        $this->cn  = $p_cn;

        $this->own = new Noalyss_Parameter_Folder($this->cn);
        $this->soc = $this->own->MY_NAME;
        $this->date = date('d.m.Y');
        parent::__construct($orientation,$unit,$format);
        date_default_timezone_set ('Europe/Paris');
        $this->SetCreator("Noalyss");
    }
    function setDossierInfo($dossier = "n/a")
    {
        $this->dossier = dossier::name()." ".$dossier;
    }
    function Header()
    {
        //Arial bold 12
        $this->SetFont('DejaVu', 'B', 12);
        //Title
        parent::Cell(0,10,$this->dossier, 'B', 0, 'C');
        //Line break
        parent::Ln(20);
    }
    function Footer()
    {
        //Position at 2 cm from bottom
        $this->SetY(-20);
        //Arial italic 8
        $this->SetFont('Arial', '', 8);
        //Page number
        parent::Cell(0,8,'Date '.$this->date." - Page ".$this->PageNo().'/{nb}',0,0,'C');
        parent::Ln(3);
        // Created by NOALYSS
        parent::Cell(0,8,'Created by NOALYSS, online on http://www.noalyss.eu',0,0,'C',false,'http://www.noalyss.eu');
    }
    /**
     *@brief retrieve the client name and quick_code
     *@param $p_jr_id jrn.jr_id
     *@param $p_jrn_type ledger type ACH VEN FIN
     *@return array (0=>qcode,1=>name) or for FIN 0=>customer qc 1=>customer name 2=>bank qc 3=>bank name
     *@see class_print_ledger_simple, class_print_ledger_simple_without_vat
     */
    function get_tiers($p_jr_id,$p_jrn_type)
    {
        if ( $p_jrn_type=='ACH' )
        {
            $array=$this->cn->get_array('SELECT
                                        jrnx.j_grpt,
                                        quant_purchase.qp_supplier,
                                        quant_purchase.qp_internal,
                                        jrn.jr_internal
                                        FROM
                                        public.quant_purchase,
                                        public.jrnx,
                                        public.jrn
                                        WHERE
                                        quant_purchase.j_id = jrnx.j_id AND
                                        jrnx.j_grpt = jrn.jr_grpt_id and jr_id=$1',array($p_jr_id));
            if (count($array)==0) return array("ERREUR $p_jr_id",'');
            $customer_id=$array[0]['qp_supplier'];
            $fiche=new Fiche($this->cn,$customer_id);
            $customer_qc=$fiche->get_quick_code($customer_id);
            $customer_name=$fiche->getName();
            return array($customer_qc,$customer_name);
        }
        if ( $p_jrn_type=='VEN' )
        {
            $array=$this->cn->get_array('SELECT
                                        quant_sold.qs_client
                                        FROM
                                        public.quant_sold,
                                        public.jrnx,
                                        public.jrn
                                        WHERE
                                        quant_sold.j_id = jrnx.j_id AND
                                        jrnx.j_grpt = jrn.jr_grpt_id and jr_id=$1',array($p_jr_id));
            if (count($array)==0) return array("ERREUR $p_jr_id",'');
            $customer_id=$array[0]['qs_client'];
            $fiche=new Fiche($this->cn,$customer_id);
            $customer_qc=$fiche->get_quick_code($customer_id);
            $customer_name=$fiche->getName();
            return array($customer_qc,$customer_name);
        }
        if ( $p_jrn_type=='FIN' )
        {
            $array=$this->cn->get_array('SELECT
                                        qf_other,qf_bank
                                        FROM
                                        public.quant_fin
                                        WHERE
                                        quant_fin.jr_id =$1',array($p_jr_id));
            if (count($array)==0) return array("ERREUR $p_jr_id",'','','');
            $customer_id=$array[0]['qf_other'];
            $fiche=new Fiche($this->cn,$customer_id);
            $customer_qc=$fiche->get_quick_code($customer_id);
            $customer_name=$fiche->getName();

            $bank_id=$array[0]['qf_bank'];
            $fiche=new Fiche($this->cn,$bank_id);
            $bank_qc=$fiche->get_quick_code($bank_id);
            $bank_name=$fiche->getName();

            return array($customer_qc,$customer_name,$bank_qc,$bank_name);
        }
    }
    
    public function set_filter_operation($filter_operation)
    {
        if (in_array($filter_operation, ['all', 'paid', 'unpaid']))
        {
            $this->filter_operation=$filter_operation;
            return $this;
        }
        throw new Exception(_("Filter invalide ".$filter_operation), 5);
    }

}