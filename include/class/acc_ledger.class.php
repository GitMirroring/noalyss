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
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
require_once NOALYSS_INCLUDE.'/database/jrn_def_sql.class.php';
require_once NOALYSS_INCLUDE.'/database/operation_currency_sql.class.php';

/**
 * \file
 * @brief Class for jrn,  class acc_ledger for manipulating the ledger
 */

/**
 * @class Acc_Ledger
 * @brief Class for jrn,  class acc_ledger for manipulating the ledger AND some acc. operations
 *
 */
class Acc_Ledger  extends jrn_def_sql
{

    var $id;     /*!< jrn_def.jrn_def_id */
    var $db;     /*!< database connextion */
    var $row;    /*!< row of the ledger */
    var $ledger_type;   /*!< type of the ledger ACH ODS FIN VEN or GL */
    var $nb;     /*!< default number of rows by  default 10 */
    var $currency_id;/*!<  $currency_id (int) SQL:CURRENCY.ID  default 0 */
    /*!< is_loaded true the ledger definition is loaded or false, it is not */
    protected  $is_loaded ; 
    var $ledger_name;
    var $jr_internal ; /*!< $jr_internal (string) internal number for an operation */
    var $jr_id; /*!< $jr_id (int) SQL : PK JRN.JR_ID */
    var $jrn_def_max_line_deb ; /*!< $jr_id (int) PK.JRN */
    var $jrn_def_id; /*!<  $jrn_def_id(INT) jrn_def.jrn_def_id */
    var $jrn_def_name; /*!<  $jrn_def_name(string) ledger name  */
    var $jrn_def_ech_lib; /*!< $jrn_def_ech_lib (string) text for limit date   */
    var $jrn_def_type; /*!< $jrn_def_type(string)  type of the ledger ACH,VEN,ODS,FIN */
    var $jrn_def_pj_pref; /*!< $jrn_def_pj_pref(string) prefix for receipt   */
    var $jrn_deb_max_line;/*!< $jrn_deb_max_line(int) max rows to display*/
    var $jrn_def_description; /*!< $jrn_def_description(string) ledger description   */
    var $jrn_enable; /*!< $jrn_enable (0 or 1)0:ledger not available, 1:ledger available    */
    var $jrn_def_negative_amount; /*!< $jrn_def_negative_amount (0-1) 0: ledger use positive or negative amount, 1: ledger should use negative amount  */
    var $jrn_def_negative_warning; /*!<$jrn_def_negative_warning (string) string to display if the amount is not positive (see $jrn_def_negative_amount) */
    var $jrn_def_quantity; /*!< $jrn_def_quantity  (0-1) 0 no quantity for operations
                            * 1 has quantity  */
    var $with_concerned; /*!< $with_concerned(bool) : true is operation comes with 
                        another one,     */
    var $jr_grpt_id ; /**! $jr_grpt_id (int) SQL JRN.JR_GRP_ID group rows
                             of an operations     */
    var $pj; /*!< $pj (string) nb receipt of the operation */
        
    var $doc; /*!< $doc (string) HTML with an anchor to the doc. of operation*/
    /**
     * @brief construct
     * @param $p_cn database connexion
     * @param $p_id (int) ledger idf jrn.jrn_def_id
     */
    function __construct($p_cn, $p_id)
    {
        parent::__construct($p_cn, $p_id);
        $this->id=$p_id;
        $this->ledger_name=&$this->jrn_def_name;
        $this->jrn_def_id=&$this->id;
        $this->db=$p_cn;
        $this->row=null;
        $this->nb=MAX_ARTICLE;
        $this->is_loaded=false;
        
        if ($p_id <> 0 ) {
            
            $this->is_loaded=true;
        }
    }
    public function get_is_loaded()
    {
        return $this->is_loaded;
    }

    public function set_is_loaded($is_loaded): void
    {
        $this->is_loaded=$is_loaded;
    }

        /**
     * @brief retrieve currency_id from database
     */
    function set_currency_id()
    {
       $this->db->get_value("select currency_id from jrn_def where jrn_def_id=$1",
            [$this->id]);
        if ( $this->currency_id == "") {
            $this->currency_id=0;
        }
    }

    /**
     * @brief returns the sequence number of the receipt for the current ledger
     * or create the sequence if it doesn't exist
     * @return int
     * @throws Exception if the ledger doesn't exist
     */
    function get_last_pj()
    {
        if (isNumber($this->id)==0)
        {
            throw new Exception(_("Paramètre invalide"));
        }
        if ($this->db->exist_sequence("s_jrn_pj".$this->id))
        {
            $ret=$this->db->get_array("select last_value,is_called from s_jrn_pj".$this->id);
            $last=$ret[0]['last_value'];
            /**
             * \note  With PSQL sequence , the last_value column is 1 when before   AND after the first call, 
             * to make the difference between them
             * I have to check whether the sequence has been already called or not */
            if ($ret[0]['is_called']=='f')
                $last--;
            return $last;
        }
        else
        {
            $this->db->create_sequence("s_jrn_pj".$this->id);
        }
        return 0;
    }
    /**
     * @brief Set the jrn_def.jrn_def_id
     * @param integer $p_id
     */
    function set_ledger_id($p_id)
    {
        $this->id=$p_id;
        $this->jrn_def_id=&$this->id;
    }
    /**
     * @brief Set the jrn_def.jrn_def_id
     * @return integer
     */
    function get_ledger_id()
    {
        return $this->id;
    }
    /**
     * @brief Return the type of a ledger (ACH,VEN,ODS or FIN) or GL
     * @return string FIN ODS ACH VEN or GL if id == 0
     */
    function get_type()
    {
        if ($this->id==0)
        {
            $this->ledger_name=_(" Tous les journaux");
            $this->ledger_type="GL";
            return "GL";
        }

        $Res=$this->db->exec_sql("select jrn_def_type from ".
                " jrn_def where jrn_def_id=".
                $this->id);
        $Max=Database::num_row($Res);
        if ($Max==0)
            return null;
        $ret=Database::fetch_array($Res, 0);
        $this->ledger_type=$ret['jrn_def_type'];
        return $ret['jrn_def_type'];
    }

    /**
     * @brief let you delete a operation
     * @note by cascade it will delete also in
     * - jrnx
     * - stock
     * - quant_purchase
     * - quant_fin
     * - quant_sold
     * - operation_analytique
     * - letter
     * - reconciliation
     * @bug the attached document is not deleted
     * @bug Normally it should be named delete_operation, cause the id is the ledger_id
     * (jrn_def_id) and not the operation id
     */
    function delete()
    {
        if ($this->id==0)
        {
            return;
        }
        $grpt_id=$this->db->get_value('select jr_grpt_id from jrn where jr_id=$1',
                array($this->jr_id));
        if ($this->db->count()==0)
        {
            return;
        }
        $this->db->exec_sql('delete from jrnx where j_grpt=$1', array($grpt_id));
        $this->db->exec_sql('delete from jrn where jr_id=$1',
                array($this->jr_id));

    }

    /**
     * @brief Display warning contained in an array
     * @return string with error message
     */
    function display_warning($pa_msg, $p_warning)
    {
        $str='<p class="notice"> '.$p_warning;
        $str.="<ol class=\"notice\">";
        for ($i=0; $i<count($pa_msg); $i++)
        {
            $str.="<li>".$pa_msg[$i]."</li>";
        }
        $str.='</ol>';
        $str.='</p>';
        return $str;
    }

    /**
     * @brief reverse the operation by creating the opposite one,
     * the result is to avoid it
     * it must be done in
     *    - jrn
     *    - jrnx
     *    - quant_fin
     *    - quant_sold
     *    - quant_purchase
     *    - stock
     *    - ANC
     *    - jrn_tax
     * Add or update a note into jrn_note
     * @param $p_date is the date of the reversed op
     * @exception if date is invalid or other prob
     * @note automatically create a reconciliation between operation
     * You must set the ledger_id $this->jrn_def_id
     * This function should be in operation or call an acc_operation object
     * 
     */
    function reverse($p_date,$p_label)
    {
        global $g_user;
        global $g_parameter;
        try
        {
            $this->db->start();
            if (!isset($this->jr_id)||$this->jr_id=='')
            {
                throw new Exception(_("this->jr_id is not set ou opération inconnue"));
            }

            /* check if the date is valid */
            if (isDate($p_date)==null)
            {
                throw new Exception(_('Date invalide').$p_date);
            }

            // if the operation is in a closed or centralized period
            // the operation is voided thanks the opposite operation
            $grp_new=$this->db->get_next_seq('s_grpt');
            $seq=$this->db->get_next_seq("s_jrn");
            $p_internal=$this->compute_internal_code($seq);
            $this->jr_grpt_id=$this->db->get_value('select jr_grpt_id from jrn where jr_id=$1',
                    array($this->jr_id));
            if ($this->db->count()==0)
                throw new Exception(_("Cette opération n'existe pas"));
            $this->jr_internal=$this->db->get_value('select jr_internal from jrn where jr_id=$1',
                    array($this->jr_id));
            if ($this->db->count()==0||trim($this->jr_internal)=='')
                throw new Exception(_("Cette opération n'existe pas"));

            /* find the periode thanks the date */
            $per=new Periode($this->db);
            $per->jrn_def_id=$this->id;
            $per->find_periode($p_date);

            if ($per->is_open()==0)
            {
                throw new Exception(_('PERIODE FERMEE')." $p_date ");
            }

            // Mark the operation invalid into the ledger
            // to avoid to nullify twice the same op., add or update a note into jrn_note
            if ($this->db->get_value("select count(*) from jrn_note where jr_id=$1",[$this->jr_id])>0){
                $sql="update jrn_note set n_text=$2||n_text where jr_id=$1";
                $Res=$this->db->exec_sql($sql, array($this->jr_id,$p_label));
            }else {
                $sql="insert into jrn_note(n_text,jr_id) values ($1,$2)";
                $Res=$this->db->exec_sql($sql, array($p_label,$this->jr_id));
            }

            // Check return code
            if ($Res==false)
            {
                throw new Exception(__FILE__.__LINE__."sql a echoue [ $sql ]");
            }

            //////////////////////////////////////////////////
            // Reverse in jrnx* tables
            //////////////////////////////////////////////////
            $a_jid=$this->db->get_array("select j_id,j_debit from jrnx where j_grpt=$1",
                    array($this->jr_grpt_id));
            $anc_group_id=0;
            // for each item in JRNX
            for ($l=0; $l<count($a_jid); $l++)
            {
                // jrnx.j_id to reverse
                $row=$a_jid[$l]['j_id'];
                
                // Make also the change into jrnx
                $sql="insert into jrnx (
                  j_date,j_montant,j_poste,j_grpt,
                  j_jrn_def,j_debit,j_text,j_internal,j_tech_user,j_tech_per,j_qcode
                  ) select to_date($1,'DD.MM.YYYY'),j_montant,j_poste,$2,
                  j_jrn_def,not (j_debit),j_text,$3,$4,$5,
                  j_qcode
                  from
                  jrnx
                  where   j_id=$6 returning j_id";
                $Res=$this->db->exec_sql($sql,
                        array($p_date, $grp_new, $p_internal, $g_user->login, $per->p_id,
                    $row));
                // Check return code
                if ($Res==false)
                {
                    throw (new Exception(__FILE__.__LINE__."SQL ERROR [ $sql ]"));
                }
                $aj_id=$this->db->fetch(0);
                
                // jrnx.j_id of the reversed operation
                $j_id=$aj_id['j_id'];

                /* automatic lettering */
                $let=new Lettering($this->db);
                $let->insert_couple($j_id, $row);

                // reverse in QUANT_SOLD
                $Res=$this->db->exec_sql("INSERT INTO quant_sold(
                                     qs_internal, qs_fiche, qs_quantite, qs_price, qs_vat,
                                     qs_vat_code, qs_client, qs_valid, j_id,qs_vat_sided,qs_unit)
                                     SELECT $1, qs_fiche, qs_quantite*(-1), qs_price*(-1), qs_vat*(-1),
                                     qs_vat_code, qs_client, qs_valid, $2,qs_vat_sided*(-1),qs_unit*(-1)
                                     FROM quant_sold where j_id=$3",
                        array($p_internal, $j_id, $row));

                if ($Res==false)
                {
                    throw new Exception(__FILE__.__LINE__."sql a echoue [ $sql ]");
                }
                $Res=$this->db->exec_sql("INSERT INTO quant_purchase(
                                     qp_internal, j_id, qp_fiche, qp_quantite, qp_price, qp_vat,
                                     qp_vat_code, qp_nd_amount, qp_nd_tva, qp_nd_tva_recup, qp_supplier,
                                     qp_valid, qp_dep_priv,qp_vat_sided,qp_unit)
                                     SELECT  $1, $2, qp_fiche, qp_quantite*(-1), qp_price*(-1), qp_vat*(-1),
                                     qp_vat_code, qp_nd_amount*(-1), qp_nd_tva*(-1), qp_nd_tva_recup*(-1), qp_supplier,
                                     qp_valid, qp_dep_priv*(-1),qp_vat_sided*(-1),qp_unit*(-1)
                                     FROM quant_purchase where j_id=$3",
                        array($p_internal, $j_id, $row));

                if ($Res==false)
                {
                    throw new Exception(__FILE__.__LINE__."SQL ERROR [ $sql ]");
                }
                // Reverse also in the currency table
                $this->db->exec_sql("insert into operation_currency (oc_amount,oc_vat_amount,oc_price_unit,j_id) "
                        . " select oc_amount,oc_vat_amount,oc_price_unit,$j_id from operation_currency where j_id=$1",
                        [$row]);
                // Extourne also into jrnx_tax
                $jrn_tax_id=$this->db->exec_sql("insert into jrn_tax(j_id,pcm_val,ac_id) 
                            select $j_id,pcm_val,ac_id from jrn_tax where j_id=$1 returning jt_id",
                    [$row]);

