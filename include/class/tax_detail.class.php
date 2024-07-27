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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 26/07/24
/*! 
 * \file
 * \brief detail of TVA_CODE for a specific ledger and periode
 */

class Tax_Detail
{
    protected $from; //!< Start date
    protected $to; //!< end date
    protected $tva_code; //!< tva_code
    protected $ledger_id; //!< ledger_id (jrn_def.jrn_def_id) -1, means all ledger Sale + Purchase

    function __construct($tva_code,$from,$to,$ledger_id) {

        $this->tva_code=$tva_code;
        $this->from=$from;
        $this->to=$to;
        $this->ledger_id=$ledger_id;

    }

    /**
     * @brief display a form for giving tva_code and dates
     * @return void
     */
    static function display_form()
    {
        require_once NOALYSS_TEMPLATE."/tax_detail-display_form.php";
    }

    /**
     * @brief get data
     */
    function get_data()
    {
        global $g_user,$cn;
        $filter_ledger=" where ";


        // Security
        if ($g_user->get_status_security_ledger()==1 && $g_user->isAdmin()==0)  {
            $filter_ledger.=$g_user->get_ledger_sql('ALL')."   and ";
        }

        // filter on the date
        $filter_ledger.="  jr_date >= to_date ($1,'DD.MM.YYYY') and jr_date <= to_date($2,'DD.MM.YYYY')";

        // SQL index of array for  array used in DatabaseCore::get_array
        $param_idx=3;
        $aParameter=array($this->from,$this->to);

        // filter on vat_code
        if ( !empty($this->tva_code ) )
        {
            $acc_tva=Acc_Tva::build($cn, $this->tva_code);
            $filter_ledger.=" and tva_opid = \$$param_idx ";
            $aParameter[]=$acc_tva->tva_id;
            $param_idx++;
        }
        // filter on the ledger
        if ( $this->ledger_id <> -1 ) {
            $filter_ledger.= " and jr_def_id = \$$param_idx";
            $param_idx++;
            $aParameter[]=$this->ledger_id;

        }
        $sql="
with v_amount_tva as (select 
                        f_id
                        ,j_qcode
                        ,case when j_debit is true then 0-j_montant else j_montant end j_montant
                     , qp_vat_code tva_opid
                     ,  0-qp_nd_tva  qp_nd_tva
                     ,  0-qp_nd_tva_recup  qp_nd_tva_recup
                     ,  0-qp_dep_priv  qp_dep_priv
                     , qp_vat_sided
                     , j_poste
                     , j_debit
                     , j_text
                     , jr2.jr_id
                     , jr2.jr_pj_number
                     , jr2.jr_internal
                    ,jr2.jr_date
                    ,to_char(jr2.jr_date,'DD.MM.YY') str_date
                    ,jr_def_id
                    ,0-qp_vat vat_amount
                from jrnx jr1
                join jrn jr2 on (jr1.j_grpt = jr2.jr_grpt_id) 
                 join  quant_purchase q1  	using (j_id)
                union all 
                select f_id
                        ,j_qcode
                        ,case when j_debit is true then 0-j_montant else j_montant end
                     , qs_vat_code
                     , 0
                     , 0
                     , 0
                     , 0
                     , j_poste
                     , j_debit
                     , j_text
                     , jr4.jr_id
                     , jr4.jr_pj_number
                     , jr4.jr_internal
                    ,jr4.jr_date
                    ,to_char(jr4.jr_date,'DD.MM.YY')
                    ,jr_def_id
                    ,qs_vat
                from  jrnx jr3 
                join jrn jr4 on (jr3.j_grpt = jr4.jr_grpt_id)
                 join quant_sold qs using (j_id)
                )
select *, tva_label,format ('%s (%s)',t1.tva_code ,t1.tva_label) tva_code,tva_rate
from v_amount_tva v1
join tva_rate t1 on (v1.tva_opid=t1.tva_id)
$filter_ledger      
order by jr_date,j_debit
        ";
        $data=$cn->get_array($sql,$aParameter);
        return $data;
    }
    /**
     * @brief display the result in HTML
     * @return void
     */
    function html() {
        global $data;
        $data=$this->get_data();
        require NOALYSS_TEMPLATE."/tax_detail-html.php";
    }
    function button_export_csv()
    {
        require NOALYSS_TEMPLATE."/tax_detail-button_export_csv.php";
    }

    /**
     * @brief export the result in a CSV file
     */
    function csv() {
        $noalyss_csv=new Noalyss_Csv(sprintf("tax_detail-{$this->tva_code}-{$this->from}-{$this->to}"));
        $data=$this->get_data();

        $header=["date",'piece',"fiche","poste","base","privé","code tva","taux","montant tva","non deductible","recup"];
        $noalyss_csv->send_header();
        $noalyss_csv->write_header($header  );
        foreach ($data as $item) {

            $noalyss_csv->add($item['str_date']);
            $noalyss_csv->add($item['jr_pj_number']);
            $noalyss_csv->add($item['jr_internal']);
            $noalyss_csv->add($item['j_qcode']);
            $noalyss_csv->add($item['j_poste']);
            $noalyss_csv->add(nb($item['j_montant'],2),"number");
            $noalyss_csv->add(nb($item['qp_dep_priv'],2),"number");
            $noalyss_csv->add($item['tva_code']);
            $noalyss_csv->add(nb($item['tva_rate'],2),"number");
            $noalyss_csv->add(nb($item['vat_amount'],2),"number");
            $noalyss_csv->add(nb($item['qp_nd_tva'],2),"number");
            $noalyss_csv->add(nb($item['qp_nd_tva_recup'],2),"number");
            $noalyss_csv->write();

        }
    }
}