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

/**
 * @file
 * @brief Compute , display and export the tax summary
 *
 */
class Tax_Summary
{
    private $date_start;
    private $date_end;
    private $db;

    function __construct(Database $db,$p_start, $p_end)
    {
        $this->db=$db;
        $this->set_date_start($p_start);
        $this->set_date_end($p_end);

    }

    /**
     * @return Database
     */
    public function get_db()
    {
        return $this->db;
        return $this;
    }

    /**
     * @param Database $db
     */
    public function set_db($db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_date_start()
    {
        return $this->date_start;
        return $this;
    }

    /**
     * @param mixed $date_start
     */
    public function set_date_start($date_start)
    {
        if (isDate($date_start) == NULL) throw Exception(_("Format date invalide") . $date_start);
        $this->date_start = $date_start;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_date_end()
    {
        return $this->date_end;
        return $this;
    }

    /**
     * @param mixed $date_end
     */
    public function set_date_end($date_end)
    {
        if (isDate($date_end) == NULL) throw Exception(_("Format date invalide") . $date_end);
        $this->date_end = $date_end;
        return $this;
    }

    /**
     * @brief depends of quant_* table, so we must check first that everything
     * is in these tables
     */
    function check()
    {
       /*-------------SALE ---------------------------------*/
        $sql="select count(*) 
             from 
                jrnx 
                join quant_sold on (quant_sold.j_id = jrnx.j_id)
             where  
                   jrnx.j_jrn_def in (select jrn_def_id from jrn_def where jrn_def_type = 'VEN')
                    and j_date >= to_date($1,'DD.MM.YYYY') 
                    and j_date <= to_date($2,'DD.MM.YYYY') 
                  ";
        $cnt=$this->db->get_value($sql,[$this->date_start,$this->date_end]);
        if ($cnt == 0) {
            throw new Exception(_("Aucune Donnée"));
        }
        /*-------------Purchase ---------------------------------*/
        $sql="select count(*) 
             from 
                jrnx 
                join quant_purchase as qp on (jrnx.j_id = qp.j_id)
             where  
                   jrnx.j_jrn_def in (select jrn_def_id from jrn_def where jrn_def_type = 'ACH')
                    and j_date >= to_date($1,'DD.MM.YYYY') 
                    and j_date <= to_date($2,'DD.MM.YYYY') 
                             ";
        $cnt=$this->db->get_value($sql,[$this->date_start,$this->date_end]);
        if ($cnt == 0) {
            throw new Exception(_("Aucune Donnée"));

        }
    }

    /**
     *  Total for each sales ledger
     * @return array
     */
    function get_row_sale()
    {
        global $g_user;
        $sql_ledger=$g_user->get_ledger_sql('ALL',3);
        $sql="with detail_tva as (
                    select 
                        sum(qs_vat) as amount_vat,
                        sum(qs_vat_sided) as amount_sided,
                        sum(qs_price) as amount_wovat,
                        qs_vat_code,
                        j_jrn_def
                    from 
                        quant_sold 
                        join tva_rate on (qs_vat_code=tva_rate.tva_id) 
                        join jrnx on (quant_sold.j_id=jrnx.j_id)
                        join jrn_def on (jrn_def.jrn_def_id=jrnx.j_jrn_def)
                    where 
                        j_date >= to_date($1,'DD.MM.YYYY') 
                        and j_date <= to_date($2,'DD.MM.YYYY') 
                        and {$sql_ledger}
                    group by j_jrn_def,qs_vat_code)  
                    select jrn_def_name,
                        tva_label ,
                        qs_vat_code,
                          tva_rate,
                    tva_both_side,
                        amount_vat,
                        amount_wovat,
                        amount_sided
                    from
                        detail_tva 
                        join tva_rate on (tva_rate.tva_id=qs_vat_code)
                        join jrn_def on (jrn_def.jrn_def_id=j_jrn_def)
                    
                    order by jrn_def_name,tva_label";
        $array=$this->db->get_array($sql,[$this->date_start,$this->date_end]);
        return $array;

    }


    /**
     * Total for each purchase ledger
     * @return array
     */
    function get_row_purchase()
    {
        global $g_user;
        $sql_ledger=$g_user->get_ledger_sql('ALL',3);
        $sql="with detail_tva as (
                select 
                    sum(qp_vat) as amount_vat,
                    sum(qp_vat_sided) as amount_sided,
                    sum(qp_price) as amount_wovat,
                    sum(qp_nd_amount) as amount_noded_amount,
                    sum(qp_nd_tva) as amount_noded_tax,
                    sum(qp_nd_tva_recup) as amount_noded_return,
                    sum(qp_dep_priv) as amount_private,
                    qp_vat_code,
                    j_jrn_def
                from 
                    quant_purchase 
                    join tva_rate on (qp_vat_code=tva_rate.tva_id) 
                    join jrnx on (quant_purchase.j_id=jrnx.j_id)
                    join jrn_def on (jrn_def.jrn_def_id=jrnx.j_jrn_def)
                where 
                    j_date >= to_date($1,'DD.MM.YYYY') 
                    and j_date <= to_date($2,'DD.MM.YYYY') 
                    and {$sql_ledger}
                group by j_jrn_def,qp_vat_code)  
                select jrn_def_name,
                    tva_label ,
                    tva_rate,
                    tva_both_side,
                    qp_vat_code,
                    amount_vat,
                    amount_wovat,
                    amount_sided,
                    amount_noded_amount,
                    amount_noded_tax,
                    amount_noded_return,
                    amount_private
                from
                    detail_tva 
                    join tva_rate on (tva_rate.tva_id=qp_vat_code)
                    join jrn_def on (jrn_def.jrn_def_id=j_jrn_def)
                order by jrn_def_name,tva_label";
        $array=$this->db->get_array($sql,[$this->date_start,$this->date_end]);
        return $array;
    }

