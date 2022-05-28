<?php

/*
 * Copyright (C) 2018 Dany De Bontridder <dany@alchimerys.be>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


/***
 * @file 
 * @brief display or export operations in HTML , PDF or CSV 
 *
 */
/**
 * @brief Display history of operation
 * @see acc_ledger_historyTest.php
 */
abstract class Acc_Ledger_History
{

    protected $m_from; //!< Starting Periode : periode.p_id
    protected $m_to;   //!< Ending Periode : periode.p_id
    protected $ma_ledger; //!< Array of ledger id : jrn_def.jrn_def_id
    protected $m_mode; //!< mode of export L : one line, E accounting writing , D : Detail
    public $db; //!< database connx
    protected $ledger_type; //! type of ledger VEN , ACH , ODS, FIN
    protected $filter_operation; //!< to filter paid, unpaid or all operation

    /**
     * 
     * @param Database $cn
     * @param array $pa_ledger array of jrn_def.jrn_def_id
     * @param integer $p_from periode 
     * @param integer $p_to
     * @param char $p_mode E D L or A , for Extended ,Detail , Listing , Accounting
     * @throws Exception if $pa_ledger is not an array
     */
    function __construct(Database $cn, $pa_ledger, $p_from, $p_to, $p_mode)
    {
        if (is_array($pa_ledger) == FALSE) {
            throw new Exception (_('pa_ledger doit être un tableau'),EXC_PARAM_VALUE);
        }
        $this->db=$cn;
        $this->ma_ledger=$pa_ledger;
        $this->m_from=$p_from;
        $this->m_to=$p_to;
        $this->m_mode=$p_mode;
        $this->filter_operation='all';
    }
    /**
     * setter / getter
     * @returns m_from (periode id)
     */
    public function get_from()
    {
        return $this->m_from;
    }
    public function get_ledger_type()
    {
        return $this->ledger_type;
    }

    public function set_ledger_type($ledger_type)
    {
        if (! in_array($ledger_type,['ACH','ODS','VEN','FIN'])) {
            record_log('Acc_Ledger_History:set_ledger_type '.var_export($ledger_type,TRUE));
            throw new Exception (_("Donnée invalide",EXC_PARAM_VALUE));
        }
        
        $this->ledger_type=$ledger_type;
        return $this;
    }

        /**
     * setter / getter
     * @returns m_to (periode id)
     */
    public function get_to()
    {
        return $this->m_to;
    }
    /**
     * setter / getter
     * @returns ma_ledger (array)
     */
    public function get_ledger()
    {
        return $this->ma_ledger;
    }
    /**
     * setter / getter
     * @returns m_mode (A,L,E,D)
     */
    public function get_mode()
    {
        return $this->m_mode;
    }
    /**
     * setter m_from (periode id)
     */
    public function set_from($m_from)
    {
        $this->m_from=$m_from;
        return $this;
    }
    /**
     * setter m_to (periode id)
     */
    public function set_to($m_to)
    {
        $this->m_to=$m_to;
        return $this;
    }
    /**
     * setter ma_ledger (array of jrn_def_id)
     */
    public function set_a_ledger($ma_ledger)
    {
        if (is_array($ma_ledger)==FALSE)
            throw new Exception(_("invalid parameter"), EXC_PARAM_VALUE);
        $this->ma_ledger=$ma_ledger;
        return $this;
    }
    /**
     * Setter
     * @param  $m_mode D,A,L,E
     * @return $this
     * @throws Exception
     */
    public function set_m_mode($m_mode)
    {
        if ($m_mode!='E'&&$m_mode!='D'&&$m_mode!='L'&&$m_mode!='A')
            throw new Exception(_("invalid parameter"), EXC_PARAM_VALUE);
        $this->m_mode=$m_mode;
        return $this;
    }

