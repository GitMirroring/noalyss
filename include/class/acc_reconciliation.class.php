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
 * \file
 *   \brief class acc_reconciliation, this class is new and the code
 *   must use it
 *
 */

/** 
 * \brief new class for managing the reconciliation it must be used
 * instead of the function InsertRapt, ...
 *
 */
class Acc_Reconciliation {

    var $db;   /*!< database connection */
    var $jr_id;   /*!< jr_id */
    var $a_jrn; /*!< $a_jrn array of  ledgers id (JRN_DEF.JRN_DEF_ID) */
    var $start_day; /*!< $start_day  (text DD.MM.YYYY) first day */
    var $end_day;/*!< $end_day  (text DD.MM.YYYY) last day */

    /**
     * query for building the temporary table TEMP_TOTAL_OPERATION
     */
    const SQL_ALL_OPERATION_RECONCILIED = "
      with total_operation as (
	select 
		jn2.jr_id,coalesce(sum(qs_price+qs_vat-qs_vat_sided),0)+coalesce(sum(qp_price+qp_vat-qp_vat_sided+qp.qp_nd_tva + qp.qp_nd_tva_recup),0) sum_amount
	from 
		jrnx jx1 
		join jrn jn2 on (jn2.jr_grpt_id =jx1.j_grpt )
		left join quant_sold qs on (jx1.j_id=qs.j_id) 
		left join quant_purchase qp on (qp.j_id =jx1.j_id)
	group by jn2.jr_id
), all_operation as (select jr_id,jra_concerned from jrn_rapt union select jra_concerned,jr_id from jrn_rapt)
,tiers as ( 
	select j_id,qf_other tiers_id from quant_fin
	union
	select j_id,qs_client from quant_sold qs 
	union
	select j_id,qp_supplier from quant_purchase
)
select distinct
    jr1.jr_id jr1_jr_id
    ,ra1.jra_concerned ra1_jra_concerned 
    ,jr1.jr_date jr1_jr_date
    ,to_char(jr1.jr_date,'DD.MM.YY') as str_jr1_jr_date
    ,jr1.jr_comment jr1_jr_comment
    ,jr1.jr_internal jr1_jr_internal
    ,jr1.jr_montant jr1_jr_montant
    ,case when to1.sum_amount=0 then jr1.jr_montant else to1.sum_amount end to1_sum_amount
    ,jr1.jr_pj_number jr1_jr_pj_number
    ,jr1.jr_def_id jr1_jr_def_id
    ,jrn1.jrn_def_name jrn1_jrn_def_name
    ,jrn1.jrn_def_type jrn1_jrn_def_type
    ,jr2.jr_date jr2_jr_date
    ,to_char(jr2.jr_date,'DD.MM.YY') as str_jr2_jr_date
    ,jr2.jr_comment jr2_jr_comment
    ,jr2.jr_internal jr2_jr_internal
    ,jr2.jr_montant jr2_jr_montant
    ,to2.sum_amount to2_sum_amount
    ,jr2.jr_pj_number jr2_jr_pj_number
    ,jr2.jr_def_id jr2_jr_def_id
    ,jrn2.jrn_def_name jrn2_jrn_def_name
    ,jrn2.jrn_def_type jrn2_jrn_def_type
    ,t3.tiers_id
    ,(select fd1.ad_value from fiche_detail fd1 where fd1.ad_id=1 and fd1.f_id=t3.tiers_id) as tiers_name
        ,(select fd1.ad_value from fiche_detail fd1 where fd1.ad_id=23 and fd1.f_id=t3.tiers_id) as tiers_qcode
    ,t5.tiers_id tiers_id_2
    ,(select fd1.ad_value from fiche_detail fd1 where fd1.ad_id=1 and fd1.f_id=t5.tiers_id) as tiers_name_2
        ,(select fd1.ad_value from fiche_detail fd1 where fd1.ad_id=23 and fd1.f_id=t5.tiers_id) as tiers_qcode_2
from jrn jr1
join total_operation to1 on (to1.jr_id=jr1.jr_id)
join jrn_def jrn1 on (jrn1.jrn_def_id=jr1.jr_def_id)
join all_operation ra1 on (ra1.jra_concerned=jr1.jr_id or ra1.jr_id=jr1.jr_id)
join jrn jr2 on (ra1.jra_concerned =jr2.jr_id)
join total_operation to2 on (to2.jr_id=jr2.jr_id)
join jrn_def jrn2 on (jrn2.jrn_def_id=jr2.jr_def_id)
left join (select t2.tiers_id,j2.j_grpt from tiers t2 join jrnx j2 on (t2.j_id=j2.j_id) ) as t3 on (t3.j_grpt=jr1.jr_grpt_id )
left join (select t4.tiers_id,j2.j_grpt from tiers t4 join jrnx j2 on (t4.j_id=j2.j_id) ) as t5 on (t5.j_grpt=jr2.jr_grpt_id )
where 
FILTER_DATE
and LEDGER_FILTER1
and LEDGER_FILTER2
order by jr1.jr_date,jr1.jr_id  
    ";
    // Get the data to display
    const SQL_QUERY = "
with base_op as (select *
    from temp_total_operation tm1
    where tm1.jr1_jr_id = tm1.ra1_jra_concerned )
, depend_op as (select jr1_jr_id
    , sum(case when to2_sum_amount != 0 then to2_sum_amount else jr2_jr_montant end) depend_sum_amount
    ,count(*) depend_count
    from temp_total_operation tm1
    where tm1.jr1_jr_id != tm1.ra1_jra_concerned
    group by jr1_jr_id )
select *
from base_op bo1
join depend_op bs1 on (bo1.jr1_jr_id = bs1.jr1_jr_id)
";