    /**
     * Summary for all sales ledger
     */
    function get_summary_sale()
    {
        global $g_user;
        $sql_ledger=$g_user->get_ledger_sql('ALL',3);
        $sql="with detail_tva as (
                    select 
                        sum(qs_vat) as amount_vat,
                        sum(qs_vat_sided) as amount_sided,
                        sum(qs_price) as amount_wovat,
                        qs_vat_code
                    from 
                        quant_sold 
                        join tva_rate on (qs_vat_code=tva_rate.tva_id) 
                        join jrnx on (quant_sold.j_id=jrnx.j_id)
                        join jrn_def on (jrn_def.jrn_def_id=jrnx.j_jrn_def)
                    where 
                        j_date >= to_date($1,'DD.MM.YYYY') 
                        and j_date <= to_date($2,'DD.MM.YYYY') 
                         and {$sql_ledger}
                    group by qs_vat_code)  
                    select 
                        tva_label ,
                        qs_vat_code,
                          tva_rate,
                    tva_both_side,
                        amount_vat,
                        amount_wovat,
                        amount_sided
                    from
                        detail_tva 
                        join tva_rate on (tva_rate.tva_id=qs_vat_code)
                    order by tva_label";
        $array=$this->db->get_array($sql,[$this->date_start,$this->date_end]);
        return $array;
    }
    /**
     * Summary for all purchase ledger
     */
    function get_summary_purchase()
    {
        global $g_user;
        $sql_ledger=$g_user->get_ledger_sql('ALL',3);
        $sql="with detail_tva as (
                select 
                    sum(qp_vat) as amount_vat,
                    sum(qp_vat_sided) as amount_sided,
                    sum(qp_price) as amount_wovat,
                    sum(qp_nd_amount) as amount_noded_amount,
                    sum(qp_nd_tva) as amount_noded_tax,
                    sum(qp_nd_tva_recup) as amount_noded_return,
                    sum(qp_dep_priv) as amount_private,
                    qp_vat_code
                from 
                    quant_purchase 
                    join tva_rate on (qp_vat_code=tva_rate.tva_id) 
                    join jrnx on (quant_purchase.j_id=jrnx.j_id)
                    join jrn_def on (jrn_def.jrn_def_id=jrnx.j_jrn_def)
                where 
                    j_date >= to_date($1,'DD.MM.YYYY') 
                    and j_date <= to_date($2,'DD.MM.YYYY') 
                     and {$sql_ledger}
                group by qp_vat_code)  
                select 
                    tva_label ,
                    tva_rate,
                    tva_both_side,
                    qp_vat_code,
                    amount_vat,
                    amount_wovat,
                    amount_sided,
                    amount_noded_amount,
                    amount_noded_tax,
                    amount_noded_return,
                    amount_private
                from
                    detail_tva 
                    join tva_rate on (tva_rate.tva_id=qp_vat_code)
                order by tva_label";
        $array=$this->db->get_array($sql,[$this->date_start,$this->date_end]);

        return $array;
    }
    /**
     * @brief display the summary of VAT in the range of date
     */
    function display()
    {
        require_once NOALYSS_INCLUDE."/template/tax_summary_display.php";
    }
    /**
     * @brief display a form to export in CSV
     * @see export_printtva_csv.php
     */
    function form_export_csv()
    {
        echo '<form method="GET" action="export.php">';
        echo Dossier::hidden();
        echo HtmlInput::hidden("act",'CSV:printtva');
        echo HtmlInput::hidden("date_start",$this->date_start);
        echo HtmlInput::hidden("date_end",$this->date_end);

        echo HtmlInput::submit("CSV:printtva",_("Export CSV"));
        echo '</form>';
    }

    /**
     * @brief display a form to export in PDF
     * @see export_printtva_pdf.php
     */
    function form_export_pdf()
    {
        echo '<form method="GET" action="export.php">';
        echo Dossier::hidden();
        echo HtmlInput::hidden("act",'PDF:printtva');
        echo HtmlInput::hidden("date_start",$this->date_start);
        echo HtmlInput::hidden("date_end",$this->date_end);

        echo HtmlInput::submit("PDF:printtva",_("Export PDF"));
        echo '</form>';
    }


}