    /**
     * Build the right object 
     * @param Database $cn database conx
     * @param array $pa_ledger ledger of array
     * @param integer $p_from periode id
     * @param integer $p_to periode id
     * @param char $p_mode L (list operation) E (extended detail) A (accouting writing) D (Detailled VAT)
     * @param $p_paid values are all means all operations, paid only paid operation, unpaid for only unpaid
     * @return Acc_Ledger_History_Generic Acc_Ledger_History_Sale Acc_Ledger_History_Financial Acc_Ledger_History_Purchase
     */
    static function factory(Database $cn, $pa_ledger, $p_from, $p_to, $p_mode,$p_paid)
    {
        // For Accounting writing , we use Acc_Ledger_History
        if ($p_mode=="A")
        {
            $ret=new Acc_Ledger_History_Generic($cn, $pa_ledger, $p_from, $p_to,
                    $p_mode);
            return $ret;
        }
        $nb_ledger=count($pa_ledger);
        $ledger=new Acc_Ledger($cn, $pa_ledger[0]);
        $type=$ledger->get_type();

        // If first one is ODS so Acc_Ledger_History
        if ($type=="ODS")
        {
            $ret=new Acc_Ledger_History_Generic($cn, $pa_ledger, $p_from, $p_to,
                    $p_mode);
            return $ret;
        }
        // If all of the same type then use the corresponding class

        for ($i=0; $i<$nb_ledger; $i++)
        {
            $ledger=new Acc_Ledger($cn, $pa_ledger[$i]);
            $type_next=$ledger->get_type();

            // If type different then we go back to the generic
            if ($type_next!=$type)
            {
                $ret=new Acc_Ledger_History_Generic($cn, $pa_ledger, $p_from,
                        $p_to, $p_mode);
                return $ret;
            }
        }
        switch ($type)
        {
            case "ACH":
                $ret=new Acc_Ledger_History_Purchase($cn, $pa_ledger, $p_from,
                        $p_to, $p_mode);
                $ret->set_filter_operation($p_paid);
                return $ret;
                break;
            case "FIN":
                $ret=new Acc_Ledger_History_Financial($cn, $pa_ledger, $p_from,
                        $p_to, $p_mode);
                return $ret;
                break;
            case "VEN":
                $ret=new Acc_Ledger_History_Sale($cn, $pa_ledger, $p_from,
                        $p_to, $p_mode);
                $ret->set_filter_operation($p_paid);
                return $ret;
                break;

            default:
                break;
        }
    }

    /**
     * Retrieve the third : supplier for purchase, customer for sale, bank for fin,
     * @param $p_jrn_type type of the ledger FIN, VEN ACH or ODS
     * @param $jr_id jrn.jr_id
     * @todo duplicate function , also in Acc_Ledger::get_tiers, remove one
     */
    function get_tiers($p_jrn_type, $jr_id)
    {
        if ($p_jrn_type=='ODS')
            return ' ';
        $tiers=$this->get_tiers_id($p_jrn_type, $jr_id);
        if ($tiers==0)
            return "";

        $name=$this->db->get_value('select ad_value from fiche_detail where ad_id=1 and f_id=$1',
                array($tiers));
        $first_name=$this->db->get_value('select ad_value from fiche_detail where ad_id=32 and f_id=$1',
                array($tiers));
        return $name.' '.$first_name;
    }

    /**
     * @brief Return the f_id of the tiers , called by get_tiers
     * @param $p_jrn_type type of the ledger FIN, VEN ACH or ODS
     * @param $jr_id jrn.jr_id
    */
    function get_tiers_id($p_jrn_type, $jr_id)
    {
        $tiers=0;
        switch ($p_jrn_type)
        {
            case 'VEN':
                $tiers=$this->db->get_value('select max(qs_client) '
                        . 'from quant_sold join jrnx using (j_id) '
                        . 'join jrn on (jr_grpt_id=j_grpt) where jrn.jr_id=$1',
                        array($jr_id));
                break;
            case 'ACH':
                $tiers=$this->db->get_value('select max(qp_supplier) '
                        . ' from quant_purchase '
                        . ' join jrnx using (j_id) '
                        . ' join jrn on (jr_grpt_id=j_grpt) '
                        . ' where jrn.jr_id=$1',
                        array($jr_id));

                break;
            case 'FIN':
                $tiers=$this->db->get_value('select qf_other from quant_fin where jr_id=$1',
                        array($jr_id));
                break;
        }
        if ($this->db->count()==0)
            return 0;
        return $tiers;
    }

    
    /**
     * Prepare the query for fetching the linked operation
     * @staticvar int $prepare
     */
    protected function prepare_reconcile_date()
    {
        static $not_done=TRUE;
        if ($not_done )  {
            $prepare_query = new Prepared_Query($this->db);
            $prepare_query->prepare_reconcile_date();
        }
        $not_done=FALSE;
    }
    /**
     * display accounting of operations m_mode=A
     */
    abstract function export_accounting_html();
    /**
     * display detail of operations m_mode=D
     */
    abstract function export_detail_html();
    /**
     * display extended details of operation m_mode=E
     */
    abstract function export_extended_html();
    /**
     * display operation on one line m_mode=L
     */
    abstract function export_oneline_html();
    /**
     * call the right function , depending of m_mode
     */
    abstract function export_html();

