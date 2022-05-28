<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
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
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

/**
 * @file
 * @brief Acc_Ledger_History : Manage the list (history) of operations for display
 * display or export operations in HTML , PDF or CSV 
 */

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
        $this->filter_operation='all';
        $this->ledger_type='VEN';
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
       $this->add_additional_tax_info();
       $this->prepare_detail();
       $this->prepare_reconcile_date();
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
                qs_price+qs_vat-qs_vat_sided as tvac,
                oc_amount,
                oc_vat_amount
                from 
                    quant_sold
                    join jrnx using (j_id)              
                    join card_name on (card_name.f_id=qs_fiche)
                    join card_qcode on (card_qcode.f_id=qs_fiche)
                    left join tva_rate on ( qs_vat_code=tva_id)
                    left join operation_currency using (j_id)
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
        
        $sql_filter=$this->build_filter_operation();
        
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
              fiche as x),
              row_currency as (
                select sum(oc_amount) as sum_oc_amount,sum(oc_vat_amount) as sum_oc_vat_amount,jrnx.j_grpt
                from 
                    operation_currency
                    join jrnx using (j_id)
                     join quant_sold  using (j_id)
                group by j_grpt
              ),
              other_tax as (select sum(case when j_debit is false 
                            then j_montant else 0-j_montant end) as other_tax_amount,
                            j_grpt 
              	from jrnx j1
              	join jrn_tax jt2 on (j1.j_id=jt2.j_id) group by j_grpt)
            select   
                    name,
                    first_name,
                    qcode,
                    jr_id,
                    jr_pj_number,
                    to_char(jr_date,'DD.MM.YYYY') as str_date,
                    to_char(jr_date_paid,'DD.MM.YYYY') as str_date_paid,
                    jr_internal,
                    qs_client,
                    jrn.jr_comment,
                    jr_pj_name,
                    vat,
                    tva_sided,
                    novat,
                    novat+vat-tva_sided as tvac,
                      to_char(jr_date,'DDMMYY') as str_date_short,
                    jr_grpt_id,
                    jrn.currency_id,
                    jrn.currency_rate,
                    jrn.currency_rate_ref,
                    sum_oc_amount,
                    sum_oc_vat_amount,
                    cr_code_iso,
                    coalesce (other_tax_amount,0) other_tax_amount
            from
                jrn
                join row_sale on (qs_internal=jr_internal)
                join client_detail on (qs_client=f_id)
                left join row_currency as rc on (rc.j_grpt = jrn.jr_grpt_id)
                left join currency as c on (c.id=jrn.currency_id)
                 left join other_tax as ot on (ot.j_grpt=jrn.jr_grpt_id)
            where
                jr_def_id in ({$ledger_list})
                {$sql_filter}
                and {$periode}
                {$cond_limite}
                     order by jr_date, substring(jr_pj_number,'[0-9]+$')::numeric ";
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
                group by qs_vat_code order by qs_vat_code");
             
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
        $nb_other_tax=$this->has_other_tax();
        require_once NOALYSS_TEMPLATE.'/acc_ledger_history_sale_oneline.php';
        
    }
    /**
     * To get data
     * @return array of rows
     */
    function get_data()
    {
        return $this->data;
    }
    function set_data($p_data)
    {
        $this->data=$p_data;
        return $this;
    }
    /**
     * export in csv with detail  VAT
     */
    function export_csv()
    {
        $export=new Noalyss_Csv(_('journal'));
        $export->send_header();
        $nb_other_tax=$this->has_other_tax();

        $this->get_row();
        $this->prepare_reconcile_date();
        $this->add_vat_info();
                
        $own=new Noalyss_Parameter_Folder($this->db);
        $title=array();
        $title[]=_('Date');
        $title[]=_("Paiement");
        $title[]=_("operation");
        $title[]=_("Pièce");
        $title[]=_("Fournisseur");
        $title[]=_("Note");
        $title[]=_("interne");
        $title[]=_("HTVA");
        $title[]=_("TVA");
        $title[]=_("TVA annulée");

        if ( $own->MY_TVA_USE=='Y')
        {
            $a_Tva=$this->db->get_array("select tva_id,tva_label from tva_rate order by tva_rate,tva_label,tva_id");
            foreach($a_Tva as $line_tva)
            {
                $title[]="Tva ".$line_tva['tva_label'];
            }
        }
        if ($nb_other_tax>0) {
            $title[]=_("Autre tx");
        }
        $title[]=_("TVAC/TTC");
        $title[]=_("Devise");
        $title[]=_("Devise HTVA");
        $title[]=_("Devise TVA");
        $title[]=_("Taux ref");
        $title[]=_("Taux utilisé");
       $title[]=_("Date paiement");
        $title[]=_("Code paiement");
        $title[]=_("Montant paiement");
        $title[]=_("n° opération");

        $export->write_header($title);
        
        foreach ($this->data as $line)
        {
            $export->add($line['str_date']);
            $export->add($line['str_date_paid']);
            $export->add($line['jr_id']);
            $export->add($line['jr_pj_number']);
            $export->add($line['name']." ".
                         $line["first_name"]." ".
                         $line["qcode"]); // qp_supplier
            $export->add($line['jr_comment']);
            $export->add($line['jr_internal']);
            $export->add($line['novat'],"number");
            $export->add($line['vat'],"number");
            $export->add($line['tva_sided'],"number");
            
            $a_tva_amount=array();
           
            if ($own->MY_TVA_USE == 'Y' )
            {
                 //- set all TVA to 0
                foreach ($a_Tva as $l) {
                    $t_id=$l["tva_id"];
                    $a_tva_amount[$t_id]=0;
                }
                foreach ($line['detail_vat'] as $lineTVA)
                {
                    $idx_tva=$lineTVA['qs_vat_code'];
                    $a_tva_amount[$idx_tva]=$lineTVA['vat_amount'];
                 }
                foreach ($a_Tva as $line_tva)
                {
                    $a=$line_tva['tva_id'];
                    $export->add($a_tva_amount[$a],"number");
                }
            }
            if ( $nb_other_tax > 0)
            {
                $export->add($line['other_tax_amount'],"number");
            }
            $export->add(bcadd($line['other_tax_amount'],$line['tvac'],2),"number");
            /**
             * Add currency info
             */
            $export->add($line['cr_code_iso']);
            $export->add($line['sum_oc_amount'],'number');
            $export->add($line['sum_oc_vat_amount'],'number');
            $export->add($line['currency_rate'],'number');
            $export->add($line['currency_rate_ref'],'number');
            
            /**
             * Retrieve payment if any
             */
             $ret_reconcile=$this->db->execute('reconcile_date',array($line['jr_id']));
             $max=Database::num_row($ret_reconcile);
            if ($max > 0) {
                for ($e=0;$e<$max;$e++) {
                    $row=Database::fetch_array($ret_reconcile, $e);
                    $export->add($row['jr_date']);
                    $export->add($row['qcode_bank']);
                    $export->add($row['qcode_name']);
                    $export->add($row['jr_montant'],"number");
                    $export->add($row['jr_internal']);

                }
            }
	    $export->write();

        }
    }
}