    function __construct($cn) {
        $this->db = $cn;
        $this->jr_id = 0;
        $this->a_jrn = null;
    }

    function set_jr_id($jr_id) {
        $this->jr_id = $jr_id;
    }

    /** 
     * \brief return a widget of type js_concerned
     */

    function widget() {
        $wConcerned = new IConcerned();
        $wConcerned->extra = 0; // with 0 javascript search from e_amount... field (see javascript)

        return $wConcerned;
    }

    /**
     * \brief   Insert into jrn_rapt the concerned operations
     *
     * \param $jr_id2 (jrn.jr_id) => jrn_rapt.jra_concerned or a string
     * like "jr_id2,jr_id3,jr_id4..."
     *
     * \return none
     *
     */

    function insert($jr_id2) {
        if (trim($jr_id2) == "")
            return;
        if (strpos($jr_id2, ',') !== 0) {
            $aRapt = explode(',', $jr_id2);
            foreach ($aRapt as $rRapt) {
                if (isNumber($rRapt) == 1) {
                    $this->insert_rapt($rRapt);
                }
            }
        } else
        if (isNumber($jr_id2) == 1) {
            $this->insert_rapt($jr_id2);
        }
    }

    /**
     * \brief   Insert into jrn_rapt the concerned operations
     * should not  be called directly, use insert instead
     *
     * \param $jr_id2 (jrn.jr_id) => jrn_rapt.jra_concerned
     *
     * \return none
     *
     */