    abstract function get_row($p_limit, $p_offset);
    
    /**
     * Build a SQL clause to filter operation depending if they are paid, unpaid or no filter
     * @return string SQL Clause
     */
    protected  function build_filter_operation()
    {
        switch ( $this->get_filter_operation() )
        {
            case 'all':
                $sql_filter="";
                break;
            case 'paid':
                $sql_filter=" and (jr_date_paid is not null or  jr_rapt ='paid' ) ";
                break;
            case 'unpaid':
                $sql_filter=" and (jr_date_paid  is null and coalesce(jr_rapt,'x') <> 'paid' ) ";
                break;
            default:
                throw new Exception(_("Filtre invalide",5));
                
        }
        return $sql_filter;
    }
    /**
     * Filter operation
     */
    function get_filter_operation()
    {
       return $this->filter_operation;
    }
        /**
     * Filter operation ,
     * @param string $filter_operation, valid : all, paid, unpaid
     */

    public function set_filter_operation($filter_operation)
    {
        if (in_array($filter_operation,['all','paid','unpaid']))
        {
            $this->filter_operation=$filter_operation;
            return $this;
        }
        throw new Exception(_("Filter invalide ".$filter_operation),5);
    }

    /**
     * @brief count the number of addition tax for the ledger
     * @return integer
     */
    public function has_other_tax()
    {
        $str_ledger=join(',',$this->ma_ledger);
        $count=$this->db->get_value("select count(*) 
                from jrn_tax 
                    join jrnx using (j_id) 
                    join jrn on (jr_grpt_id=j_grpt) 
                    where jr_tech_per>=$1 and jr_tech_per <=$2
                    and jr_def_id in ($str_ledger) ",[$this->m_from,$this->m_to]);
        return $count;
    }
    /**
     * @brief add additional info about additional tax. Add to $this->data an array containing the
     * info about a additional tax. Concerns only purchase and sales ledgers
     * @verbatim
    $this->data[$i]['supp_tax']['ac_id'] id in Acc_Other_Tax
    $this->data[$i]['supp_tax']['j_montant']  Amount of this tax
    $this->data[$i]['supp_tax']['ac_label']   Label of this tax
    $this->data[$i]['supp_tax']['ac_rate']    Rate of this tax
    $this->data[$i]['supp_tax']['j_poste']    Accounting
     * @endverbatim
     */
    protected function add_additional_tax_info()
    {
        $prepare=$this->db->is_prepare("supp_tax_info");
        if ( $prepare == false ){
            $this->db->prepare("supp_tax_info","
                select 
                   case when j.j_debit is false and jd.jrn_def_type='ACH'      then 0-j_montant
                      when j.j_debit is true and jd.jrn_def_type='VEN'      then 0-j_montant
                      else j.j_montant end j_montant,
                    jt1.ac_id,
                    ac_label,
                    ac_rate,j_poste 
                from 
                    jrn_tax jt1 
                    join acc_other_tax using (ac_id)
                    join jrnx j using (j_id) 
                    join jrn_def jd on (j.j_jrn_def=jd.jrn_def_id) 
                where j_grpt=$1
            ");

        }
        $data=$this->get_data();
        $nb_row=count($data);

        for ($i=0;$i<$nb_row;$i++)
        {
            $ret=$this->db->execute("supp_tax_info",array($data[$i]["jr_grpt_id"]));
            $array=Database::fetch_all($ret);
            $array=($array==false)?array():$array;
            $data[$i]["supp_tax"]=$array;
        }
        $this->set_data($data);
    }
}
