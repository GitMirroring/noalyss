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
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

/**
 * @file
 * @brief Acc_Ledger_History : Manage the list (history) of operations for display
 */
require_once NOALYSS_INCLUDE."/class/acc_ledger_history.class.php";

/**
 * @brief Acc_Ledger_History : Manage the list (history) of operations for display
 */
class Acc_Ledger_History_Sale extends Acc_Ledger_History
{

    private $data; //!< Contains rows from SQL

    public function __construct(\Database $cn, $pa_ledger, $p_from, $p_to,
            $p_mode)
    {
        parent::__construct($cn, $pa_ledger, $p_from, $p_to, $p_mode);
    }
    /**
     * Display the operation of sales with detailled VAT
     */
    public function export_detail_html()
    {
        $own=new Noalyss_Parameter_Folder($this->db);

        $this->get_row();
        $this->add_vat_info();
        $this->prepare_reconcile_date();
        include NOALYSS_TEMPLATE."/acc_ledger_history_sale_detail.php";
    }
    /**
     * @brief display the accounting 
     */
    public function export_accounting_html()
    {
        $ledger_history=new Acc_Ledger_History_Generic($this->db,
                $this->ma_ledger, $this->m_from, $this->m_to, $this->m_mode);
        $ledger_history->export_accounting_html();
    }
    /**
     * display the operation with detailled vat per item
     */
    public function export_extended_html()
    {
       $this->get_row();
       $this->add_vat_info();
       $this->prepare_detail();
       include NOALYSS_TEMPLATE."/acc_ledger_history_sale_extended.php";
    }
    /**
     * Prepare the query for fetching detail of an operation
     */
    private function prepare_detail()
    {
        
        if ( $this->db->is_prepare("detail_sale")== FALSE) 
        {
            $this->db->prepare("detail_sale","
                with card_name as 
                (select f_id,ad_value as name 
                    from fiche_detail where ad_id=1),
                card_qcode as  
                (select f_id,ad_value as qcode 
                from fiche_detail where ad_id=23)
                select 	qs_price,qs_quantite,qs_vat,qs_vat_code,qs_unit,qs_vat_sided,name,qcode,tva_label,
                qs_price+qs_vat-qs_vat_sided as tvac
                from 
                    quant_sold
                    join jrnx using (j_id)              
                    join card_name on (card_name.f_id=qs_fiche)
                    join card_qcode on (card_qcode.f_id=qs_fiche)
                    join tva_rate on ( qs_vat_code=tva_id)
                where
                    qs_internal=$1
                
            ");
        }
    }
    /**
     * Get the rows from jrnx and quant* tables
     * @param int $p_limit max of rows to returns
     * @param int $p_offset the number of rows to skip
     */
    public function get_row($p_limit=-1, $p_offset="")
    {
        $periode=sql_filter_per($this->db, $this->m_from, $this->m_to, 'p_id',
                'jr_tech_per');

        $cond_limite=($p_limit!=-1)?" limit ".$p_limit." offset ".$p_offset:"";

        $ledger_list=join(",", $this->ma_ledger);
        $sql="
            with row_sale as 
                (select qs_internal,
                    qs_client,sum(qs_price) as novat,
                    sum(qs_vat) as vat ,
                    sum(qs_vat_sided) as tva_sided 
                 from 
                    quant_sold group by qs_client,qs_internal),
              client_detail as (
              select x.f_id as f_id,
                (select ad_value from fiche_detail where ad_id=1 and f_id=x.f_id) as name,
                (select ad_value from fiche_detail where ad_id=32 and f_id=x.f_id) as first_name,
                (select ad_value from fiche_detail where ad_id=23 and f_id=x.f_id) as qcode
              from 
              fiche as x)
            select   
                    name,
                    first_name,
                    qcode,
                    jr_id,
                    jr_pj_number,
                    jr_date,
                    jr_date_paid,
                    jr_internal,
                    qs_client,
                    jrn.jr_comment,
                    jr_pj_name,
                    vat,
                    tva_sided,
                    novat,
                    novat+vat-tva_sided as tvac
            from
                jrn
                join row_sale on (qs_internal=jr_internal)
                join client_detail on (qs_client=f_id)
            where
                jr_def_id in ({$ledger_list})
                and {$periode}
                {$cond_limite}";
        $this->data=$this->db->get_array($sql);
        
    }
    /**
     * @brief preprare the query for fetching the detailed VAT of an operation
     * @staticvar int $prepare
     */
    private function add_vat_info()
    {
         $prepare=$this->db->is_prepare("vat_info");
        if ( $prepare==FALSE) {
            $this->db->prepare("vat_info","
                select 
                    sum(qs_vat) vat_amount , 
                    qs_vat_code 
                from 
                    quant_sold 
                where 
                    qs_internal = $1 
                group by qs_vat_code,qs_internal order by qs_vat_code");
             
        }
        
        $nb_row=count($this->data);
        for ($i=0;$i<$nb_row;$i++)
        {
            $ret=$this->db->execute("vat_info",array($this->data[$i]["jr_internal"]));
            $array=Database::fetch_all($ret);
            $this->data[$i]["detail_vat"]=$array;
        }
    }
    /**
     * @brief display in HTML following the mode 
     */
    function export_html()
    {
        switch ($this->m_mode)
        {
            case "E":
                $this->export_extended_html();
                break;
            case "D":
                $this->export_detail_html();
                break;
            case "L":
                $this->export_oneline_html();
                break;
            case "A":
                $this->export_accounting_html();
                break;
            default:
                break;
        }
    }
   
    /**
     * display in HTML one operation by line
     */
    public function export_oneline_html()
    {
        $this->get_row();
        $this->prepare_reconcile_date();
        require_once NOALYSS_TEMPLATE.'/acc_ledger_history_sale_oneline.php';
        
    }

}