    function insert_rapt($jr_id2) {
        if (isNumber($this->jr_id) == 0 || isNumber($jr_id2) == 0) {
            return false;
        }
        if ($this->jr_id == $jr_id2)
            return true;

        if ($this->db->count_sql("select jr_id from jrn where jr_id=$1" ,[ $this->jr_id]) == 0)
            return false;
        if ($this->db->count_sql("select jr_id from jrn where jr_id=$1",[$jr_id2]) == 0)
            return false;

        // verify if exists
        if ($this->db->count_sql(
                        "select jra_id from jrn_rapt where jra_concerned=$1
                         and jr_id=$2
                    union
                    select jra_id from jrn_rapt where jr_id= $1
                         and jra_concerned=$2 " ,[$this->jr_id,$jr_id2]) == 0) {
            // Ok we can insert
            $Res = $this->db->exec_sql("insert into jrn_rapt(jr_id,jra_concerned) values ($1,$2)",
                    array($this->jr_id, $jr_id2)
            );
            // try to letter automatically same account from both operation
            $this->auto_letter($jr_id2);

            // update date of paiement -----------------------------------------------------------------------
            $source_type = $this->db->get_value("select substr(jr_internal,1,1) from jrn where jr_id=$1", array($this->jr_id));
            $dest_type = $this->db->get_value("select substr(jr_internal,1,1) from jrn where jr_id=$1", array($jr_id2));
            if (($source_type == 'A' || $source_type == 'V') && ($dest_type != 'A' && $dest_type != 'V')) {
                // set the date on source
                $date = $this->db->get_value('select jr_date from jrn where jr_id=$1', array($jr_id2));
                if (trim($date) == '')
                    $date = null;
                $this->db->exec_sql('update jrn set jr_date_paid=$1 where jr_id=$2 and jr_date_paid is null ', array($date, $this->jr_id));
            }
            if (($source_type != 'A' && $source_type != 'V') && ($dest_type == 'A' || $dest_type == 'V')) {
                // set the date on dest
                $date = $this->db->get_value('select jr_date from jrn where jr_id=$1', array($this->jr_id));
                if (trim($date) == '')
                    $date = null;
                $this->db->exec_sql('update jrn set jr_date_paid=$1 where jr_id=$2 and jr_date_paid is null ', array($date, $jr_id2));
            }
        }
        return true;
    }

    /**
     * @brief try to letter same card between $p_jrid and $this->jr_id
     * @param jrn.jr_id $p_jrid  the operation to reconcile
     */
    function auto_letter($p_jrid) {
        // Try to find same card from both operation
        $sql = "select j1.f_id as fiche ,coalesce(j1.j_id,-1) as jrnx_id1,coalesce(j2.j_id,-1) as jrnx_id2,
j1.j_poste as poste
            from jrnx as j1
                    join jrn as jr1 on (j1.j_grpt=jr1.jr_grpt_id)
                    join jrnx as j2 on (coalesce(j1.f_id,-1)=coalesce(j2.f_id,-1) and j1.j_poste=j2.j_poste)
                    join jrn as jr2 on (j2.j_grpt=jr2.jr_grpt_id)
            where
                    jr1.jr_id=$1
                    and
                    jr2.jr_id= $2";
        $result = $this->db->get_array($sql, array($this->jr_id, $p_jrid));
        if (count($result) == 0) {
            return;
        }
        for ($i = 0; $i < count($result); $i++) {
            if ($result[$i]['fiche'] != -1) {
                $letter = new Lettering_Card($this->db);
                $letter->insert_couple($result[$i]['jrnx_id1'], $result[$i]['jrnx_id2']);
            } else {
                $letter = new Lettering_Account($this->db);
                $letter->insert_couple($result[$i]['jrnx_id1'], $result[$i]['jrnx_id2']);
            }
        }
    }

    /**
     * \brief   Insert into jrn_rapt the concerned operations
     *
     * \param $this->jr_id (jrn.jr_id) => jrn_rapt.jr_id
     * \param $jr_id2 (jrn.jr_id) => jrn_rapt.jra_concerned
     *
     * \return none
     */

    function remove($jr_id2) {
        if (isNumber($this->jr_id) == 0 or
                isNumber($jr_id2) == 0) {
            return;
        }
        // verify if exists
        if ($this->db->count_sql("select jra_id from jrn_rapt where " .
            " jra_concerned=" . $this->jr_id . "  and jr_id=$jr_id2
                      union
                      select jra_id from jrn_rapt where jra_concerned=$jr_id2 " .
            " and jr_id=" . $this->jr_id) != 0) {
            /**
             * remove also lettering between both operation
             */
            $sql = " 
delete from
    jnt_letter
where jl_id in ( select jl_id from jnt_letter
    join letter_cred as lc using(jl_id)
    join letter_deb as ld using (jl_id)
where
    lc.j_id in (select j_id
                            from jrnx join jrn on (j_grpt=jr_grpt_id)
                            where jr_id in ($1,$2))
    or
    ld.j_id in (select j_id
                            from jrnx join jrn on (j_grpt=jr_grpt_id)
                            where jr_id in ($1,$2))



							)";
            $this->db->exec_sql($sql, array($jr_id2, $this->jr_id));
            // Ok we can delete
            $Res = $this->db->exec_sql("delete from jrn_rapt where 
                (jra_concerned=$1 and jr_id= $2) or
                (jra_concerned=$2 and jr_id=$1) ",
                    [$jr_id2,$this->jr_id]);
        }
    }

    /**
     * \brief   Return an array of the concerned operation
     *
     *
     * \param database connection
     * \return array if something is found or null
     */

    function get() {
        $sql = " select jr_id as cn from jrn_rapt where jra_concerned=$1
              union 
              select jra_concerned as cn from jrn_rapt where jr_id=$2";
        $Res = $this->db->exec_sql($sql, array($this->jr_id, $this->jr_id));

        // If nothing is found return null
        $n = Database::num_row($Res);

        if ($n == 0)
            return [];

        // put everything in an array
        for ($i = 0; $i < $n; $i++) {
            $l = Database::fetch_array($Res, $i);
            $r[$i] = $l['cn'];
        }
        return $r;
    }

    /**
     * @deprecated since version 9307
     * @brief retrieve row from JRN
     * @return type
     */
    function fill_info() {
        $sql = "select jr_id,jr_date,jr_comment,jr_internal,jr_montant,jr_pj_number,jr_def_id,jrn_def_name,jrn_def_type
             from jrn join jrn_def on (jrn_def_id=jr_def_id)
             where jr_id=$1";
        $a = $this->db->get_array($sql, array($this->jr_id));
        return $a[0];
    }

    /**
     * @brief return array of not-reconciled operation
     * Prepare and put in memory the SQL detail_quant
     */
    function get_not_reconciled() {
       $this->build_temp_total_operation();
       $filter_date = $this->filter_date();
        /* create ledger filter */
        $sql_jrn = $this->ledger_filter();

        $array = $this->db->get_array("
                  with total_operation as (
	select 
		jn2.jr_id,coalesce(sum(qs_price+qs_vat-qs_vat_sided),0)+coalesce(sum(qp_price+qp_vat-qp_vat_sided+qp.qp_nd_tva + qp.qp_nd_tva_recup),0) sum_amount
	from 
		jrnx jx1 
		join jrn jn2 on (jn2.jr_grpt_id =jx1.j_grpt )
		left join quant_sold qs on (jx1.j_id=qs.j_id) 
		left join quant_purchase qp on (qp.j_id =jx1.j_id)
	group by jn2.jr_id)
,tiers as ( 
	select j_id,qf_other tiers_id from quant_fin
	union
	select j_id,qs_client from quant_sold qs 
	union
	select j_id,qp_supplier from quant_purchase
)
            select distinct
    jr1.jr_id jr1_jr_id
    ,null ra1_jra_concerned 
    ,jr1.jr_date jr1_jr_date
    ,to_char(jr1.jr_date,'DD.MM.YY') as str_jr1_jr_date
    ,jr1.jr_comment jr1_jr_comment
    ,jr1.jr_internal jr1_jr_internal
    ,jr1.jr_montant jr1_jr_montant
    ,case when to1.sum_amount=0 then jr1.jr_montant else to1.sum_amount end to1_sum_amount
    ,jr1.jr_pj_number jr1_jr_pj_number
    ,jr1.jr_def_id jr1_jr_def_id
    ,jrn1.jrn_def_name jrn1_jrn_def_name
    ,jrn1.jrn_def_type jrn1_jrn_def_type
    ,null jr2_jr_date
    ,null str_jr2_jr_date
    ,null jr2_jr_comment
    ,null jr2_jr_internal
    ,null jr2_jr_montant
    ,null to2_sum_amount
    ,null jr2_jr_pj_number
    ,null jr2_jr_def_id
    ,null jrn2_jrn_def_name
    ,null jrn2_jrn_def_type
    ,0 depend_count
    ,(select fd1.ad_value from fiche_detail fd1 where fd1.ad_id=1 and fd1.f_id=t3.tiers_id) as tiers_name
    ,(select fd1.ad_value from fiche_detail fd1 where fd1.ad_id=23 and fd1.f_id=t3.tiers_id) as tiers_qcode
from jrn jr1
join total_operation to1 on (to1.jr_id=jr1.jr_id)
join jrn_def jrn1 on (jrn1.jrn_def_id=jr1.jr_def_id)
left join (select t2.tiers_id,j2.j_grpt from tiers t2 join jrnx j2 on (t2.j_id=j2.j_id) ) as t3 on (t3.j_grpt=jr1.jr_grpt_id )
where 
    $filter_date 
    and $sql_jrn 
    and jr1.jr_id not in (select jr_id from jrn_rapt 
                        union select jra_concerned from jrn_rapt) 
    order by jr_date
");


       return $array;
    }

    /**
     * @brief Create a sql condition to filter by security and by asked ledger
     * based on $this->a_jrn
     * @return a valid sql stmt to include
     * @see get_not_reconciled get_reconciled
     */
    function ledger_filter() {
        global $g_user;
        /* get the available ledgers for current user */
        $sql = $g_user->get_ledger_sql('ALL', 3);
        $sql = noalyss_str_replace('jrn_def_id', 'jr_def_id', $sql);
        $r = '';
        /* filter by this->r_jrn */
        if (!empty($this->a_jrn) && is_array($this->a_jrn)) {
            $sep = '';
            $r = 'and jr_def_id in (';
            foreach ($this->a_jrn as $key => $value) {
                $r .= $sep . $value;
                $sep = ',';
            }
            $r .= ')';
        }
        return $sql . '  ' . $r;
    }

    /**
     * @brief build a temporary table with all operation + dependencies
     * @return type
     */
    function build_temp_total_operation() {
        static $done=false;
        if ( $done  ) {
            return;
        }
        global $g_user;
        $filter_date = str_replace("jr_date", "jr1.jr_date", $this->filter_date());

        /* create ledger filters */
        $sql_jrn = $this->ledger_filter();
        $sql_jrn1 = str_replace("jr_def_id", "jr1.jr_def_id", $sql_jrn);

        /* security on the ledger */
        $sql = $g_user->get_ledger_sql('ALL', 3);
        $sql_jrn2 = noalyss_str_replace('jrn_def_id', 'jr2.jr_def_id', $sql);

        $sql_string = Acc_Reconciliation::SQL_ALL_OPERATION_RECONCILIED;
        $sql_string = str_replace("FILTER_DATE", $filter_date, $sql_string);
        $sql_string = str_replace("LEDGER_FILTER1", $sql_jrn1, $sql_string);
        $sql_string = str_replace("LEDGER_FILTER2", $sql_jrn2, $sql_string);
        try {
            
            $this->db->exec_sql(" create temporary table temp_total_operation as $sql_string");
            $done=true;
        } catch (Exception $exc) {
            echo $exc->getMessage();
            return;
        }
    }

    /**
     * @brief return array of reconciled operation
     * Prepare and put in memory the SQL detail_quant
     * @return
     * @note
     * @see
      @code

      @endcode
     */
    function get_reconciled() {
        $this->build_temp_total_operation();
        $sql_amount = Acc_Reconciliation::SQL_QUERY;

        $a_row = $this->db->get_array("$sql_amount order by jr1_jr_date");
        return $a_row;
    }

    /**
     * @brief
     * Prepare and put in memory the SQL detail_quant
     * @param
     * @return
     * @note
     * @see
      @code

      @endcode
     */
    function get_reconciled_amount($p_equal = false) {
        // build temporary table temp_total_operation 
        $this->build_temp_total_operation();
        // SQL with different amount 
        $sql_amount = Acc_Reconciliation::SQL_QUERY;
        if ($p_equal) {
            $sql_amount = $sql_amount . " where bs1.depend_sum_amount = to1_sum_amount ";
        } else {
            $sql_amount = $sql_amount . " where bs1.depend_sum_amount != to1_sum_amount";
        }
        $a_row = $this->db->get_array("$sql_amount order by jr1_jr_date");
        return $a_row;
    }

    /**
     * @brief create a string to filter thanks the date
     * @return a sql string like jr_date > ... and jr_date < ....
     * @note use the data member start_day and end_day
     * @see get_reconciled get_not_reconciled
     */
    function filter_date() {
        global $g_user;
        $g_user->db=$this->db;
        list($start, $end) = $g_user->get_limit_current_exercice();

        if (isDate($this->start_day) == null) {
            $this->start_day = $start;
        }
        if (isDate($this->end_day) == null) {
            $this->end_day = $end;
        }
        $sql = " (jr_date >= to_date('" . $this->start_day . "','DD.MM.YYYY')
		and jr_date <= to_date('" . $this->end_day . "','DD.MM.YYYY'))";
        return $sql;
    }
    /**
     * @deprecated since version 9307
     */
    function show_detail($p_ret) {
        if (Database::num_row($p_ret) > 0) {
            echo '<tr >';
            echo '<td></td>';
            echo '<td colspan="5" style="border:1px solid black;width:auto">';
            include NOALYSS_TEMPLATE . '/impress_reconciliation_detail.php';
            echo '</td>';
            echo '</tr>';
        }
    }

    /**
     * @brief Export to CSV
     * @param type $p_choice 
     * 
     * @note must be set before calling
     *    - $this->a_jrn       array of ledger
     *    - $this->start_day start date
     *    - $this->end_day end date
     * @see Acc_Reconciliation::get_data
     */
    function export_csv($p_choice) {
        $export = new Noalyss_Csv(_('rapprochement'));
        $export->send_header();

        $array = $this->get_data($p_choice);
        for ($i = 0; $i < count($array); $i++) {
            if ( $i == 0)
            {
                $title[] = _('n°');
                $title[] = _('Date');
                $title[] = _('pièce');
                $title[] = _('internal');
                $title[] = _('Qcode');
                $title[] = _('Nom');
                $title[] = _('libellé');
                $title[] = _('journal');
                $title[] = _('type journal');
                $title[] = _('montant');
                $export->write_header($title);
            }
            $export->add($i, "number");
            $export->add($array[$i]['str_jr1_jr_date']);
            $export->add($array[$i]['jr1_jr_pj_number']);
            $export->add($array[$i]['jr1_jr_internal']);
            $export->add($array[$i]['tiers_qcode']);
            $export->add($array[$i]['tiers_name']);
            $export->add($array[$i]['jr1_jr_comment']);
            $export->add($array[$i]['jrn1_jrn_def_name']);
            $export->add($array[$i]['jrn1_jrn_def_type']);
             $x=($array[$i]['to1_sum_amount']!=0)?$array[$i]['to1_sum_amount']:$array[$i]['jr1_jr_montant'];
            $export->add($x, "number");
            $export->write();
            
             if ( $array[$i]['depend_count']>0) {
                $depend=$this->db->get_array("select * 
                        from temp_total_operation 
                        where 
                            jr1_jr_id=$1 and ra1_jra_concerned != jr1_jr_id"
                            ,[$array[$i]['jr1_jr_id']]);
                $nb_depend = count($depend);
                $totdepend=0;$delta=$x;
                for ($e = 0; $e < $nb_depend ; $e++) {
                    $x=($depend[$e]['to2_sum_amount']!=0)?$depend[$e]['to2_sum_amount']:$depend[$e]['jr2_jr_montant'];
                    $totdepend=bcadd($totdepend,$x,2);
                    $delta=bcsub($delta,$x,2);
                    $export->add($i, "number");
                    $export->add($depend[$e]["str_jr2_jr_date"]);
                    $export->add($depend[$e]["jr2_jr_internal"]);
                    $export->add($array[$i]['tiers_name_2']);
                    $export->add($array[$i]['tiers_qcode_2']);
                    $export->add($depend[$e]["jr2_jr_pj_number"]);
                    $export->add($depend[$e]["jr2_jr_comment"]);
                    $export->add($depend[$e]['jrn2_jrn_def_name']);
                    $export->add($depend[$e]['jrn2_jrn_def_type']);
                    $export->add($x, "number");
                    $export->write();
                }
                $export->add("Total");
                $export->add($totdepend,"number");
                $export->add("Différence");
                $export->add($delta,"number");
                $export->write();
                 
             }
            
            
        }
    }

    /**
     * @brief retrieve data
     * @param type $p_choice
     *       - 0 : operation reconcilied
     *       - 1 : reconcilied with different amount
     *       - 2 : reconcilied with same amount
     *       - 3 : not reconcilied 
     * @return $array
     */
    function get_data($p_choice) {
        switch ($p_choice) {
            case 0:
                $array = $this->get_reconciled();
                break;
            case 1:
                $array = $this->get_reconciled_amount(false);
                break;
            case 2:
                $array = $this->get_reconciled_amount(true);
                break;
            case 3:
                $array = $this->get_not_reconciled();
                break;
            default:
                echo "Choix invalid";
                throw new Exception("invalide");
        }
        return $array;
    }

    function prepare_query_detail_quant() {
        static $seen = 0;
        if ($seen == 1)
            return;
        $this->db->prepare('detail_quant', 'select * from v_quant_detail where jr_id=$1');
        // $this->db->prepare('detail_depend',' ');
        $seen = 1;
    }

    /**
     * @brief Retrieve the amount VAT included and autoreversed VAT excluded thanks
     * the view v_quant_detail and return it.
     * If the operation is not a sale or a purchase , it doesn't exist in the
     * view then the function just returns the default amount
     * @param type $p_jrn_id  jrn.jr_id
     * @param type $p_default_amount amount to return if not found in the view
     * v_quant_detail
     * @return number
     */
    function get_amount_noautovat($p_jrn_id, $p_default_amount) {
        static $p = 0;
        if ($p == 0) {
            $this->prepare_query_detail_quant();
            $p = 1;
        }
        bcscale(2);
        $retdb = $this->db->execute("detail_quant", array($p_jrn_id));
        $nb_record = Database::num_row($retdb);
        if ($nb_record > 0) {
            $total_price = $first_amount = 0;
            for ($i = 0; $i < $nb_record; $i++) {
                // then second_amount takes in account the vat_sided
                $row = Database::fetch_array($retdb, $i);
                $total_price = bcadd($row['price'], $row['vat_amount']);
                $total_price = bcsub($total_price, $row['vat_sided']);
                $total_price = bcadd($total_price, $row['nd_tva']);
                $total_price = bcadd($total_price, $row['nd_tva_recup']);
                $first_amount = bcadd($total_price, $first_amount);
            }
        } else {
            // else take the amount from jrn
            $first_amount = $p_default_amount;
        }
        return $first_amount;
    }

    static function test_me() {
        $cn = Dossier::connect();
        $rap = new Acc_Reconciliation($cn);
        var_dump($rap->get_reconciled_amount(false));
        $rap->build_temp_total_operation();
        $rap->build_temp_total_operation();
        $rap->build_temp_total_operation();
    }
}