                // if we use analytic , extourne also in operation_analytic
                if ($g_parameter->MY_ANALYTIC != 'nu') {
                    // if there is the first operation_analytic to insert , compute the group_id (operation_analytic.oa_group)
                    if ($anc_group_id == 0 ) $anc_group_id=$this->db->get_next_seq('s_oa_group');
                    $this->db->exec_sql("
           insert into operation_analytique (po_id,oa_amount
                    ,oa_description
                    ,oa_debit
                    ,j_id
                    , oa_date
                    , oa_row
                    , oa_positive
                    , f_id
                    ,oa_jrnx_id_source
                    ,oa_group) 
                select po_id
                     ,oa_amount
                     ,oa_description
                     ,case oa_debit when true then false else true end 
                     , $j_id
                     ,  to_date($2,'DD.MM.YYYY')
                     , oa_row
                     , oa_positive
                     , f_id
                     ,oa_jrnx_id_source
                     ,$anc_group_id from operation_analytique 
                            where 
                                j_id=$1
                    ",[$row, $p_date]);

                }


            } // end for each item in JRNX
            $old_receipt=$this->db->get_row("select jr_pj_number,jr_def_id from jrn where jr_id=$1",[$this->jr_id]);
            $sql="insert into jrn (
              jr_id,
              jr_def_id,
              jr_montant,
              jr_comment,
              jr_date,
              jr_grpt_id,
              jr_internal
              ,jr_tech_per, 
              jr_valid,
              jr_optype,
              currency_id,
              currency_rate,
              currency_rate_ref
              )
              select $1,jr_def_id,jr_montant,$7,
              to_date($2,'DD.MM.YYYY'),$3,$4,
              $5, true,'EXT',currency_id,currency_rate,currency_rate_ref
              from
              jrn
              where   jr_id=$6 returning jr_id";
            try {

                $reverse_id=$this->db->get_value($sql,
                    array($seq, $p_date, $grp_new, $p_internal, $per->p_id, $this->jr_id,$p_label));
                $reverse_accOp=new Acc_Operation($this->db);
                $reverse_accOp->set_id($reverse_id);
                $reverse_accOp->pj=$old_receipt['jr_pj_number'];
                $reverse_accOp->jrn=$old_receipt['jr_def_id'];
                $reverse_accOp->update_receipt();
            // Check return code
            } catch (\Exception $e){
                throw new \Exception('Echec extourne');

            }



            // reverse in QUANT_FIN table
            $Res=$this->db->exec_sql("  INSERT INTO quant_fin(
                                 qf_bank,  qf_other, qf_amount,jr_id,j_id)
                                 SELECT  qf_bank,  qf_other, qf_amount*(-1),$1,$3
                                 FROM quant_fin where jr_id=$2",
                    array($seq, $this->jr_id,$j_id));
            if ($Res==false)
            {
                throw (new Exception(__FILE__.__LINE__."SQL ERROR[ $sql ]"));
            }

            // Add a "concerned operation to bound these op.together
            //
            $rec=new Acc_Reconciliation($this->db);
            $rec->set_jr_id($seq);
            $rec->insert($this->jr_id);

            // Check return code
            if ($Res==false)
            {
                throw (new Exception(__FILE__.__LINE__."SQL ERROR [ $sql ]"));
            }

            // the table stock must updated
            // also in the stock table
            $sql="delete from stock_goods where sg_id = any ( select sg_id
             from stock_goods natural join jrnx  where j_grpt=$1)";
            $Res=$this->db->exec_sql($sql,array($this->jr_grpt_id));
            if ($Res==false)
            {
                throw (new Exception(__FILE__.__LINE__."SQL ERROR [ $sql ]"));
            }
            /**
             * reverse also in analytic account;
             */

            $this->db->commit();
        }
        catch (Exception $e)
        {
              record_log($e);
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * @brief Return the name of a ledger
     *
     */
    function get_name()
    {
        if ($this->id==0)
        {
            $this->ledger_name=_("Grand Livre");
            return $this->ledger_name;
        }

        $Res=$this->db->exec_sql("select jrn_def_name from ".
                " jrn_def where jrn_def_id=$1", array($this->id));
        $Max=Database::num_row($Res);
        if ($Max==0)
        {
            return null;
        }
        $ret=Database::fetch_array($Res, 0);
        $this->ledger_name=$ret['jrn_def_name'];
        return $ret['jrn_def_name'];
    }

    

    


    /**
     * @brief guess what  the next pj should be
     */
    function guess_pj()
    {
        $prop=$this->get_propertie();
        $pj_pref=$prop["jrn_def_pj_pref"];
        $padding=$prop['jrn_def_pj_padding'];
        $pj_seq=$this->get_last_pj()+1;
        return $pj_pref.str_pad($pj_seq,$padding??0,'0',STR_PAD_LEFT);

    }

    

    
   

// retrieve data from jrnx
    /**
     * @brief  Get the properties of a journal
     *
     * \return an array containing properties
     *
     */
    function get_propertie()
    {
        if ($this->id==0)
            return;

        $Res=$this->db->get_row("select *
                                 from jrn_Def
                                 where jrn_def_id=$1", array($this->id));
        if ($Res == NULL)
        {
            echo '<DIV="redcontent"><H2 class="error">'._('Parametres journaux non trouves').'</H2> </DIV>';
            return null;
        }
        return $Res;
    }


    /**
     * @brief get the saldo of a ledger for a specific period
     * @param$p_from start period
     * @param$p_to end period
     */
    function get_solde($p_from, $p_to)
    {
        bcscale(4);
        $ledger="";
        if ($this->id!=0)
        {
            $ledger=" and j_jrn_def = ".$this->id;
        }

        $periode=sql_filter_per($this->db, $p_from, $p_to, 'p_id', 'j_tech_per');
        $sql='select j_montant as montant,j_debit as deb from jrnx where '
                .$periode.$ledger;

        $ret=$this->db->exec_sql($sql);
        $array=Database::fetch_all($ret);
        $deb=0.0;
        $cred=0.0;
        if ( $array==FALSE) $array=[];
        foreach ($array as $line)
        {

            if ($line['deb']=='t')
                $deb=bcadd($deb,$line['montant']);
            else
                $cred=bcadd($cred,$line['montant']);
        }
        $response=array($deb, $cred);
        return $response;
    }

    /**
     * @brief Show a select list   of the ledgers you can access in
     * writing, reading or simply accessing.
     * @param $p_type = ALL or the type of the ledger (ACH,VEN,FIN,ODS)
     * @param $p_access =3 for READ or WRITE, 2 for write and 1 for readonly
     * @param Boolean TRUE all ledger are selected, or FALSE only enable
     * \return     object HtmlInput select
     * 
     */
    function select_ledger($p_type="ALL", $p_access=3,$enable=TRUE)
    {
        global $g_user;
        $array=$g_user->get_ledger($p_type, $p_access,$enable);

        if ($array==null)
            return null;
        $idx=0;
        $ret=array();

        foreach ($array as $value)
        {
            $ret[$idx]['value']=$value['jrn_def_id'];
            $ret[$idx]['label']=h($value['jrn_def_name']);
            $idx++;
        }

        $select=new ISelect();
        $select->name='p_jrn';
        $select->value=$ret;
        $select->selected=$this->id;
        return $select;
    }

    /**
     * @brief retrieve the jrn_def_fiche and return them into a array
     *        index deb, cred
     * \param
     * \param
     * \param
     *
     *
     * \return return an array ('deb'=> ,'cred'=>)
     */
    function get_fiche_def()
    {
        $sql="select jrn_def_fiche_deb as deb,jrn_def_fiche_cred as cred ".
                " from jrn_def where ".
                " jrn_def_id = $1 ";

        $r=$this->db->exec_sql($sql, array($this->id));

        $res=Database::fetch_all($r);
        if ($res==FALSE || empty($res))
            return null;

        return $res[0];
    }

    /**
     * @brief retrieve the jrn_def_class_deb and return it
     *
     *
     * \return return an string
     */
    function get_class_def()
    {
        $sql="select jrn_def_class_deb  ".
                " from jrn_def where ".
                " jrn_def_id = $1";

        $r=$this->db->exec_sql($sql, array($this->id));

        $res=Database::fetch_all($r);

        if (empty($res))
            return null;

        return $res[0];
    }

    /**
     * @brief show the result of the array to confirm
     * before inserting
     * @param$p_array array from the form
     * @return HTML string
     */
    function confirm($p_array, $p_readonly=false)
    {
        global $g_parameter,$g_user;
        $http=new HttpInput();
        $msg=array();
        if (!$p_readonly)
            $msg=$this->verify_operation($p_array);
        $this->id=$p_array['p_jrn'];
        if (empty($p_array))
            return _("Aucun résultat");
        $anc=null;
        extract($p_array, EXTR_SKIP);
        if ( !isset($p_array['jrn_note_input'])) {$p_array['jrn_note_input']='';}
        $lPeriode=new Periode($this->db);
        if ($this->check_periode()==true)
        {
            $lPeriode->p_id=$period;
        }
        else
        {
            $lPeriode->find_periode($e_date);
        }
        $total_deb=0;
        $total_cred=0;
        bcscale(2);

        $ret="";
        if (!empty($msg))
        {
            $ret.=$this->display_warning($msg,
                    _("Attention : il vaut mieux utiliser les fiches que les postes comptables"));
        }
        $ret.="<table >";
        $ret.="<tr><td>"._('Date')." : </td><td>$e_date</td></tr>";
        /* display periode */
        $date_limit=$lPeriode->get_date_limit();
        $ret.='<tr> '.td(_('Période Comptable')).td($date_limit['p_start'].'-'.$date_limit['p_end']).'</tr>';
        $ret.="<tr><td>"._('Libellé')." </td><td>".h($desc)."</td></tr>";
        $ret.="<tr><td>";
        $ret.=_('Note').'</td><td><pre>'. h($p_array['jrn_note_input']).'</pre>';
        $ret.="</td></tr>";
        $span=$this->warn_manual_receipt($p_array);
        if ( $g_parameter->MY_PJ_SUGGEST=="A"||$g_user->check_action(UPDRECEIPT)==0)
        {
            $e_pj=$this->guess_pj();
            $span="";
        }
        if ($p_readonly == false) $ret.="<tr><td>"._('PJ Num')." </td><td>".h($e_pj).$span."</td></tr>";
        if ($p_readonly == true ) $ret.="<tr><td>"._('PJ Num')." </td><td>".h($this->pj).$span."</td></tr>";
        $ret.='</table>';
        $ret.="<table class=\"result\">";
        $ret.="<tr>";
        $ret.="<th>"._('Quick Code ou ');
        $ret.=_("Poste")." </th>";
        $ret.="<th style=\"text-align:left\"> "._("Libellé")." </th>";
        $ret.="<th style=\"text-align:right\">"._("Débit")."</th>";
        $ret.="<th style=\"text-align:right\">"._("Crédit")."</th>";
        /* if we use the AC */
        if ($g_parameter->MY_ANALYTIC!='nu')
        {
            $anc=new Anc_Plan($this->db);
            $a_anc=$anc->get_list();
            $x=count($a_anc);
            /* set the width of the col */
            $ret.='<th colspan="'.$x.'" style="width:auto;text-align:center" >'._('Compt. Analytique').'</th>';

            /* add hidden variables pa[] to hold the value of pa_id */
            $ret.=Anc_Plan::hidden($a_anc);
        }
        $ret.="</tr>";

        $ret.=HtmlInput::hidden('e_date', $e_date);
        $ret.=HtmlInput::hidden('desc', $desc);
        $ret.=HtmlInput::hidden('period', $lPeriode->p_id);
        $ret.=HtmlInput::hidden('e_pj', $e_pj);
        $ret.=HtmlInput::hidden('e_pj_suggest', $e_pj_suggest);
        $ret.=HtmlInput::hidden('jrn_note_input',h($p_array['jrn_note_input']));

        $mt=microtime(true);
        $ret.=HtmlInput::hidden('mt', $mt);
        // For predefined operation
        $ret.=HtmlInput::hidden('e_comm', $desc);
        $ret.=HtmlInput::hidden('jrn_type', $this->get_type());
        $ret.=HtmlInput::hidden('p_jrn', $this->id);
        $ret.=HtmlInput::hidden('nb_item', $nb_item);
        if ($this->with_concerned==true)
        {
            $ret.=HtmlInput::hidden('jrn_concerned', $jrn_concerned);
        }
        $ret.=dossier::hidden();
        $count=0;
        for ($i=0; $i<$nb_item; $i++)
        {
            if ($p_readonly==true)
            {
                if (!isset(${'qc_'.$i}))
                    ${'qc_'.$i}='';
                if (!isset(${'poste'.$i}))
                    ${'poste'.$i}='';
                if (!isset(${'amount'.$i}))
                    ${'amount'.$i}='';
            }
            $class=($i%2==0)?' class="even" ':' class="odd" ';
            $ret.="<tr $class> ";
            if (trim(${'qc_'.$i})!="")
            {
                $oqc=new Fiche($this->db);
                $oqc->get_by_qcode(${'qc_'.$i}, false);
                $strPoste=$oqc->get_attribute(ATTR_DEF_ACCOUNT);
                $ret.="<td>".
                        ${'qc_'.$i}.' - '.
                        $oqc->get_attribute(ATTR_DEF_NAME).HtmlInput::hidden('qc_'.$i,
                                ${'qc_'.$i}).
                        '</td>';
            }

            if (trim(${'qc_'.$i})==""&&trim(${'poste'.$i})!="")
            {
                $oposte=new Acc_Account_Ledger($this->db, ${'poste'.$i});
                $strPoste=$oposte->id;
                $ret.="<td>".h(${"poste".$i}." - ".
                                $oposte->get_name()).HtmlInput::hidden('poste'.$i,
                                ${'poste'.$i}).
                        '</td>';
            }

            if (trim(${'qc_'.$i})==""&&trim(${'poste'.$i})=="")
                continue;
            $ret.="<td>".h(${"ld".$i}).HtmlInput::hidden('ld'.$i, ${'ld'.$i});
            $ret.=(isset(${"ck$i"}))?HtmlInput::hidden('ck'.$i, ${'ck'.$i}):"";
            $ret.="</td>";
            if (isset(${"ck$i"}))
            {
                $ret.="<td class=\"num\">".nbm(${"amount".$i}).HtmlInput::hidden('amount'.$i,
                                ${'amount'.$i})."</td>".td("");
                $total_deb=bcadd($total_deb, ${'amount'.$i});
            }
            else
            {
                $ret.=td("")."<td class=\"num\">".nbm(${"amount".$i}).HtmlInput::hidden('amount'.$i,
                                ${'amount'.$i})."</td>";
                $total_cred=bcadd($total_cred, ${"amount".$i});
            }
            // CA
            if ($g_parameter->MY_ANALYTIC!='nu') // use of AA
            {
                if ($g_parameter->match_analytic( $strPoste)==TRUE)
                {
                    // show form
                    $op=new Anc_Operation($this->db);
                    $null=($g_parameter->MY_ANALYTIC=='op')?1:0;
                    $p_array['pa_id']=$a_anc;
                    /* op is the operation it contains either a sequence or a jrnx.j_id */
                    $ret.=HtmlInput::hidden('op[]=', $i);

                    $ret.='<td style="text-align:center">';
                    $read=($p_readonly==true)?0:1;
                    $ret.=$op->display_form_plan($p_array, $null, $read, $count,
                            round(${'amount'.$i}, 2));
                    $ret.='</td>';
                    $count++;
                }
            }



            $ret.="</tr>";
        }
        $http->set_array($p_array);
        $currency_code=$http->extract("p_currency_code","number");
        $currency_rate=$http->extract("p_currency_rate","number");
        $currency=new Acc_Currency($this->db,$currency_code);
        $msg_currency= ($currency_code != 0 )?sprintf(_("Totaux %s (%s)"),$currency->get_code(),$currency_rate):_("Totaux");
        
        $ret.=tr(td('').td($msg_currency).td($total_deb, 'class="num"').td($total_cred,
                        'class="num"'), 'class="highlight"');
        // Currency
        if ( $currency_code != 0)
        {
            $currency_rate=$http->extract("p_currency_rate","number");
            $default_currency=new Acc_Currency($this->db,0);

            $ret.=tr(td('').
                    td(_('Totaux')." ".$default_currency->get_code()).
                    td(bcdiv($total_deb,$currency_rate), 'class="num"').
                    td(bcdiv($total_cred,$currency_rate), 'class="num"'),
                    'class="highlight"');
        }
        
        $ret.="</table>";
        if ($g_parameter->MY_ANALYTIC!='nu'&&$p_readonly==false) {
            $ret.='<input type="button" class="button" value="'._('verifie Imputation Analytique').
        '" onClick="verify_ca(\'\');">';
            
        }
        return $ret;
    }

    function get_min_row()
    {
        $row=$this->db->get_value("select jrn_deb_max_line from jrn_def where jrn_def_id=$1",
                array($this->id));
        return $row;
    }

    /**
     * @brief Show the form to encode your operation
     * @param$p_array if you correct or use a predef operation (default = null)
     * @param$p_readonly 1 for readonly 0 for writable (default 0)
     * @exception if ledger not found
     * \return a string containing the form
     */
    function input($p_array=null, $p_readonly=0)
    {
        global $g_parameter, $g_user;
        $http=new HttpInput();
        $this->nb=$this->get_min_row();
        if ($p_readonly==1)
            return $this->confirm($p_array);

        if ($p_array!=null)
            extract($p_array, EXTR_SKIP);
        $add_js="";
        if ($g_parameter->MY_PJ_SUGGEST !='N')
        {
            $add_js="update_receipt();";
        }
        if ($g_parameter->MY_DATE_SUGGEST=='Y')
        {
            $add_js.='get_last_date();';
        }
        $add_js.='update_row("quick_item");';
        $ret="";
        $add_card=false;
        if ($g_user->check_action(FICADD)==1)
        {
            // Button for adding customer
            $add_card=true;
        }
        $wLedger=$this->select_ledger('ODS', 2,FALSE);
        if ($wLedger==null)
            throw new Exception(_('Pas de journal disponible'));
        $ac=$http->request("ac");
        $wLedger->javascript="onChange='update_name();update_predef(\"ods\",\"t\",\"".$ac."\");$add_js'";
        $label=" Journal ".Icon_Action::infobulle(2);

        $ret.="<table>";
        $ret.=tr(td($label).td($wLedger->input()));

        // 
        // Button for template operation
        //
                ob_start();
        echo '<div id="predef_form">';
        echo HtmlInput::hidden('p_jrn_predef', $this->id);
        $op=new Pre_operation( $this->db);
        $op->set_p_jrn($this->id);
        $op->set_jrn_type("ODS");

        $url=http_build_query(
                array('action'=>'use_opd', 
                    'p_jrn_predef'=>$this->id,
                    'ac'=>$ac,
                    'gDossier'=>dossier::id()));
        echo $op->form_get('do.php?'.$url);

        echo '</div>';
        $str_op_template=ob_get_contents();
        ob_end_clean();
        $ret.="<tr>";
        $ret.="<td>"._("Modèle d'opération")."</td>";
        $ret.="<td>".$str_op_template."</td>";
        $ret.="</tr>";
        // Load the javascript
        //
		//$ret.= '<tr ><td colspan="2" style="width:auto">';
        $wDate=new IDate('e_date');
        $wDate->readonly=$p_readonly;
        $e_date=(isset($e_date)&&trim($e_date)!='')?$e_date:'';
        $wDate->value=$e_date;

        $ret.=tr(td(_("Date")).td($wDate->input()));
        /* insert periode if needed */
        // Periode
        //--
        if ($this->check_periode()==true)
        {
            $l_user_per=$g_user->get_periode();
            $def=(isset($periode))?$periode:$l_user_per;

            $period=new IPeriod("period");
            $period->user=$g_user;
            $period->cn=$this->db;
            $period->value=$def;
            $period->type=OPEN;
            try
            {
                $l_form_per=$period->input();
            }
            catch (Exception $e)
            {
                  record_log($e);
                if ($e->getCode()==1)
                {
                    echo _("Aucune période ouverte");
                    exit();
                }
            }
            $label=Icon_Action::infobulle(3);
            $f_periode=td(_("Période comptable")." $label ").td($l_form_per);
            $ret.=tr($f_periode);
        }
        $wPJ=new IText('e_pj');
        $wPJ->readonly=false;
        $wPJ->size=10;

        /* suggest PJ ? */
        $default_pj='';
        if ($g_parameter->MY_PJ_SUGGEST != 'N')
        {
            $default_pj=$this->guess_pj();
        }
        if ( $g_parameter->MY_PJ_SUGGEST=='A' || $g_user->check_action(UPDRECEIPT)==0)
        {
            $wPJ->setReadOnly(true);
            $wPJ->value=$default_pj;
            $wPJ->id="e_pj";
        } else {
            $wPJ->value=(isset($e_pj))?$e_pj:$default_pj;
        }
        $ret.='</tr>';
        $ret.='<tr >';
        $ret.='<td style="width:auto"> '._('Pièce').' </td> ';
        $ret.=td($wPJ->input());
        $ret.='</td>';
        $ret.='</tr>';
        $ret.='<tr>';
        $ret.=td(_('Libellé'));
        $ret.='<td>';

        $wDescription=new IText('desc');
        $wDescription->readonly=$p_readonly;
        $wDescription->size = (empty($desc))?60:strlen($desc)+5;
        $wDescription->size = ($wDescription->size<60)?60:$wDescription->size;
        $wDescription->value=(isset($desc))?$desc:'';
        $ret.=$wDescription->input();
        $ret.=Icon_Action::longer("desc",20);
        $ret.='</td>';
        $ret.='</tr>';
        $ret.='<tr>';
        $ret.='<td>';
        $ret.=_("Note").
            Icon_Action::show_note('jrn_note_div');
        $ret.='</td>';
        $ret.='<td> <pre id="jrn_note_td"></pre></td>';
        // Currency
        $currency_select = $this->CurrencyInput("currency_code", "p_currency_rate" , "p_currency_euro");
        $currency_select->selected=$http->request('p_currency_code','string',0);

        $currency_input=new INum("p_currency_rate");
        $currency_input->prec=8;
        $currency_input->id="p_currency_rate";
        $currency_input->value=$http->request('p_currency_rate','string',1);
        $ret.=tr(td(_("Devise")).td($currency_select->input().
            $currency_input->change('CurrencyComputeMisc(\'p_currency_rate\',\'p_currency_euro\');')));
        $ret.='</div>';
        $currency=new Acc_Currency($this->db,0);

        $ret.='</table>';

// note for operation
        $note = (isset($p_array['jrn_note_input'])) ? $p_array['jrn_note_input'] : '';
        ob_start();
        Acc_Operation_Note::input($note);
        $ret.=ob_get_contents();
        ob_end_clean();

        $ret.=HtmlInput::hidden('e_pj_suggest', $default_pj);


         
        $nb_row=(isset($nb_item) )?$nb_item:$this->nb;

        $ret.=HtmlInput::hidden('nb_item', $nb_row);
        $ret.=dossier::hidden();
        $ret.=HtmlInput::hidden('jrn_type', $this->get_type());
        $info=Icon_Action::infobulle(0);
        $info_poste=Icon_Action::infobulle(9);
        $ret.='<table id="quick_item" style="width:100%">';
        $ret.='<tr>'.
                '<th style="text-align:left">Quickcode'.$info.'</th>'.
                '<th style="text-align:left">'._('Poste').$info_poste.'</th>'.
                '<th class="visible_gt800 visible_gt1155" style="text-align:left">'._('Libellé').'</th>'.
                '<th style="text-align:left">'._('Montant').'</th>'.
                '<th style="text-align:left">'._('Côté').'</th>'.
                '</tr>';


        for ($i=0; $i<$nb_row; $i++)
        {
            // Quick Code
            $quick_code=new ICard('qc_'.$i);
            $quick_code->set_dblclick("fill_ipopcard(this);");
            $quick_code->set_attribute('ipopup', 'ipopcard');

            // name of the field to update with the name of the card
            $quick_code->set_attribute('label', "ld".$i);

            // name of the field to update with the name of the card
            $quick_code->set_attribute('typecard', 'filter');

            // Add the callback function to filter the card on the jrn
            $quick_code->set_callback('filter_card');
            $quick_code->set_function('fill_data');
            $quick_code->javascript=sprintf(' onchange="fill_data_onchange(\'%s\');" ',
                    $quick_code->name);

            $quick_code->value=(isset(${'qc_'.$i}))?${'qc_'.$i}:"";
            $quick_code->readonly=$p_readonly;
            $label='';
            if ($quick_code->value!='')
            {
                $Fiche=new Fiche($this->db);
                $Fiche->get_by_qcode($quick_code->value);
                $label=$Fiche->get_attribute(ATTR_DEF_NAME);
            }


            // Account
            $poste=new IPoste();
            $poste->name='poste'.$i;
            $poste->id='poste'.$i;
            $poste->set_attribute('ipopup', 'ipop_account');
            $poste->set_attribute('label', 'ld'.$i);
            $poste->set_attribute('account', 'poste'.$i);
            $poste->set_attribute('dossier', Dossier::id());

            $poste->value=(isset(${'poste'.$i}))?${"poste".$i}:''
            ;
            $poste->dbl_click_history();

            $poste->readonly=$p_readonly;

            if ($poste->value!='')
            {
                $Poste=new Acc_Account($this->db,$poste->value);
                $label=$Poste->get_lib();
            }

            // Description of the line
            $line_desc=new IText();
            $line_desc->name='ld'.$i;
            $line_desc->size=30;
            $line_desc->value=(isset(${"ld".$i}))?${"ld".$i}:
                    $label;

            // Amount
            $amount=new INum();
            $amount->size=10;
            $amount->name='amount'.$i;
            $amount->value=(isset(${'amount'.$i}))?${"amount".$i}:''
            ;
            $amount->readonly=$p_readonly;
            $amount->javascript='onChange="format_number(this);checkTotalDirect()"';
            // D/C
            $deb=new ICheckBox();
            $deb->name='ck'.$i;
            $deb->selected=(isset(${'ck'.$i}))?true:false;
            $deb->readonly=$p_readonly;
            $deb->javascript='class="debit-credit"  onChange="checkTotalDirect()"';
            $str_add_button=($add_card==true)?$this->add_card("-1",
                            $quick_code->id):"";
            $ret.='<tr>';
            $ret.='<td>'.$quick_code->input().$quick_code->search().$str_add_button.'</td>';
            $ret.='<td>'.$poste->input().
                    '<script> '.
                    'document.getElementById(\'poste'.$i.'\').onblur=function(){'.
                    'if (trim(this.value) !=\'\') '.
                    '{document.getElementById(\'qc_'.$i.'\').value="";}}'.
                    '</script>'.
                    '</td>';
            $ret.='<td class="visible_gt800 visible_gt1155">'.$line_desc->input().'</td>';
            $ret.='<td>'.$amount->input().'</td>';
            $ret.='<td>'.$deb->input()
                    .'<span id="txt'.$deb->id.'"></span>'
                .'</td>';
            $ret.='</tr>';
            // If readonly == 1 then show CA
        }
        $ret.='</table>';
        if (isset($this->with_concerned)&&$this->with_concerned==true)
        {
            $oRapt=new Acc_Reconciliation($this->db);
            $w=$oRapt->widget();
            $w->name='jrn_concerned';
            $w->value=(isset($jrn_concerned))?$jrn_concerned:"";
            $ret.=sprintf(_("Réconciliation/rapprochements : %s"), $w->input());
        }
        $ret.=create_script("$('".$wDate->id."').focus()");
        // for displaying Credit or Debit
        $ret.=create_script("(function(){activate_checkbox_side()})();");

        return $ret;
    }

    /**
     * @brief
     * check if the current ledger is closed
     * \return 1 for yes, otherwise 0
     * \see Periode::is_closed
     */
    function is_closed($p_periode)
    {
        $per=new Periode($this->db);
        $per->set_ledger($this->id);
        $per->set_periode($p_periode);
        $ret=$per->is_closed();
        return $ret;
    }

    /**
     * @brief verify that the operation can be saved
     * @param$p_array array of data same layout that the $_POST from show_form
     *
     *
     * \throw  the getcode  value is 1 incorrect balance,  2 date
     * invalid, 3 invalid amount,  4 the card is not in the range of
     * permitted card, 5 not in the user's period, 6 closed period
     *
     */
    function verify_operation($p_array)
    {
        global $g_parameter;
        $http=new HttpInput();
        if (is_array($p_array)==false||empty($p_array))
            throw new Exception("Array empty");
        /*
         * Check needed value
         */
        check_parameter($p_array, 'p_jrn,e_date');

        extract($p_array, EXTR_SKIP);
        global $g_user;
        $tot_cred=0;
        $tot_deb=0;
        $msg=array();
        $http->set_array($p_array);
        /* Check currency : rate cannot be equal to 0 */
        $currency_rate=$http->extract("p_currency_rate","number");
        if ( $currency_rate <=0 ) {
            throw new Exception(_("Taux de conversion doit être supérieur à 0"),3);
        }
        /* Check currency : Does the currency parameter exist */
        $currency_code=$http->extract("p_currency_code","number");
        $currency=new Acc_Currency($this->db,$currency_code);

        $this->check_currency_setting($currency_code);
        
        /* check if we can write into this ledger */
        if ($g_user->check_jrn($p_jrn)!='W')
            throw new Exception(_('Accès interdit'), 20);

        /* check for a double reload */
        if (isset($mt)&&$this->db->count_sql('select jr_mt from jrn where jr_mt=$1',
                        array($mt))!=0)
            throw new Exception(_('Double Encodage'), 5);

        // Check the periode and the date
        if (isDate($e_date)==null)
        {
            throw new Exception(_('Date invalide'), 2);
        }
        $periode=new Periode($this->db);
        /* find the periode  if we have enabled the check_periode 
         * or if period is not set
         */
        if ($this->check_periode()==false||!isset($p_array['period']))
        {
            try {
                $periode->find_periode($e_date);
            } catch (Exception $e) {
                throw new Exception(_("Période inexistante"), 6, $e);
            }
        }
        else
        {
            $periode->p_id=$p_array['period'];
            list ($min, $max)=$periode->get_date_limit();
            if (cmpDate($e_date, $min)<0||
                    cmpDate($e_date, $max)>0)
                throw new Exception(_('Date et periode ne correspondent pas'), 6);
        }



        // Periode ferme
        if ($this->is_closed($periode->p_id)==1)
        {
            throw new Exception(_('Periode fermee'), 6);
        }
        /* check if we are using the strict mode */
        if ($this->check_strict()==true)
        {
            /* if we use the strict mode, we get the date of the last
              operation */
            $last_date=$this->get_last_date();
            if ($last_date!=null&&cmpDate($e_date, $last_date)<0)
                throw new Exception(
                        sprintf ( 
                                _('Vous utilisez le mode strict la dernière operation est la date du %s
                vous ne pouvez pas encoder à une date antérieure'),$last_date),
                15);
        }

        for ($i=0; $i<$nb_item; $i++)
        {
            $err=0;

            // compatibily php 8.0 , if $amount is not a number then skip
            if (!isset(${'amount'.$i}) || isNumber(${'amount'.$i})==0) {
                continue;
            }
            
            // Check the balance
            $amount=round(${'amount'.$i}, 2);
            $tot_deb+=(isset(${'ck'.$i}))?$amount:0;
            $tot_cred+=(!isset(${'ck'.$i}))?$amount:0;

            // Check if the card is permitted
            if (isset(${'qc_'.$i})&&trim(${'qc_'.$i})!="")
            {
                $f=new Fiche($this->db);
                $f->get_by_qcode(${'qc_'.$i});
                $f->quick_code=${'qc_'.$i};

                if ($f->get_f_enable() == '0')
                    throw new Exception(sprintf(_("La fiche %s n'est plus utilisée"),${'qc_'.$i}), 50);
                if ($f->belong_ledger($p_jrn) < 1 )
                    throw new Exception("La fiche quick_code = ".
                    $f->quick_code." n'est pas accessible depuis ce journal, à configurer dans C0JRN", 4);
                if (noalyss_strlentrim(${'qc_'.$i})!=0&&isNumber(${'amount'.$i})==0)
                    throw new Exception(_('Montant invalide'), 3);

                $strPoste=$f->get_attribute(ATTR_DEF_ACCOUNT);
                if ($strPoste=='')
                    throw new Exception(sprintf(_("La fiche %s n'a pas de poste comptable"),
                            ${"qc_".$i}));

                $p=new Acc_Account_Ledger($this->db, $strPoste);
                if ($p->do_exist()==0)
                    throw new Exception(_('Poste Inexistant pour la fiche ['.${'qc_'.$i}.']'),
                    4);
            }

            // Check if the account is permitted
            if (isset(${'poste'.$i})&&noalyss_strlentrim(${'poste'.$i})!=0)
            {
                $p=new Acc_Account_Ledger($this->db, ${'poste'.$i});
                if ($p->belong_ledger($p_jrn)<0) {
                    throw new Exception(sprintf ( 
                            _("Le poste %s n'est pas accessible dans ce journal, à configurer dans C0JRN",$p->id)),
                    5);
                }
                if (noalyss_strlentrim(${'poste'.$i})!=0&&isNumber(${'amount'.$i})==0)
                    throw new Exception(_('Poste invalide ['.${'poste'.$i}.']'),
                    3);
                if ($p->do_exist()==0)
                    throw new Exception(_('Poste Inexistant ['.${'poste'.$i}.']'),
                    4);
                $card_id=$p->find_card();
                if (!empty($card_id))
                {
                    $str_msg=sprintf(_(" Le poste %s appartient à  fiche(s) dont : %s"),$p->id,count($card_id));
                    $max=(count($card_id)>MAX_COMPTE_CARD)?MAX_COMPTE_CARD:count($card_id);
                    for ($x=0; $x<$max; $x++)
                    {
                        $card=new Fiche($this->db, $card_id[$x]['f_id']);
                        $str_msg.=HtmlInput::card_detail($card->get_attribute(ATTR_DEF_QUICKCODE),
                                        $card->get_attribute(ATTR_DEF_NAME),
                                        'style="color:red;display:inline;text-decoration:underline"');
                        $str_msg.=" ";
                    }
                    $msg[]=$str_msg;
                }
                $account=new Acc_Account($this->db,${"poste".$i});
                if ( $account->get_parameter("pcm_direct_use") == "N") {
                    throw new Exception(sprintf (_("Utilisation directe interdite du poste comptable %s"), ${"poste".$i}));
                }
            }
        }
        $tot_deb=round($tot_deb, 4);
        $tot_cred=round($tot_cred, 4);
        if ($tot_deb!=$tot_cred)
        {
            throw new Exception(_("Balance incorrecte ")." debit = $tot_deb credit=$tot_cred ",
            1);
        }

        return $msg;
    }

    /**
     * @brief compute the internal code of the saved operation and set the $this->jr_internal to
     *  the computed value
     *
     * @param$p_grpt id in jr_grpt_
     *
     * \return string internal_code
     *      -
     *
     */
    function compute_internal_code($p_grpt)
    {
        if ($this->id==0)
            return;
        $num=$this->db->get_next_seq('s_internal');
        $atype=$this->get_propertie();
        $type=substr($atype['jrn_def_code'], 0, 1);
        $internal_code=sprintf("%s%06X", $type, $num);
        $this->jr_internal=$internal_code;
        return $internal_code;
    }

    /**
     * @brief save the operation into the jrnx,jrn, ,
     *  CA and pre_def
     * @param$p_array
     *
     * \return array with [0] = false if failed otherwise true, [1] error
     * code
     */
    function save($p_array=null)
    {
        if ($p_array==null)
        {
            throw new Exception('save cannot use a empty array');
        }
        global $g_parameter;
        bcscale(4);
        $http=new HttpInput();
        extract($p_array, EXTR_SKIP);
        if ( !isset($p_array['jrn_note_input'])) {$p_array['jrn_note_input']='';}
        try
        {
            $msg=$this->verify($p_array);
            if (!empty($msg))
            {
                echo $this->display_warning($msg,
                        _("Attention : il vaut mieux utiliser les fiches que les postes comptables "));
            }
            $this->db->start();

            $seq=$this->db->get_next_seq('s_grpt');
            $internal=$this->compute_internal_code($seq);

            $group=$this->db->get_next_seq("s_oa_group");
            $tot_amount=0;
            $tot_deb=0;
            $tot_cred=0;
            $oPeriode=new Periode($this->db);
            $check_periode=$this->check_periode();
            if ($check_periode==false||!isset($p_array['period']))
            {
                $oPeriode->find_periode($e_date);
            }
            else
            {
                $oPeriode->p_id=$period;
            }

            $count=0;
            
            // currency
            $http->set_array($p_array);
            $currency_code=$http->extract( "p_currency_code","number",0);
            $currency_rate=$http->extract( "p_currency_rate","number",1);
            $currency_rate_ref=new Acc_Currency($this->db, $currency_code);
            
            for ($i=0; $i<$nb_item; $i++)
            {
                if (!isset(${'qc_'.$i})&&!isset(${'poste'.$i}))
                    continue;
                $acc_op=new Acc_Operation($this->db);
                $quick_code="";
                // First we save the jrnx
                if (isset(${'qc_'.$i}))
                {
                    $qc=new Fiche($this->db);
                    $qc->get_by_qcode(${'qc_'.$i}, false);
                    $sposte=$qc->get_attribute(ATTR_DEF_ACCOUNT);
                    /*  if there are 2 accounts take following the deb or cred */
                    if (strpos($sposte, ',')!=0)
                    {
                        $array=explode(",", $sposte);
                        $poste=(isset(${'ck'.$i}))?$array[0]:$array[1];
                    }
                    else
                    {
                        $poste=$sposte;
                        if ($poste=='')
                            throw new Exception(sprintf(_("La fiche %s n'a pas de poste comptable"),
                                    ${"qc_".$i}));
                    }
                    $quick_code=${'qc_'.$i};
                }
                else
                {
                    $poste=${'poste'.$i};
                }

                $acc_op->date=$e_date;
                // compute the periode is do not check it
                if ($check_periode==false)
                    $acc_op->periode=$oPeriode->p_id;
                $acc_op->desc=null;
                if (noalyss_strlentrim(${'ld'.$i})!=0)
                    $acc_op->desc=${'ld'.$i};
                    
                // Amount in default currency , usually EUR
                $acc_op->amount=round(bcdiv(${'amount'.$i},$currency_rate),2);
                $acc_op->grpt=$seq;
                $acc_op->poste=$poste;
                $acc_op->jrn=$this->id;
                $acc_op->type=(isset(${'ck'.$i}))?'d':'c';
                $acc_op->qcode=$quick_code;
                $j_id=$acc_op->insert_jrnx();
                
                // Save in currency
                $operation_currency=new Operation_currency_SQL($this->db);
                if (isNumber(${'amount'.$i}) == 0) {
                    $operation_currency->oc_amount=0;
                }else {
                    $operation_currency->oc_amount=round(${'amount'.$i}, 2);
                }
                $operation_currency->oc_vat_amount=0;
                $operation_currency->oc_price_unit=0;
                $operation_currency->j_id=$j_id;
                $operation_currency->insert();
                
                $tot_amount=bcadd($tot_amount,round($acc_op->amount, 2));
                
                if ( $acc_op->type == 'd') {
                    $tot_deb=bcadd($tot_deb, $acc_op->amount);
                }elseif ( $acc_op->type == 'c') {
                    $tot_cred=bcadd($tot_cred,$acc_op->amount);
                }
                
                if ($g_parameter->MY_ANALYTIC!="nu")
                {
                    if ($g_parameter->match_analytic( $poste)==TRUE)
                    {

                        // for each item, insert into operation_analytique */
                        $op=new Anc_Operation($this->db);
                        $op->set_currency_rate($currency_rate);
                        $op->oa_group=$group;
                        $op->j_id=$j_id;
                        $op->oa_date=$e_date;
                        $op->oa_debit=($acc_op->type=='d' )?'t':'f';
                        $op->oa_description=$desc;
                        
                        // send the amount in default currency to analytic
                        $an_array=$p_array;
                        $an_array['amount'.$i]=$acc_op->amount;
                        $op->save_form_plan($an_array, $count, $j_id);
                        $count++;
                    }
                }
            }// loop for each item
            $acc_end=new Acc_Operation($this->db);
            // Check the balance 
            if ( bcsub($tot_deb,$tot_cred,2) != 0 && $currency_code != 0) 
            {
                
                $diff=bcsub($tot_cred, $tot_deb);
                // store the difference in currency_rounded_delta
                $poste_cred = $g_parameter->MY_DEFAULT_ROUND_ERROR_CRED;
                $side="c";
                if ( $diff > 0 )
                {
                    $poste=$g_parameter->MY_DEFAULT_ROUND_ERROR_DEB;
                    $side="d";
                    
                }
                
                // insert difference of change
                $acc_change=new Acc_Operation($this->db);
                $acc_change->amount=abs($diff);
                $acc_change->grpt=$seq;
                $acc_change->poste=$poste;
                $acc_change->jrn=$this->id;
                $acc_change->type=$side;
                $acc_change->date=$e_date;
                $acc_change->desc=_("Différence de change");
                
                $change_j_id=$acc_change->insert_jrnx();
                
                $tot_deb=bcadd($tot_deb,$diff);
                
            }
            $acc_end->amount=$tot_deb;
            if ($check_periode==false)
            {
                $acc_end->periode=$oPeriode->p_id;
            }
            $acc_end->date=$e_date;
            $acc_end->desc=$desc;
            $acc_end->grpt=$seq;
            $acc_end->jrn=$this->id;
            $acc_end->mt=$mt;
            $acc_end->jr_optype=$jr_optype;
            $acc_end->currency_id=$currency_code;
            $acc_end->currency_rate=$currency_rate;
            $acc_end->currency_rate_ref=$currency_rate_ref->get_rate();
            
            // @var $jr_id (int) JRN.JR_ID
            $jr_id=$acc_end->insert_jrn();
            
            $this->jr_id=$jr_id;
            if ($jr_id==false)
                throw new Exception(_('Balance incorrecte'));
            $acc_end->pj=$e_pj;
            $this->pj=$acc_end->update_receipt();
            /* if e_suggest != e_pj then do not increment sequence */
            if ($this->pj == $e_pj_suggest &&noalyss_strlentrim($e_pj)!=0)
            {
                $this->inc_seq_pj();
            }



            $this->db->exec_sql("update jrn set jr_internal=$1 
                        where jr_grpt_id = $2",array($internal,$seq));

            $this->jr_internal=$internal;
            // Save now the predef op
            //------------------------
            if (isset($opd_name)&&trim($opd_name)!="")
            {
                $opd=new Pre_operation($this->db);
                $opd->set_od_direct('t');
                $opd->get_post();
                $opd->save();
            }

            if (isset($this->with_concerned)&&$this->with_concerned==true)
            {
                $orap=new acc_reconciliation($this->db);
                $orap->jr_id=$jr_id;

                $orap->insert($jrn_concerned);
            }
            /**
             * Save the file is any
             */
            if (isset($_FILES["pj"]))
            {
                $acc_document=new Acc_Document($this->db, $jr_id);
                $acc_document->save_receipt();
            }
            /*----------------------------------------------
             * Save the note
             ----------------------------------------------*/
            if (isset($p_array['jrn_note_input']) && !empty($p_array['jrn_note_input'])) {
                $acc_operation_note=Acc_Operation_Note::build_jrn_id(-1);
                $acc_operation_note->setNote($p_array['jrn_note_input']);
                $acc_operation_note->setOperation_id( $jr_id);
                $acc_operation_note->save();
            }
        }
        catch (Exception $e)
        {
            record_log($e);
            $this->db->rollback();
            echo_warning(_('OPERATION ANNULEE voir log'));
            exit();
        }
        $this->db->commit();
        return true;
    }

    /**
     * @brief retrieve the next number for this type of ledger
     * @param  p_cn connx
     * @param  p_type ledger type
     *
     * \return the number
     *
     *
     */
    static function next_number($p_cn, $p_type)
    {

        $Ret=$p_cn->count_sql("select * from jrn_def where jrn_def_type='".$p_type."'");
        return $Ret+1;
    }

    /**
     * @brief get the first ledger
     * @param  type
     * @return the j_id or null if no user available
     */
    public function get_first($p_type, $p_access=3)
    {
        global $g_user;
        $all=$g_user->get_ledger($p_type, $p_access,false);
        if (empty ($all)) return NULL;
        return $all[0];
    }

    /**
     * @brief Update the paiment  in the list of operation
     * @param  $p_array is normally $_GET
     */
    function update_paid($p_array)
    {
        // reset all the paid flag because the checkbox is post only
        // when checked
        foreach ($p_array as $name=> $paid)
        {
            list($ad)=sscanf($name, "set_jr_id%d");
            if ($ad==null)
                continue;
            $sql="update jrn set jr_rapt='' where jr_id=$ad";
            $Res=$this->db->exec_sql($sql);
        }
        // set a paid flag for the checked box
        foreach ($p_array as $name=> $paid)
        {
            list ($id)=sscanf($name, "rd_paid%d");
            if ($id==null)
                continue;

            $sql="update jrn set jr_rapt='paid' where jr_id=$id";
            $Res=$this->db->exec_sql($sql);
        }
    }

    function update_internal_code($p_internal)
    {
        if (!isset($this->jr_grpt_id))
            throw new Exception(('ERREUR jr_grpt_id not set '.__FILE__.":".__LINE__));
        $Res=$this->db->exec_sql("update jrn set jr_internal=$1 where 
                 jr_grpt_id = $2 ",array($p_internal,$this->jr_grpt_id));
    }

    /**
     * Return an array of default card for the ledger type given 
     * 
     * @param $p_ledger_type VEN ACH ODS or FIN
     * @param $p_side   D for Debit or C for credit or NA No Applicable
     */
    function get_default_card($p_ledger_type, $p_side)
    {
        $array=array();
        $fiche_def_ref=new Fiche_Def_Ref($this->db);
        // ----- for FINANCIAL  ----
        if ($p_ledger_type=='FIN')
        {
            $array=$fiche_def_ref->get_by_modele(FICHE_TYPE_CLIENT);
            $array=array_merge($array,
                    $fiche_def_ref->get_by_modele(FICHE_TYPE_FOURNISSEUR));
            $array=array_merge($array,
                    $fiche_def_ref->get_by_modele(FICHE_TYPE_FIN));
            $array=array_merge($array,
                    $fiche_def_ref->get_by_modele(FICHE_TYPE_ADM_TAX));
            $array=array_merge($array,
                    $fiche_def_ref->get_by_modele(FICHE_TYPE_EMPL));
        }
        // --- for miscellaneous ----
        if ($p_ledger_type=='ODS')
        {
            $result=$this->db->get_array('select fd_id from fiche_def');
            for ($i=0; $i<count($result); $i++)
            {
                $array[$i]=$result[$i]['fd_id'];
            }
        }
        if ($p_side=='D')
        {
            switch ($p_ledger_type)
            {
                case 'VEN':
                    $array=$fiche_def_ref->get_by_modele(FICHE_TYPE_CLIENT);
                    break;
                case 'ACH':
                    $array=$fiche_def_ref->get_by_modele(FICHE_TYPE_ACH_SER);
                    $array=array_merge($array,
                            $fiche_def_ref->get_by_modele(FICHE_TYPE_ACH_MAR));
                    $array=array_merge($array,
                            $fiche_def_ref->get_by_modele(FICHE_TYPE_ACH_MAT));
                    break;
                default :
                    throw new Exception(_('get_default_card p_ledger_side is invalide ['.$p_ledger_type.']'));
            }
        }
        elseif ($p_side=='C')
        {
            switch ($p_ledger_type)
            {
                case 'VEN':
                    $array=$fiche_def_ref->get_by_modele(FICHE_TYPE_VENTE);
                    break;
                case 'ACH':
                    $array=array_merge($array,
                            $fiche_def_ref->get_by_modele(FICHE_TYPE_ADM_TAX));
                    $array=array_merge($array,
                            $fiche_def_ref->get_by_modele(FICHE_TYPE_FOURNISSEUR));
                    break;
                default :
                    throw new Exception(_('get_default_card p_ledger_side is invalide ['.$p_ledger_type.']'));
            }
        }
        return $array;
        /*
          $return=array();
          $return = array_values($array);
          for ($i = 0;$i<count($array);$i++ )
          {
          $return[$i]=$array[$i]['fd_id'];
          }
          return $return;
         * 
         */
    }

    /**
     * @brief retrieve all the card for this type of ledger, make them
     * into a string separated by comma
     * @return all the card or null is nothing is found
     */
    function get_all_fiche_def()
    {
        $sql="select jrn_def_fiche_deb as deb,jrn_def_fiche_cred as cred ".
                " from jrn_def where ".
                " jrn_def_id = $1 ";

        $r=$this->db->exec_sql($sql, array($this->id));

        $res=Database::fetch_all($r);
        if (empty($res))
            return null;
        $card="";
        $comma='';
        foreach ($res as $item)
        {
            if (noalyss_strlentrim($item['deb'])!=0)
            {
                $card.=$comma.$item['deb'];
                $comma=',';
            }
            if (strlen(noalyss_trim($item['cred']))!=0)
            {
                $card.=$comma.$item['cred'];
                $comma=',';
            }
        }

        return $card;
    }

    /**
     * @brief Check if a Dossier is using the strict mode or not
     * \return true if we are using the strict_mode
     */
    function check_strict()
    {
        global $g_parameter;
        if ($g_parameter->MY_STRICT=='Y')
            return true;
        if ($g_parameter->MY_STRICT=='N')
            return false;
        throw new Exception("Valeur invalid ".__FILE__.':'.__LINE__);
    }

    /**
     * @brief Check if a Dossier is using the check on the periode, if true than the user has to enter the date
     * and the periode, it is a security check
     * \return true if we are using the double encoding (date+periode)
     */
    function check_periode()
    {
        global $g_parameter;
        if ($g_parameter->MY_CHECK_PERIODE=='Y')
            return true;
        if ($g_parameter->MY_CHECK_PERIODE=='N')
            return false;
        throw new Exception("Valeur invalid ".__FILE__.':'.__LINE__);
    }
    /**
     * Check that the currency code  does exist and the setting of the folder is correct
     * 
     * @param int $p_currency_code
     * @throws Exception
     */
    function check_currency_setting ($p_currency_code)
    {
        global $g_parameter;
          if ( $p_currency_code == -1 )
        {
            throw new Exception(_('Devise inconnue'), 3);
        }
        /* -- check the accounting for error of exchange -*/
        if ($p_currency_code >  0 )
        {
            $poste=new Acc_Account($this->db,$g_parameter->MY_DEFAULT_ROUND_ERROR_DEB);
           
            if ($poste->get_parameter("id") == -1 )
            {
                throw new Exception(
                        sprintf(_("Dans COMPANY, vous n'avez pas paramétré correctement ".
                                " le compte de débit %s pour les erreurs de conversion"),
                                $g_parameter->MY_DEFAULT_ROUND_ERROR_DEB), 3);
            }
            
            $poste=new Acc_Account($this->db,$g_parameter->MY_DEFAULT_ROUND_ERROR_CRED);
            if ($poste->get_parameter("id") == -1 )
            {
                throw new Exception(
                        sprintf(_("Dans COMPANY, vous n'avez pas paramétré correctement ".
                                " le compte de crédit %s pour les erreurs de conversion"),
                                $g_parameter->MY_DEFAULT_ROUND_ERROR_CRED), 3);
            }

        }        
    }
    /**
     * When we write a record for the payment at the same time as a sale or a purchase, to have a 
     * bank saldo reliable , all the bank operation must be in the same currency
     * Operation = Currency 1 and Bank = Currency 2 then it must failed , except if currency 2 (of the bank is the 
     * default currency
     * @param string $p_qcode_payment Qcode of the payment card
     * @param int  $p_currency_id currency id of the sale/purchase operation
     * @throws Exception
     */
    function check_currency($p_qcode_payment, $p_currency_id)
    {
        $card=new Fiche($this->db);
        $card->get_by_qcode($p_qcode_payment);
        if ( $card->id == 0) throw new Exception (_("Fiche invalide"));
        
        $ledger = $card->get_bank_ledger();
        if ( $ledger != NULL )
        {
            $ledger_currency_id=$ledger->get_currency()->get_id();
            // if sale and payment are not the same currency and the 
            if ($ledger_currency_id != 0 && $p_currency_id != $ledger_currency_id )
            {
                throw new Exception (_("Devise de la banque doit être identique à l'opération"));
            }
        }
    }
    /**
     * @brief get the date of the last operation
     */
    function get_last_date()
    {
        if ($this->id==0)
            throw new Exception(__FILE__.":".__LINE__."Journal incorrect ");
        $sql="select to_char(max(jr_date),'DD.MM.YYYY') from jrn where jr_def_id=$1";
        $date=$this->db->get_value($sql, array($this->id));
        return $date;
    }

    /**
     * @brief retrieve the jr_id thanks the internal code, do not change
     * anything to the current object
     * @param  internal code
     * \return the jr_id or 0 if not found
     */
    function get_id($p_internal)
    {
        $sql='select jr_id from jrn where jr_internal=$1';
        $value=$this->db->get_value($sql, array($p_internal));
        if ($value=='')
            $value=0;
        return $value;
    }

    /**
     * @brief alias for Acc_Document->create_document 
     * @param  $internal is the internal code
     * @param  $p_array is normally the $_POST
       @see Acc_Document::create_document
     * @return html string
     */
    function create_document($internal, $p_array)
    {
        $id=$this->db->get_value('select jr_id from jrn where jr_internal=$1',
                [$internal]);
        if ( $id == "") {
            return;
        }
        $acc_document=new Acc_Document($this->db,$id);
        $acc_document->create_document($internal,$p_array);
        return h($acc_document->d_name.' ('.$acc_document->d_filename.')');
    }

    /**
     * @brief check if the payment method is valid
     * @param  $e_mp is the value and $e_mp_qcode is the quickcode
     * \return nothing throw an Exception
     */
    public function check_payment($e_mp, $e_mp_qcode)
    {
        /*   Check if the "paid by" is empty, */
        if ($e_mp!=0)
        {
            /* the paid by is not empty then check if valid */
            $empl=new Fiche($this->db);
            $empl->get_by_qcode($e_mp_qcode);
            if ($empl->empty_attribute(ATTR_DEF_ACCOUNT)==true)
            {
                throw new Exception(_("Le moyen de paiement choisi n'a pas de poste comptable"),
                20);
            }
            /* get the account and explode if necessary */
            $sposte=$empl->get_attribute(ATTR_DEF_ACCOUNT);
            // if 2 accounts, take only the debit one for customer
            if (strpos($sposte, ',')!=0)
            {
                $array=explode(',', $sposte);
                $poste_val=$array[0];
            }
            else
            {
                $poste_val=$sposte;
            }
            $poste=new Acc_Account_Ledger($this->db, $poste_val);
            if ($poste->load()==false)
            {
                throw new Exception(sprintf(_("Pour la fiche %s le poste comptable [%s] n'existe pas"),
                        $empl->quick_code, $poste->id), 9);
            }
        }
    }

    /**
     * @brief increment the sequence for the pj */
    function inc_seq_pj()
    {
        $sql="select nextval('s_jrn_pj".$this->id."')";
        $this->db->exec_sql($sql);
    }

  

    /**
     * @brief return the last p_limit operation into an array
     * @param$p_limit is the max of operation to return
     * \return $p_array of Follow_Up object
     */
    function get_last($p_limit)
    {
        global $g_user;
        $filter_ledger=$g_user->get_ledger_sql('ALL', 3);
        $filter_ledger=noalyss_str_replace('jrn_def_id', 'jr_def_id', $filter_ledger);
        $sql="
			select jr_id,jr_pj_number,jr_date,to_char(jr_date,'DD.MM.YYYY') as jr_date_fmt,jr_montant, jr_comment,jr_internal,jrn_def_code
			from jrn
			join jrn_def on (jrn_def_id=jr_def_id)
			 where $filter_ledger
			order by jr_date desc , substring(jr_pj_number,'[0-9]+$')::numeric desc limit $p_limit";
        $array=$this->db->get_array($sql);
        return $array;
    }

    /**
     * @brief retreive the jr_grpt_id from a ledger
     * @param $p_what the column to seek
     *    possible values are
     *   - internal
     * @param $p_value the value of the col.
     */
    function search_group($p_what, $p_value)
    {
        switch ($p_what)
        {
            case 'internal':
                return $this->db->get_value('select jr_grpt_id from jrn where jr_internal=$1',
                                array($p_value));
        }
    }

    /**
     * @brief retrieve operation from  jrn
     * @param $p_from periode (id)
     * @param $p_to periode (id)
     */
    function get_operation($p_from, $p_to)
    {
        global $g_user;
        $jrn=($this->id==0)?'and '.$g_user->get_ledger_sql():' and jr_def_id = '.$this->id;
        $sql="select jr_id as id ,jr_internal as internal, ".
                "jr_pj_number as pj,jr_grpt_id,".
                " to_char(jr_date,'DDMMYY') as date_fmt, ".
                " jr_comment as comment, jr_montant as montant ,".
                " jr_grpt_id,jr_def_id,jrn.currency_id,currency_rate,currency_rate_ref,cr_code_iso ".
                " from jrn join jrn_def on (jr_def_id=jrn_def_id) ".
                " left join currency on (currency.id=jrn.currency_id) ".
                " where  ".
                " jr_date >= (select p_start from parm_periode where p_id = $1)
				 and  jr_date <= (select p_end from parm_periode where p_id  = $2)".
                '  '.$jrn.' order by jr_date,substring(jr_pj_number,\'[0-9]+$\')::numeric asc';
        $ret=$this->db->get_array($sql, array($p_from, $p_to));
        return $ret;
    }

    /**
     * @brief return the used VAT code with a rate > 0
     * @return Anc_Plan array of tva_id,tva_label,tva_poste
     */
    public function existing_vat()
    {
        $array=[];
        if ($this->get_type()=='ACH')
        {
            $array=$this->db->get_array("select tva_id,tva_label,tva_poste 
                    from tva_rate 
                    where tva_rate != 0.0000 
                         and  exists (select qp_vat_code from quant_purchase
                                        where  
                                        qp_vat_code=tva_id 
                                        and  exists (select j_id 
                                            from jrnx where j_jrn_def = $1)) 
                    order by tva_id",
                    array($this->id));
        }
        if ($this->get_type()=='VEN')
        {
            $array=$this->db->get_array("select tva_id,tva_label,tva_poste 
                    from tva_rate 
                    where tva_rate != 0.0000 
                          and  exists (select qs_vat_code from quant_sold
                                        where  qs_vat_code=tva_id 
                                            and  
                                                exists (select j_id from jrnx where j_jrn_def = $1)) 
                    order by tva_id",
                    array($this->id));
        }
        return $array;
    }

    /**
     * @brief get the amount of vat for a given jr_grpt_id from the table
     * quant_purchase
     * @param the jr_grpt_id
     * @return array price=htva, [1] =  vat,
     * @note
     * @see
      @code
      array
      'price' => string '91.3500' (length=7)
      'vat' => string '0.0000' (length=6)
      'priv' => string '0.0000' (length=6)
      'tva_nd_recup' => string '0.0000' (length=6)

      @endcode
     */
    function get_other_amount($p_jr_id)
    {
        if ($this->get_type()=='ACH')
        {
            $array=$this->db->get_array('select sum(qp_price) as price,sum(qp_vat) as vat '.
                    ',sum(coalesce(qp_nd_amount,0)+coalesce(qp_dep_priv,0)) as priv'.
                    ',sum(coalesce(qp_nd_tva_recup,0)+coalesce(qp_nd_tva,0)) as tva_nd'.
                    ',sum(qp_vat_sided)  as tva_np'.
                    '  from quant_purchase join jrnx using(j_id)
                                        where  j_grpt=$1 ', array($p_jr_id));
            $ret=$array[0];
        }
        if ($this->get_type()=='VEN')
        {
            $array=$this->db->get_array('select sum(qs_price) as price,sum(qs_vat) as vat '.
                    ',0 as priv'.
                    ',0 as tva_nd'.
                    ',sum(qs_vat_sided)  as tva_np'.
                    '  from quant_sold join jrnx using(j_id)
                                        where  j_grpt=$1 ', array($p_jr_id));
            $ret=$array[0];
        }
        return $ret;
    }

    /**
     * @brief get the amount of vat for a given jr_grpt_id from the table
     * quant_purchase
     * @param the jr_grpt_id
     * @return array of sum_vat, tva_label
     * @note
     * @see
      @code

      @endcode
     */
    function vat_operation($p_jr_id)
    {
        if ($this->get_type()=='ACH')
        {
            $array=$this->db->get_array('select coalesce(sum(qp_vat),0) as sum_vat,tva_id
                                        from quant_purchase as p right join tva_rate on (qp_vat_code=tva_id)  join jrnx using(j_id)
                                        where tva_rate !=0.0 and j_grpt=$1 group by tva_id',
                    array($p_jr_id));
        }
        if ($this->get_type()=='VEN')
        {
            $array=$this->db->get_array('select coalesce(sum(qs_vat),0) as sum_vat,tva_id
                                        from quant_sold as p right join tva_rate on (qs_vat_code=tva_id)  join jrnx using(j_id)
                                        where tva_rate !=0.0 and j_grpt=$1 group by tva_id',
                    array($p_jr_id));
        }
        return $array;
    }

    /**
     * @brief retrieve amount of previous periode
     * @param $p_to frmo the start of the exercise until $p_to
     * @return $array with vat, price,other_amount
     * @note
     * @see
      @code
      array
      'price' => string '446.1900' (length=8)
      'vat' => string '21.7600' (length=7)
      'priv' => string '0.0000' (length=6)
      'tva_nd_recup' => string '0.0000' (length=6)
      'tva' =>
      array
      0 =>
      array
      'sum_vat' => string '13.7200' (length=7)
      'tva_id' => string '1' (length=1)
      1 =>
      array
      'sum_vat' => string '8.0400' (length=6)
      'tva_id' => string '3' (length=1)
      2 =>
      array
      'sum_vat' => string '0.0000' (length=6)
      'tva_id' => string '4' (length=1)

      @endcode
     */
    function previous_amount($p_to)
    {
        /* get the first periode of exercise */
        $periode=new Periode($this->db, $p_to);
        $exercise=$periode->get_exercice();
        list ($min, $max)=$periode->get_limit($exercise);
        // transform min into date
        $min_date=$min->first_day();
        // transform $p_to  into date
        $periode_max=new Periode($this->db, $p_to);
        $max_date=$periode_max->first_day();
        bcscale(2);
        // min periode
        if ($this->get_type()=='ACH')
        {
            /*  get all amount exclude vat */
            $sql="select coalesce(sum(qp_price),0) as price".
                    " ,coalesce(sum(qp_vat),0) as vat ".
                    ',coalesce(sum(qp_dep_priv),0) as priv'.
                    ',coalesce(sum(qp_vat_sided),0) as reversed'.
                    ',coalesce(sum(qp_nd_tva_recup),0)+coalesce(sum(qp_nd_tva),0) as tva_nd'.
                    ',coalesce(sum(qp_vat_sided),0) as tva_np'.
                    '  from quant_purchase join jrnx using(j_id) '.
                    " where j_date >= to_date($1,'DD.MM.YYYY') and j_date < to_date($2,'DD.MM.YYYY') ".
                    ' and j_jrn_def = $3';
            $array=$this->db->get_array($sql,
                    array($min_date, $max_date, $this->id));

            $ret=$array[0];
            /* retrieve all vat code */
            $array=$this->db->get_array("select coalesce(sum(qp_vat),0) as sum_vat,tva_id
                                        from quant_purchase as p 
                                            right join tva_rate on (qp_vat_code=tva_id)  join jrnx using(j_id)
                                        where 
                                            tva_rate !=0 
                                            and  j_date >= to_date($1,'DD.MM.YYYY') 
                                            and j_date < to_date($2,'DD.MM.YYYY') 
                                        and j_jrn_def = $3
                                        group by tva_id",
                    array($min_date, $max_date, $this->id));
            $ret['tva']=$array;
        }
        if ($this->get_type()=='VEN')
        {
            /*  get all amount exclude vat */
            $sql="select coalesce(sum(qs_price),0) as price".
                    " ,coalesce(sum(qs_vat),0) as vat ".
                    ',0 as priv'.
                    ',0 as tva_nd'.
                    ',coalesce(sum(qs_vat_sided),0) as reversed'.
                    ',0 as tva_np'.
                    '  from quant_sold join jrnx using(j_id) '.
                    " where j_date >= to_date($1,'DD.MM.YYYY') and j_date < to_date($2,'DD.MM.YYYY') ".
                    ' and j_jrn_def = $3';
            $array=$this->db->get_array($sql,
                    array($min_date, $max_date, $this->id));
            $ret=$array[0];
            /* retrieve all vat code */
            $array=$this->db->get_array("select coalesce(sum(qs_vat),0) as sum_vat,tva_id
                                        from quant_sold as p 
                                            right join tva_rate on (qs_vat_code=tva_id)  
                                            join jrnx using(j_id)
                                        where 
                                             tva_rate !=0 and
                                             j_date >= to_date($1,'DD.MM.YYYY') 
                                        and  j_date < to_date($2,'DD.MM.YYYY') 
                                        and  j_jrn_def = $3
                                        group by tva_id",
                    array($min_date, $max_date, $this->id));
            $ret['tva']=$array;
        }
        if ($this->get_type()=="FIN")
        {

            /* find the quick code of this ledger */
            $ledger=new Acc_Ledger_Fin($this->db, $this->id);
            $qcode=$ledger->get_bank();
            $bank_card=new Fiche($this->db, $qcode);
            $periode=new Periode($this->db);
            //$periode->find_periode($min_date);
            //$a_date=$periode->get_limit($periode->get_exercice());
            
            /* add the amount from Opening Writing                  */
            if ( $min_date <> $max_date)
            {
                $cond=sprintf("j_date >=  to_date('%s','DD.MM.YYYY') and j_date < to_date('%s','DD.MM.YYYY')  ",
                     $min_date,$max_date);
            }else{
                $cond=sprintf("j_date =  to_date('%s','DD.MM.YYYY')   ",
                     $min_date);
            }
            $saldo=$bank_card->get_bk_balance($cond);
            $ret['amount']=noalyss_bcsub($saldo['debit'], $saldo['credit']);
        }
        return $ret;
    }

    /**
     * @brief retrieve the previous amount
     * @param $p_to from the start of exercice until p_to
     * @return array [other_tax_amount]
     */
    function previous_other_tax($p_to) {
        $periode=new Periode($this->db, $p_to);
        $exercise=$periode->get_exercice();
        list ($min, $max)=$periode->get_limit($exercise);
        // transform min into date
        $min_date=$min->first_day();
        // transform $p_to  into date
        $periode_max=new Periode($this->db, $p_to);
        $max_date=$periode_max->first_day();
        bcscale(2);
        // min periode
        if ($this->get_type()=='ACH')
        {

            $sql=" 
                select j_montant 
                  from quant_purchase 
                      join jrnx using(j_id)
                    join jrn_tax using (j_id)
                 where j_date >= to_date($1,'DD.MM.YYYY') and j_date < to_date($2,'DD.MM.YYYY') 
                 and j_jrn_def = $3";
            $amount=$this->db->get_value($sql,
                array($min_date, $max_date, $this->id));

            return array('other_tax_amount',$amount);

        }
        if ($this->get_type()=='VEN')
        {

            $sql=" 
                select j_montant 
                  from quant_sold 
                      join jrnx using(j_id)
                    join jrn_tax using (j_id)
                 where j_date >= to_date($1,'DD.MM.YYYY') and j_date < to_date($2,'DD.MM.YYYY') 
                 and j_jrn_def = $3";
            $amount=$this->db->get_value($sql,
                array($min_date, $max_date, $this->id));

            return array('other_tax_amount',$amount);

        }
        return array('other_tax_amount',0);
    }
    ////////////////////////////////////////////////////////////////////////////////
    // TEST MODULE
    ////////////////////////////////////////////////////////////////////////////////
    /**
     * @brief this function is intended to test this class
     */
    static function test_me($pCase='')
    {
        if ($pCase=='')
        {
            echo Acc_Reconciliation::$javascript;
            html_page_start();
            $cn=Dossier::connect();
            $_SESSION[SESSION_KEY.'g_user']=NOALYSS_ADMINISTRATOR;
            $_SESSION[SESSION_KEY.'g_pass']='phpcompta';

            $id=(isset($_REQUEST['p_jrn']))?$_REQUEST['p_jrn']:-1;
            $a=new Acc_Ledger($cn, $id);
            $a->with_concerned=true;
            // Vide
            echo '<FORM method="post">';
            echo $a->select_ledger()->input();
            echo HtmlInput::submit('go', 'Test it');
            echo '</form>';
            if (isset($_POST['go']))
            {
                echo "Ok ";
                echo '<form method="post">';
                echo $a->show_form();
                echo HtmlInput::submit('post_id', 'Try me');
                echo '</form>';
                // Show the predef operation
                // Don't forget the p_jrn
                echo '<form>';
                echo dossier::hidden();
                echo '<input type="hidden" value="'.$id.'" name="p_jrn">';
                $op=new Pre_operation($cn);
                $op->set_p_jrn($id);

                if ($op->count()!=0)
                {
                    echo HtmlInput::submit('use_opd',
                            'Utilisez une opération pr&eacute;d&eacute;finie',
                            "", "smallbutton");
                    echo $op->show_button();
                }
                echo '</form>';
                exit('test_me');
            }

            if (isset($_POST['post_id']))
            {

                echo '<form method="post">';
                echo $a->show_form($_POST, 1);
                echo HtmlInput::button('add', 'Ajout d\'une ligne',
                        'onClick="quick_writing_add_row()"');
                echo HtmlInput::submit('save_it', _("Sauver"));
                echo '</form>';
                exit('test_me');
            }
            if (isset($_POST['save_it']))
            {
                print 'saving';
                $array=$_POST;
                $array['save_opd']=1;
                try
                {
                    $a->save($array);
                }
                catch (Exception $e)
                {
                    alert($e->getMessage());
                    echo '<form method="post">';

                    echo $a->show_form($_POST);
                    echo HtmlInput::submit('post_id', 'Try me');
                    echo '</form>';
                }
                return;
            }
            // The GET at the end because automatically repost when you don't
            // specify the url in the METHOD field
            if (isset($_GET['use_opd']))
            {
                $op=new Pre_op_advanced($cn);
                $op->set_od_id($_REQUEST['pre_def']);
                //$op->p_jrn=$id;

                $p_post=$op->compute_array();

                echo '<FORM method="post">';

                echo $a->show_form($p_post);
                echo HtmlInput::submit('post_id', 'Use predefined operation');
                echo '</form>';
                return;
            }
        }// if case = ''
        ///////////////////////////////////////////////////////////////////////////
        // search
        if ($pCase=='search')
        {
            html_page_start();
            $cn=Dossier::connect();
            $ledger=new Acc_Ledger($cn, 0);
            $_SESSION[SESSION_KEY.'g_user']=NOALYSS_ADMINISTRATOR;
            $_SESSION[SESSION_KEY.'g_pass']='phpcompta';
            echo $ledger->search_form('ALL');
        }
        ///////////////////////////////////////////////////////////////////////////
        // reverse
        // Give yourself the var and check in your tables
        ///////////////////////////////////////////////////////////////////////////
        if ($pCase=='reverse')
        {
            $cn=Dossier::connect();
            $jr_internal='OD-01-272';
            try
            {
                $cn->start();
                $jrn_def_id=$cn->get_value('select jr_def_id from jrn where jr_internal=$1',
                        array($jr_internal));
                $ledger=new Acc_Ledger($cn, $jrn_def_id);
                $ledger->jr_id=$cn->get_value('select jr_id from jrn where jr_internal=$1',
                        array($jr_internal));

                echo "Ouvrez le fichier ".__FILE__." à la ligne ".__LINE__." pour changer jr_internal et vérifier le résultat de l'extourne";

                $ledger->reverse('01.07.2010');
            }
            catch (Exception $e)
            {
                $cn->rollback();
                  record_log($e);
            }
            $cn->commit();
        }
    }

    /**
     * create an array of the existing cat, to be used in a checkbox form
     *
     */
    static function array_cat()
    {
        $r=array(
            array('cat'=>'VEN', 'name'=>_("Journaux de vente")),
            array('cat'=>'ACH', 'name'=>_("Journaux d'achat")),
            array('cat'=>'FIN', 'name'=>_("Journaux Financier")),
            array('cat'=>'ODS', 'name'=>_("Journaux d'Opérations diverses"))
        );
        return $r;
    }

    //---------------------------------------------------------------------
    /// Return the f_id of the tiers , called by get_tiers
    //!\param $p_jrn_type type of the ledger FIN, VEN ACH or ODS
    //!\param $jr_id jrn.jr_id
    //---------------------------------------------------------------------
    function get_tiers_id($p_jrn_type, $jr_id)
    {
        $tiers=0;
        switch ($p_jrn_type)
        {
            case 'VEN':
                $tiers=$this->db->get_value('select max(qs_client) from quant_sold join jrnx using (j_id) join jrn on (jr_grpt_id=j_grpt) where jrn.jr_id=$1',
                        array($jr_id));
                break;
            case 'ACH':
                $tiers=$this->db->get_value('select max(qp_supplier) from quant_purchase join jrnx using (j_id) join jrn on (jr_grpt_id=j_grpt) where jrn.jr_id=$1',
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
     * Retrieve the third : supplier for purchase, customer for sale, bank for fin,
     * @param $p_jrn_type type of the ledger FIN, VEN ACH or ODS
     * @param $jr_id jrn.jr_id
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
     * @brief listing of all ledgers
     * @return HTML string
     */
    function listing()
    {
        $str_dossier=dossier::get();
        $base_url="?".dossier::get()."&ac=".$_REQUEST['ac'];

        $r="";
        $r.=_('Cherche')." ".HtmlInput::filter_table("cfgledger_table_id", "0",
                        "1");
        $r.='<TABLE id="cfgledger_table_id" class="vert_mtitle">';
        $r.='<TR><TD class="first"><A HREF="'.$base_url.'&sa=add">'._('Ajout journal').' </A></TD></TR>';
        $ret=$this->db->exec_sql("select distinct jrn_def_id,jrn_def_name,
                       jrn_def_class_deb,jrn_def_class_cred,jrn_def_type
                       from jrn_def order by jrn_def_name");
        $Max=Database::num_row($ret);


        for ($i=0; $i<$Max; $i++)
        {
            $l_line=Database::fetch_array($ret, $i);
            $url=$base_url."&sa=detail&p_jrn=".$l_line['jrn_def_id'];
            $r.=sprintf('<TR ledger_type="%s"><TD><A HREF="%s">%s</A></TD></TR>', $l_line['jrn_def_type'],$url,
                    h($l_line['jrn_def_name']).' ('.$l_line['jrn_def_type'].')');
        }
        $r.="</TABLE>";
        return $r;
    }

    /**
     * display detail of a ledger
     *
     */
    function display_ledger()
    {
        if ($this->load()==false)
        {
            throw new Exception(_("Journal n'existe pas"), -1);
        }
        $type=$this->jrn_def_type;
        $name=$this->jrn_def_name;
        $code=$this->jrn_def_code;
        $str_add_button="";
        /* widget for searching an account */
        $wSearch=new IPoste();
        $wSearch->set_attribute('ipopup', 'ipop_account');
        $wSearch->set_attribute('account', 'p_jrn_class_deb');
        $wSearch->set_attribute('no_overwrite', '1');
        $wSearch->set_attribute('noquery', '1');
        $wSearch->table=3;
        $wSearch->name="p_jrn_class_deb";
        $wSearch->size=20;
        $wSearch->value=$this->jrn_def_class_deb;
        $search=$wSearch->input();

        $wPjPref=new IText();
        $wPjPref->name='jrn_def_pj_pref';
        $wPjPref->value=$this->jrn_def_pj_pref;
        $pj_pref=$wPjPref->input();

        $wPjSeq=new INum();
        $wPjSeq->value=0;
        $wPjSeq->name='jrn_def_pj_seq';
        $pj_seq=$wPjSeq->input();
        $last_seq=$this->get_last_pj();
        $name=$this->jrn_def_name;

        $hidden=HtmlInput::hidden('p_jrn', $this->id);
        $hidden.=HtmlInput::hidden('sa', 'detail');
        $hidden.=dossier::hidden();
        $hidden.=HtmlInput::hidden('p_jrn_deb_max_line', 10);
        $hidden.=HtmlInput::hidden('p_ech_lib', 'echeance');
        $hidden.=HtmlInput::hidden('p_jrn_type', $type);

        $min_row=new INum("min_row", $this->jrn_deb_max_line);
        $min_row->prec=0;

        $description=new ITextarea('p_description');
        $description->style='class="itextarea" style="margin:0px;"';
        $description->value=$this->jrn_def_description;
        $str_description=$description->input();

        /* Load the card */
        $card=$this->get_fiche_def();
        $rdeb=noalyss_explode(',', $card['deb']);
        $rcred=noalyss_explode(',', $card['cred']);
        /* Numbering (only FIN) */
        $num_op=new ICheckBox('numb_operation');
        if ($this->jrn_def_num_op==1)
            $num_op->selected=true;
        /* bank card */
        $qcode_bank='';
        if ($type=='FIN')
        {
            $f_id=$this->jrn_def_bank;
            if (isNumber($f_id)==1)
            {
                $fBank=new Fiche($this->db, $f_id);
                $qcode_bank=$fBank->get_quick_code();
            }
        }
        $new=0;
        $cn=$this->db;
        echo $hidden;
        $actif=new ISelect("jrn_enable");
        $actif->value=[
            ["label"=>_("Activé"),"value"=>1],
            ["label"=>_("Désactivé"),"value"=>0]
        ];
        $actif->selected=$this->jrn_enable;

        // -- default currency used : only for financial ledgers
        $default_currency=$this->select_default_currency();

        $negative=new InputSwitch('negative_amount',$this->jrn_def_negative_amount);
        $negative_warning=new IText("negative_warning",_($this->jrn_def_negative_warning));
        $negative_warning->size=55;

        // use of quantity in ledger
        $quantity=new InputSwitch('p_jrn_quantity',$this->jrn_def_quantity);

        // padding
        $padding = new \INum ('p_jrn_padding',$this->jrn_def_pj_padding);
        require_once NOALYSS_TEMPLATE.'/param_jrn.php';
    }

    /**
     * @brief create a select button to set the default currency for a ledger
     * used only for empty financial ledger
     * @return ISelect object
     */
    function select_default_currency()
    {
        $default_currency=new ISelect("defaultCurrency");
        $default_currency->value=$this->db->make_array("select id,cr_code_iso from public.currency order by 1 ");
        $default_currency->selected=$this->currency_id;
        $nb_operation=$this->db->get_value("select count(*) from jrn where jr_def_id=$1",[$this->id]);
        if (  $nb_operation > 0) {
                $default_currency->setReadOnly(TRUE);
        }
        return $default_currency;
    }
    /**
     * Verify before update
     *
     * @param type
     * @code
     * $array
     *   'p_jrn' => string '3' (length=1)
      'sa' => string 'detail' (length=6)
      'gDossier' => string '82' (length=2)
      'p_jrn_deb_max_line' => string '10' (length=2)
      'p_ech_lib' => string 'echeance' (length=8)
      'p_jrn_type' => string 'ACH' (length=3)
      'p_jrn_name' => string 'Achat' (length=5)
      'jrn_def_pj_pref' => string 'ACH' (length=3)
      'jrn_def_pj_seq' => string '0' (length=1)
      'FICHECRED' =>array(fd_id)
      'FICHEDEB' =>array(fd_id)
        'update' => string 'Sauve' (length=5
     * @endcode
     * @exception is throw is test are not valid
     */
    function verify_ledger($array)
    {
        $http=new HttpInput();
        $http->set_array($array);
        $p_jrn=$http->extract('p_jrn',);
        $p_jrn_deb_max_line=$http->extract('p_jrn_deb_max_line');
        $p_jrn_name=$http->extract('p_jrn_name');
        $p_jrn_type=$http->extract('p_jrn_type');
        $p_jrn_quantity=$http->extract('p_jrn_quantity','number',0);

        try
        {
            if (isNumber($p_jrn)==0)
                throw new Exception("Id invalide");
            if (isNumber($p_jrn_deb_max_line)==0)
                throw new Exception(_("Nombre de ligne incorrect"));
            if (trim($p_jrn_name)=="")
                throw new Exception("Nom de journal invalide");
            if ($this->db->get_value("select count(*) from jrn_def where jrn_def_name=$1 and jrn_Def_id<>$2",
                            array($p_jrn_name, $p_jrn))>0)
                throw new Exception(_("Un journal avec ce nom existe déjà"));
            if ($p_jrn_type=='FIN')
            {
                $http=new \HttpInput();
                $a=new Fiche($this->db);
                $bank=$http->post("bank");
                $result=$a->get_by_qcode(trim(strtoupper($bank)), false);
                if ($result==1)
                    throw new Exception(_("Aucun compte en banque n'est donné"));
            }
            if ($p_jrn_type=="-1")
            {
                throw new Exception(_('Choix du type de journal est obligatoire'));
            }

           if ( isset( $array['negative_warning']) && 
                isset( $array['negative_amount']) &&
                $array['negative_amount'] == 1 
                   && trim($array['negative_warning'])=="") {

                throw new Exception(_("Avertissement ne peut être vide"));
            }
            if ( isset( $array['negative_amount'])  &&
                 $array['negative_amount'] <> 0 &&
                 $array['negative_amount'] <> 1 )
             {
                  throw new Exception(_("Valeur invalide {$array['negative_amount']}"));
            }
        }
        catch (Exception $e)
        {
              record_log($e);
            throw $e;
        }
    }

    /**
     * @brief update a ledger
     * @param type $array  normally post
     * @code
     * // Example for a financial ledger
     [p_jrn] => 83
    [sa] => detail
    [gDossier] => 25
    [p_jrn_deb_max_line] => 10
    [p_ech_lib] => echeance
    [p_jrn_type] => FIN
    [p_jrn_name] => Banque Privée
    [bank] => BANQUE
    [min_row] => 5
    [p_description] => Compte fermé
    [jrn_def_pj_pref] => BP19-
    [jrn_def_pj_seq] => 0
    [jrn_enable] => 0
    [FIN_FICHEDEB] =>array(fd_id)
    [defaultCurrency] => 0 // if there is no operation
    [action_frm] => update
     * @endcode
     * @see verify_ledger
     */
    function update($array=null)
    {
        $this->jrn_def_quantity=(!isset($this->jrn_def_quantity)||$this->jrn_def_quantity===null)?1:$this->jrn_def_quantity;

        if ($array==null) {
            // update with the current value
            parent::update();
            return;
        }

        $http=new HttpInput();
        $http->set_array($array);
        $p_jrn_deb_max_line=$http->extract("p_jrn_deb_max_line","number",-1);
        $min_row=$http->extract("min_row");

        $this->jrn_def_id=$http->extract('p_jrn');
        $this->jrn_def_name=$http->extract('p_jrn_name');
        $this->jrn_def_ech_lib=$http->extract('p_ech_lib');
        $this->jrn_def_max_line_deb=($p_jrn_deb_max_line<1)?1:$p_jrn_deb_max_line;
        $this->jrn_def_type=$http->extract('p_jrn_type');
        $this->jrn_def_pj_pref=$http->extract('jrn_def_pj_pref');
        $this->jrn_deb_max_line=($min_row<1)?1:$min_row;
        $this->jrn_def_description=$http->extract('p_description');
        $this->jrn_enable=$http->extract('jrn_enable');
        $this->currency_id=0;
        $this->jrn_def_negative_amount=$http->extract('negative_amount','string',0);
        $this->jrn_def_negative_warning=$http->extract("negative_warning",'string',
                _("Attention, ce journal doit utiliser des montants négatifs"));
        $this->jrn_def_quantity=$http->extract('p_jrn_quantity','string',1);
        $jrn_def_pj_seq=$http->extract("jrn_def_pj_seq");
        $this->jrn_def_pj_padding=$http->extract('p_jrn_padding','number');
        switch ($this->jrn_def_type)
        {
            case 'ACH':
                $ACH_FICHECRED=$http->extract('ACH_FICHECRED','array',array());
                $ACH_FICHEDEB=$http->extract('ACH_FICHEDEB','array',array());
                $this->jrn_def_fiche_cred=(!empty($ACH_FICHECRED))?join(',',$ACH_FICHECRED):'';
                $this->jrn_def_fiche_deb=(!empty($ACH_FICHEDEB))?join(',',$ACH_FICHEDEB):"";
                break;
            case 'VEN':
                $VEN_FICHECRED=$http->extract('VEN_FICHECRED','array',array());
                $VEN_FICHEDEB=$http->extract('VEN_FICHEDEB','array',array());
                $this->jrn_def_fiche_cred=(!empty($VEN_FICHECRED))?join(',',$VEN_FICHECRED):'';
                $this->jrn_def_fiche_deb=(!empty($VEN_FICHEDEB))?join(',',$VEN_FICHEDEB):"";

                break;
            case 'ODS':
                $this->jrn_def_class_deb=$http->extract('p_jrn_class_deb','string');
                $ODS_FICHEDEB=$http->extract('ODS_FICHEDEB','array',array());
                $this->jrn_def_fiche_deb=(!empty($ODS_FICHEDEB))?join(',',$ODS_FICHEDEB):''; ;
                $this->jrn_def_fiche_cred=null;
                break;

            case 'FIN':
                $a=new Fiche($this->db);
                $result=$a->get_by_qcode(trim(strtoupper($http->extract('bank'))), false);
                $bank=$a->id;
                $this->jrn_def_bank=$bank;
                $FIN_FICHEDEB=$http->extract('FIN_FICHEDEB','array',array());
                $this->jrn_def_fiche_deb=(!empty($FIN_FICHEDEB))?join(',',$FIN_FICHEDEB):"";
                if ($result==-1)
                    throw new Exception(_("Aucun compte en banque n'est donné"));
                $this->jrn_def_num_op=$http->extract('numb_operation','string',0);
                // if nb operation == 0 then update currency_id
                $nb_operation = $this->db->get_value("select count(*) from jrn where jr_def_id=$1",
                        [$this->jrn_def_id]);
                /*
                 * Set the default currency except if there are already operation
                 */
                if ( $nb_operation == 0 ){
                    $this->currency_id=$http->extract("defaultCurrency");
                } else {
                    $this->currency_id=$this->db->get_value("select currency_id from jrn_def where jrn_def_id=$1",
                            [$this->jrn_def_id]);
                }
                break;
        }

        parent::update();
        //Reset sequence if needed
        if ($jrn_def_pj_seq!=0)
        {
            $Res=$this->db->alter_seq("s_jrn_pj".$this->jrn_def_id, $jrn_def_pj_seq);
        }
    }
    /**
     * Create the section payment
     * @param int $p_selected
     * @param number $p_amount
     * @param date $p_date or empty string
     * @param string $p_comm or empty comm
     * @return string
     */
    function input_paid($p_selected,$p_amount=0,$p_date="",$p_comm="")
    {
        $r='';
        $r.='<div id="payment"> ';
        $r.='<h2 class="h-section"> '._('opération de paiement').' </h2>';
        $r.='<p class="text-muted">';
        $r.=_("Opération de paiement crée en plus de cette opération, ne concerne pas la facturation électronique");
        $r.='</p>';
        $mp=new Acc_Payment($this->db);
        $mp->set_parameter('ledger_source', $this->id);
        $r.=$mp->select($p_selected,$p_amount,$p_date,$p_comm);
        $r.='</div>';
        return $r;
    }

    /**
     * @brief display FORM  to enter parameters to create a new ledger.
     */
    function input_new()
    {
      	global $g_user;
        $http=new HttpInput();
        $retry=$http->post("sa", "string", "");
//            if ( $retry == "add") {
        $default_type=$http->post("p_jrn_type", "string", -1);
        $previous_jrn_def_pj_pref=$http->post("jrn_def_pj_pref", "string", "");
        $previous_p_description=$http->post("p_description", "string", "");
        $previous_p_jrn_name=$http->post('p_jrn_name', "string", '');
        $previous_p_jrn_type=$http->post("p_jrn_type", "string", "");
//            }
        $f_add_button=new ISmallButton('add_card');
        $f_add_button->label=_('Créer une nouvelle fiche');
        $f_add_button->tabindex=-1;
        $f_add_button->set_attribute('jrn', -1);
        $f_add_button->javascript=" select_card_type({type_cat:4,elementId:'bank',p_jrn:-1});";

        $str_add_button="";

        if ($g_user->check_action(FICADD)==1)
        {
            $str_add_button=$f_add_button->input();
        }
        $wSearch=new IPoste();
        $wSearch->table=3;
        $wSearch->set_attribute('ipopup', 'ipop_account');
        $wSearch->set_attribute('account', 'p_jrn_class_deb');
        $wSearch->set_attribute('no_overwrite', '1');
        $wSearch->set_attribute('noquery', '1');

        $wSearch->name="p_jrn_class_deb";
        $wSearch->size=20;

        $search=$wSearch->input();
        // default for ACH
        $default_deb_purchase=$this->get_default_card('ACH', 'D');
        $default_cred_purchase=$this->get_default_card('ACH', 'C');

        // default for VEN
        $default_deb_sale=$this->get_default_card('VEN', 'D');
        $default_cred_sale=$this->get_default_card('VEN', 'C');

        // default for FIN
        $default_fin=$this->get_default_card("FIN", "");

        //default ods
        $default_ods=$this->get_default_card("ODS", "");

        /* construct all the hidden */
        $hidden=HtmlInput::hidden('p_jrn', -1);
        $hidden.=HtmlInput::hidden('p_action', 'jrn');
        $hidden.=HtmlInput::hidden('sa', 'add');
        $hidden.=dossier::hidden();
        $hidden.=HtmlInput::hidden('p_jrn_deb_max_line', 10);
        $hidden.=HtmlInput::hidden('p_ech_lib', 'echeance');

        /* properties of the ledger */
        $name=$previous_p_jrn_name;
        $code="";
        $wType=new ISelect();
        $a_jrn=$this->db->make_array("select '-1',' -- "._("choix du type de journal")." -- ' union select jrn_type_id,jrn_desc from jrn_type");
        $wType->selected='-1';
        $wType->value=$a_jrn;
        $wType->name="p_jrn_type";
        $wType->id="p_jrn_type_select_id";
        $wType->javascript=' onchange="show_ledger_div()"';
        $wType->selected=$default_type;
        $type=$wType->input();
        $rcred=$rdeb=array();
        $wPjPref=new IText();
        $wPjPref->name='jrn_def_pj_pref';
        $wPjPref->value=$previous_jrn_def_pj_pref;
        $pj_pref=$wPjPref->input();
        $pj_seq='';
        $last_seq=0;
        $new=1;
        $description=new ITextarea('p_description');
        $description->style='class="itextarea" style="margin:0px;"';
        $description->value=$previous_p_description;
        $str_description=$description->input();
        /* bank card */
        $qcode_bank='';
        /* Numbering (only FIN) */
        $num_op=new ICheckBox('numb_operation');
        echo dossier::hidden();
        echo HtmlInput::hidden('ac', $http->request('ac'));
        echo $hidden;

        $cn=$this->db;
        $min_row=new INum("min_row", MAX_ARTICLE);
        $min_row->prec=0;
         // -- default currency used : only for financial ledgers
        $default_currency=$this->select_default_currency();
        
        $negative=new InputSwitch('negative_amount',0);
        $negative_warning=new IText('negative_warning',_("Attention, ce journal doit utiliser des montants négatifs"));
        $negative_warning->size="55";
        // use of quantity in ledger
        $quantity=new InputSwitch('p_jrn_quantity',1);
        $padding = new \INum ('p_jrn_padding',5);
        require_once NOALYSS_TEMPLATE.'/param_jrn.php';
    }

    /**
     * @brief Insert a new ledger , member variable  like jrn_def_id will changed 
     * @param array $array normally $_POST
     * @code
    Array
    (
    [gDossier] => 25
    [ac] => CFG/MACC/C0JRN
    [p_jrn] => -1
    [p_action] => jrn
    [sa] => add
    [p_jrn_deb_max_line] => 10
    [p_ech_lib] => echeance
    [p_jrn_type] => VEN
    [p_jrn_name] => test
    [p_jrn_class_deb] =>
    [bank] =>
    [negative_amount] => 0
    [negative_warning] => Attention, ce journal doit utiliser des montants négatifs
    [p_jrn_quantity] => 1
    [min_row] => 5
    [p_description] =>
    [jrn_def_pj_pref] => A
    [defaultCurrency] => 0
    [ACH_FICHECRED] => Array(fd_id)
    [ACH_FICHEDEB] => Array (fd_id)
    [VEN_FICHEDEB] => Array (fd_id)
    [VEN_FICHECRED] => Array (fd_id)
    [ODS_FICHEDEB] => Array(fd_id)
    [FIN_FICHEDEB] => Array(fd_id)
    [ defaultCurrency] =>"int"  only for Financial if there is no operation
    )
     *
     *
     * @endcode
     *
     * @see verify_ledger
     */
    function save_new($array)
    {
        $this->load();
        extract($array, EXTR_SKIP);
        $this->jrn_def_id=-1;
        $this->jrn_def_name=$p_jrn_name;
        $this->jrn_def_ech_lib=$p_ech_lib;
        $this->jrn_def_max_line_deb=$p_jrn_deb_max_line;
        $this->jrn_def_type=$p_jrn_type;
        $this->jrn_def_pj_pref=$jrn_def_pj_pref;
        $this->jrn_deb_max_line=$min_row;
        $this->jrn_def_code=trim(substr($this->jrn_def_type, 0, 1));
        $this->jrn_def_code.=str_pad(
                    base_convert(
                        Acc_Ledger::next_number($this->db, $this->jrn_def_type),10,36),2,"0",STR_PAD_LEFT);
        $this->jrn_def_code=strtoupper($this->jrn_def_code);
        $this->jrn_def_description=$p_description;
        $this->currency_id=0;
        $this->jrn_def_negative_amount=$negative_amount;
        $this->jrn_def_negative_warning=$negative_warning;
        $this->jrn_enable=1;
        $this->jrn_def_pj_padding=$p_jrn_padding;
        switch ($this->jrn_def_type)
        {
            case 'ACH':
                $this->jrn_def_fiche_cred=(isset($ACH_FICHECRED))?join(',',$ACH_FICHECRED):'';
                $this->jrn_def_fiche_deb=(isset($ACH_FICHEDEB))?join(',',$ACH_FICHEDEB):"";
                break;
            case 'VEN':
                $this->jrn_def_fiche_cred=(isset($VEN_FICHECRED))?join(',',$VEN_FICHECRED):'';
                $this->jrn_def_fiche_deb=(isset($VEN_FICHEDEB))?join(',',$VEN_FICHEDEB):"";

                break;
            case 'ODS':
                $this->jrn_def_class_deb=$p_jrn_class_deb;
                $this->jrn_def_fiche_deb=(isset($ODS_FICHEDEB))?join(',',$ODS_FICHEDEB):''; ;
                $this->jrn_def_fiche_cred=null;
                break;
            case 'FIN':
                $a=new Fiche($this->db);
                $result=$a->get_by_qcode(trim(strtoupper($bank)), false);
                $bank_id=$a->id;
                $this->jrn_def_bank=$bank_id;
                $this->jrn_def_fiche_deb=(isset($FIN_FICHEDEB))?join(',',$FIN_FICHEDEB):"";
                if ($result==-1)
                    throw new Exception(_("Aucun compte en banque n'est donné"));
                $this->jrn_def_num_op=(isset($numb_operation))?1:0;
                $this->currency_id=$defaultCurrency;
                break;
        }
        $this->jrn_def_quantity=(!isset($this->jrn_def_quantity)||$this->jrn_def_quantity==null)?1:$this->jrn_def_quantity;
        parent::insert();
        $this->id=$this->jrn_def_id;
    }

    /**
     * @brief delete a ledger IF it doesn't contain anything
     * @exception : cannot delete
     */
    function delete_ledger()
    {
        try
        {
            if ($this->db->get_value("select count(jr_id) from jrn where jr_def_id=$1",
                            array($this->jrn_def_id))>0)
                throw new Exception(_("Impossible d'effacer un journal qui contient des opérations"));
            parent::delete();
        }
        catch (Exception $e)
        {
              record_log($e);
            throw $e;
        }
    }

    /**
     * @brief Get operation from the ledger type before, after or with the
     * given date . The array is filtered by the ledgers granted to the 
     * user
     * @global type $g_user
     * @param $p_date Date (d.m.Y)
     * @param $p_ledger_type VEN ACH 
     * @param type $sql_op < > or =
     * @return array from jrn (jr_id, jr_internal, jr_date, jr_comment,jr_pj_number,jr_montant)
     * @throws Exception
     */
    function get_operation_date($p_date, $p_ledger_type, $sql_op)
    {
        global $g_user;
        switch ($p_ledger_type)
        {
            case 'ACH':
                $filter=$g_user->get_ledger_sql('ACH', 3);
                break;
            case 'VEN':
                $filter=$g_user->get_ledger_sql('VEN', 3);
                break;
            default:
                throw new Exception('Ledger_type invalid : '.$p_ledger_type);
        }
        if ( ! in_array($sql_op ,array('>','<','=','>=','<='))) {
            throw new \Exception ("AC3162 : invalid \$sql_op = [$sql_op]");
        }

        $sql="select jr_id, jr_internal, jr_date, jr_comment,jr_pj_number,jr_montant,jr_ech
				from jrn
				join jrn_def on (jrn_def_id=jr_def_id)
				where
				jr_ech is not null
				and jr_ech $sql_op to_date($1,'DD.MM.YYYY')
				and coalesce (jr_rapt,'xx') <> 'paid'
				and $filter
                order by jr_date
				";
        $array=$this->db->get_array($sql, array($p_date));
        return $array;
    }
    /**
     * @brief  Get simplified row from ledger
     * Call Acc_Ledger_History_Generic:get_rowSimple
     * @param p_from periode
     * @param p_to periode
     * @param p_limit starting line
     * @param p_offset number of lines
     * @param trunc if data must be truncated (pdf export)
     *
     * \return an Array with the asked data
     */
    function get_rowSimple($p_from, $p_to, $pa_ledger=[],$trunc=0,$p_limit=-1,$p_offset=-1)
    {
        if ( empty($pa_ledger) ) {
            $pa_ledger=[$this->id];
        }
        // if $pa_ledger == 0, it means we need to show all ledgers
        if ( $pa_ledger == [0] ) {
            $pa_ledger=Print_Ledger::available_ledger($p_from);
        }
        
        $alh_generic=new Acc_Ledger_History_Generic($this->db, $pa_ledger, $p_from, $p_to, "A");
        $alh_generic->get_rowSimple($trunc,$p_limit,$p_offset);
        $data=$alh_generic->get_data();
        return $data;
    }
    /**
     * @brief get info from supplier to pay today
     */
    function get_supplier_now()
    {
        $array=$this->get_operation_date(Date('d.m.Y'), 'ACH', '=');
        return $array;
    }

    /**
     * @brief get info from supplier not yet paid
     */
    function get_supplier_late()
    {
        $array=$this->get_operation_date(Date('d.m.Y'), 'ACH', '<');
        return $array;
    }

    /**
     * @brief get info from customer to pay today
     */
    function get_customer_now()
    {
        $array=$this->get_operation_date(Date('d.m.Y'), 'VEN', '=');
        return $array;
    }

    /**
     * @brief get info from customer not yet paid
     */
    function get_customer_late()
    {
        $array=$this->get_operation_date(Date('d.m.Y'), 'VEN', '<');
        return $array;
    }

    /**
     * @brief convert operations from FOLLOWUP into a SALE , FEENOTE
     * or PURCHASE operation into a suitable array
     * @param $p_ag_id int PK of ACTION_GESTION
     * @param $copy_description int 0 the description of the followup  is not copied
     * in the note , 1 is copied
     * @return array|void|null
     * @throws Exception
     */
    function convert_from_follow($p_ag_id,$copy_description=0)
    {
        global $g_user;
        if (isNumber($p_ag_id)==0)
            return null;
        if (!$g_user->can_read_action($p_ag_id))
            die(_('Action non accessible'));
        $array=array();

        // retrieve info from action_gestion
        $tiers_id=$this->db->get_value('select f_id_dest from action_gestion where ag_id=$1',
                array($p_ag_id));
        if ($this->db->size()!=0)
            $qcode=$this->db->get_value('select j_qcode from vw_poste_qcode where f_id=$1',
                    array($tiers_id));
        else
            $qcode="";

        $comment=$this->db->get_value('select ag_title from action_gestion where ag_id=$1',
                array($p_ag_id));
        $array['e_client']=$qcode;
        $array['e_comm']=$comment;

        // retrieve info from action_detail
        $a_item=$this->db->get_array('select f_id,ad_text,ad_pu,ad_quant,ad_tva_id,ad_tva_amount,j_qcode 
                    from 
                  action_detail 
                  left join vw_poste_qcode using(f_id)
                  where
                    ag_id=$1', array($p_ag_id));

        $array['nb_item']=($this->nb>count($a_item))?$this->nb:count($a_item);
        for ($i=0; $i<count($a_item); $i++)
        {
            $array['e_march'.$i]=$a_item[$i]['j_qcode'];
            $array['e_march'.$i.'_label']=$a_item[$i]['ad_text'];
            $array['e_march'.$i.'_price']=$a_item[$i]['ad_pu'];
            $array['e_march'.$i.'_tva_id']=$a_item[$i]['ad_tva_id'];
            $array['e_march'.$i.'_tva_amount']=$a_item[$i]['ad_tva_amount'];
            $array['e_quant'.$i]=$a_item[$i]['ad_quant'];
        }
        if ( $copy_description == 1) {
            $acomment=$this->db->get_array("SELECT agc_id, ag_id, to_char(agc_date,'DD.MM.YYYY HH24:MI') as str_agc_date, agc_comment, agc_comment_raw,tech_user
				 FROM action_gestion_comment where ag_id=$1 order by agc_id", array($p_ag_id)
            );
            if (count ($acomment) > 0)
                $array['jrn_note_input']=$acomment[0]['agc_comment'];
        }
        return $array;
    }

    /**
     * Retrieve the label of an accounting
     * @param $p_value tmp_pcmn.pcm_val
     * @return string
     */
    protected function find_label($p_value)
    {
        $lib=$this->db->get_value('select pcm_lib from tmp_pcmn where pcm_val=$1',
                array($p_value));
        return $lib;
    }

    /**
     * Let you select the repository before confirming a sale or a purchase.
     * Returns an empty string if the company doesn't use stock
     * @brief Let you select the repository before confirming a sale or a purchase.
     * @global type $g_parameter check if company is using stock
     * @param type $p_readonly 
     * @param type $p_repo
     * @return string
     */
    public function select_depot($p_readonly, $p_repo)
    {
        global $g_parameter;
        $r=($p_readonly==false)?'<div id="repo_div_id" style="height:185px;height:10rem;">':'<div id="repo_div_id" >';
        // Show the available repository
        if ($g_parameter->MY_STOCK=='Y')
        {
            $sel=HtmlInput::select_stock($this->db, 'repo', 'W');
            $sel->readOnly=$p_readonly;
            if ($p_readonly==true)
                $sel->selected=$p_repo;
            $r.="<p class=\"decale\">"._('Dans le dépôt')." : ";
            $r.=$sel->input();
            $r.='</p>';
        } else
        {
            $r.='<span class="notice">'.'Stock non utilisé'.'</span>';
        }
        $r.='</div>';
        return $r;
    }

    /**
     * Create a button to encode a new operation into the same ledger
     * @return string
     */
    function button_new_operation()
    {
        $url=http_build_query(array('ac'=>$_REQUEST['ac'], 'gDossier'=>$_REQUEST['gDossier'],
            'p_jrn'=>$_REQUEST['p_jrn']));
        $button=HtmlInput::button_anchor(_("Nouvelle opération"),
                        'do.php?'.$url, "", "", "smallbutton");
        return '<p>'.$button.'</p>';
    }

    /**
     * @brief Show a button to create an operation identical to the recorded
     * one. It is a form POST since it is a limit with get
     */
    public function button_copy_operation()
    {
        echo '<FORM METHOD="POST">';
        echo HtmlInput::post_to_hidden(
                array("gDossier", "ac", "p_jrn", "e_client", "nb_item", "desc", "e_comm")
        );
        echo HtmlInput::hidden("correct", "copy");
        // e_march
        $http=new HttpInput();
        $nb=$http->post("nb_item", "number", 0);
        echo HtmlInput::post_to_hidden(['p_currency_rate','p_currency_code']);
        echo HtmlInput::post_to_hidden(['other_tax','other_tax_amount']);
        for ($i=0; $i<$nb; $i++)
        {
            echo HtmlInput::post_to_hidden(
                    array(
                        "e_march".$i,
                        "e_march".$i."_price",
                        "e_march".$i."_quant",
                        "e_march".$i."_label",
                        "e_march".$i."_tva_id",
                        "e_march".$i."_tva_amount",
                        "e_quant".$i,
                        "poste".$i,
                        "ld".$i,
                        "qc_".$i,
                        "amount".$i,
                        "ck".$i
            ));
        }
        echo HtmlInput::submit("copy_operation", _("Opération identique"));

        echo '</FORM>';
    }

    /**
     * Return a button to create new card, depending of the ledger 
     * @param $p_filter string : filter for adding : deb, cred or -1 for filter depending of the ledger
     * @param $p_id_update string
     */
    function add_card($p_filter, $p_id_update)
    {
        $js_script="this.filter='{$p_filter}';this.elementId='{$p_id_update}';this.jrn=\$('p_jrn').value; select_card_type(this);";
        $str_add_button=Icon_Action::icon_add(uniqid(), $js_script);
        return $str_add_button;
    }
    /**
     * Check if a ledger is enabled , 1 for yes and 0 if disabled
     */
    function is_enable()
    {
       return $this->db->get_value("select jrn_enable from jrn_def where jrn_def_id=$1",[$this->id]); 
    }
    /**
     * Check if a ledger is enabled , 1 for yes and 0 if disabled
     */
    function has_quantity()
    {
        return $this->jrn_def_quantity;
    }
    /**
     * @brief set quantity for the ledger to 1 or 0,
     * @note do not save in the DB
     */
    function set_quantity($p_value)
    {
         $this->jrn_def_quantity=$p_value;
    }
    /**
     * Check if the operation is used in the table quant*
     * @param integer $p_grpt_id
     * @param string $p_jrn_type ledger's type ACH, VEN,ODS or FIN
     * @return boolean TRUE if existing info in quant*
     * @Exceptions code 1000  if unknown ledger's type
     */
    function use_quant_table($p_grpt_id,$p_jrn_type)
    {
        if ( $p_jrn_type == 'ACH')
        {
            $sql="select count(*) from jrnx join quant_purchase using (j_id) where j_grpt=$1";
        }elseif ($p_jrn_type=='VEN')
        {
            $sql="select count(*) from jrnx join quant_sold using (j_id) where j_grpt=$1";
        }elseif ($p_jrn_type=='FIN')
        {
            $sql="select count(*) from jrn join quant_fin using (jr_id) where jr_grpt_id=$1";
            
        }elseif ($p_jrn_type=='ODS') return 0;
        else 
        {
            throw new Exception(_('Journal incorrect'),1000);
        }
        
        $count=$this->db->get_value($sql,[$p_grpt_id]);
        
        if ($count > 0) return TRUE; 
        
        return FALSE;
        
    }
    /**
     * Create a select from value for currency and add javascript to update $p_currency_rate and
     * $p_eur_amount 
     * @param string DOMID $p_currency_code
     * @param string DOMID $p_currency_rate
     * @param string DOMID $p_eur_amount
     */
    function CurrencyInput($p_currency_code, $p_currency_rate, $p_eur_amount)
    {
        $type=$this->get_type();
        $currency=new Acc_Currency($this->db);
        $select=$currency->select_currency();
        if ($type =='ODS')
        {
            
            $select->javascript=sprintf('onchange="LedgerCurrencyUpdateMisc(\'%s\',\'%s\',\'%s\',\'%s\',\'%s\');'.
                    '$(\'update_p_currency_rate\').innerHTML=$(\'p_currency_rate\').value;"',
                    Dossier::id(), $select->name, $p_currency_code, $p_currency_rate, $p_eur_amount);
        }
        elseif ($type == 'ACH' || $type == 'VEN')
        {
            
            $select->javascript=sprintf('onchange="LedgerCurrencyUpdate(\'%s\',\'%s\',\'%s\',\'%s\',\'%s\');'.
                    '$(\'update_p_currency_rate\').innerHTML=$(\'p_currency_rate\').value;"',
                    Dossier::id(), $select->name, $p_currency_code, $p_currency_rate, $p_eur_amount);
        }
        else
        {
            throw new Exception(_("Journal type non déterminé"));
        }
        return $select;
    }

    /**
     * @brief returns the code iso of the default currency for this ledger
     */
    function get_currency()
    {
        $cr=new Acc_Currency($this->db,$this->currency_id);
        if ( $cr->get_id() < 0 ) {
            throw new Exception("ACL.3214"._("Taux invalide"));
        }
        return $cr;
    }
    /**
     * If the amount is positive and the ledger expects a negative amount, il will return the saved warning
     * 
     * @param int $p_amount amount to check
     * @throws Exception 1 if invalid ledger
     */
    function display_negative_warning($p_amount)
    {
        if ($this->id == 0) {
            throw new Exception(_("Journal invalide"), 1);
        }
        $ledger=new Jrn_def_SQL($this->db,$this->id);
        if ( $p_amount > 0 && $ledger->getp("jrn_def_negative_amount")==1){
            return _($ledger->getp("jrn_def_negative_warning"));
        }
        return "";
    }
    function input_extra_info()
    {
        require NOALYSS_TEMPLATE."/acc_ledger-input_extra_info.php";
    }
    /**
     * @brief attach action-followups to an operation, 
     * @param string $s_related_action action.ag_id separated by comma
     * @see Acc_Operation
     * @return boolean true success ,false nothing inserted
     */
    function save_followup($s_related_action)
    {
        if ($this->jr_id == 0 || empty ($s_related_action))  { return false;  }
        
        $acc_operation=new Acc_Operation($this->cn);
        $acc_operation->jr_id=$this->jr_id;
        $acc_operation->insert_related_action($s_related_action);
        return true;
    }

    /**
     * @brief form : display additional tax available for this ledger and value, set 2 values : checkbox if tax applies
     * and value
     *
     * @see template/form_ledger_detail.php
     * @returns string
     */
    function input_additional_tax()
    {
        $http=new HttpInput();
        if ($this->has_other_tax() == false ) { return "";}
        $amount=new INum("other_tax_amount",0);
        $amount->value=$http->request("other_tax_amount","number",0);

        $amount->javascript='onchange="format_number(this,2);refresh_ledger();"';
        $msg=_("Montant");
        $row=$this->cn->get_row("select ac_id,ac_label,ac_rate from acc_other_tax where $1 = any (ajrn_def_id)",
            [$this->id]);
        $checkbox=new ICheckBox("other_tax",$row['ac_id']);
        $checkbox->set_check($http->request("other_tax","number",-1));
        $checkbox->javascript=<<<EOF
onchange='if (! this.checked) {  $("other_tax_amount").value=0;}compute_all_ledger();'
EOF;

        $label=h($row['ac_label']);
        $title=_("Autre taxe");
        $out=<<<EOF

    <h2 class="h3">{$title}</h2>
    {$checkbox->input()} {$label} {$row['ac_rate']}%: {$msg} {$amount->input()}
EOF;

        $out.="<h4>"._("Total opération").
            "<span class=\"mx-4\" id='total_operation_other_tax'>".
            "</span>".
            "</h4>";
        return $out;
    }

    /**
     * @brief in confirm screen , display the compute value for additional tax
     * @parameter $p_additional_tax acc_other_tax.ac_id
     */
    function display_additional_tax($p_additional_tax,$p_amount)
    {
        $row=$this->cn->get_row("select ac_id,ac_label,ac_rate from acc_other_tax where ac_id=$1",
            [$p_additional_tax]);
        $label=h($row['ac_label']);
        $title=_("Autre taxe");
        $p_amount=h($p_amount);
        $out=<<<EOF
<div id="additional_tax">
    <h2 class="h3">{$title}</h2>
   {$label} {$row['ac_rate']}%: $p_amount
</div>

EOF;
        return $out;
    }


    /**
     * @brief returns true if the  ledger has an additional tax
     */
    function has_other_tax()
    {
        $cnt=$this->db->get_value('select count(*) 
            from acc_other_tax 
            where array_position(ajrn_def_id,$1) is not null',[$this->id]);
        if ($cnt == 0 ) return false;
        return true;

    }

    /**
     * @brief compare given receipt number and suggested one, if different , it means that the user enters a receipt number
     * if e_pj or e_pj_suggest is not set or empty , or if both are equals then will return true,
     * it returns only if they exist and are different
     * @param $p_array same structure as input
     * @return void
     */
    protected function verify_autonumber($p_array)
    {
        if (empty($p_array['e_pj'])) return true;
        if (empty($p_array['e_pj_suggest'])) return true;
        if ( noalyss_trim($p_array['e_pj'])===noalyss_trim($p_array['e_pj_suggest'])) { return true; }
        return false;
    }
    /**
     * @brief warn if the suggested receipt and receipt are different , it means that the user tried to
     * number himself
     * @param $p_array same structure as input
     * @see Acc_Ledger::input()
     * @see Acc_Ledger::confirm()
     * @return void
     */
    protected function  warn_manual_receipt($p_array)
    {
        if ( $this->verify_autonumber($p_array) == false) {
            return span (_("Attention ! Numéro de Pièce non automatique mais forcée"),'class="warning"');
        }
    }
}

?>
