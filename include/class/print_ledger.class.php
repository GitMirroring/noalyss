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

/**
 *  Parent class for the print_ledger class
 *
 * @author danydb
 */

/**
 * @brief Strategie class for the print_ledger class
 * 
*/
class Print_Ledger extends PDF
{
    protected  $filter_operation; // See Acc_Ledger_History::filter_operation
    private $ledger ; //!< concerned Ledger 
    private $from ; //! integer parm_periode.p_id , start periode;
    private $to ; //! integer parm_periode.p_id , end periode;
    public function __construct(\Database $p_cn, 
                                $orientation, 
                                $unit, 
                                $format,
                                Acc_Ledger $p_ledger,
                                $p_from,
                                $p_to,
                                $p_filter_operation)
    {
        parent::__construct($p_cn, $orientation, $unit, $format);
        $this->ledger=$p_ledger;
        $this->from=$p_from;
        $this->to=$p_to;
        $this->set_filter_operation($p_filter_operation);
    }
    public function get_ledger()
    {
        return $this->ledger;
    }

    public function get_from()
    {
        return $this->from;
    }

    public function get_to()
    {
        return $this->to;
    }

    public function set_ledger($ledger)
    {
        $this->ledger=$ledger;
        return $this;
    }

    public function set_from($from)
    {
        $this->from=$from;
        return $this;
    }

    public function set_to($to)
    {
        $this->to=$to;
        return $this;
    }
    function get_filter_operation()
    {
        return $this->filter_operation;
    }
        /**
     * Filter the operations , 
     * @param string $filter_operation : all , paid, unpaid
     * @return $this
     * @throws Exception 5 , if filter invalid
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
     * Create an object Print_Ledger* depending on $p_type_export ( 0 => accounting
     * 1-> one row per operation 2-> detail of item)
     * @param Database $cn
     * @param char $p_type_export E(xtended) L(isting) A(ccounting) D(etail)
     * @param Acc_Ledger $ledger
     */
    static function  factory(Database $cn, $p_type_export, Acc_Ledger $p_ledger,$p_from,$p_to,$p_filter_operation) 
    {
        /**
         * @Bug
         * Strange PHP Bug when autoloader is not used , the require_once doesn't seems to
         * work properly and does not include the files , except if you put them here
         *
         * if you put them on the top of this file,  export_ledger_pdf.php will include the files
         * but not export_ledger_csv.php
         */

        /**
         * For PDF output
         */
        switch ($p_type_export) {
            case 'D':
                $own=new Noalyss_Parameter_Folder($cn);
                $jrn_type=$p_ledger->get_type();
                //---------------------------------------------
                // Detailled Printing (accounting )
                //---------------------------------------------
                if ($jrn_type=='ACH'||$jrn_type=='VEN')
                {
                    if (
                            ($jrn_type=='ACH'&&$cn->get_value('select count(qp_id) from quant_purchase')
                            ==0)||
                            ($jrn_type=='VEN'&&$cn->get_value('select count(qs_id) from quant_sold')
                            ==0)
                    )
                    {
                        $pdf=new Print_Ledger_Simple_without_vat($cn,
                                $p_ledger,$p_from,$p_to,$p_filter_operation);
                        $pdf->set_error(_('Ce journal ne peut être imprimé en mode simple'));
                        return $pdf;
                    }
                    if ($own->MY_TVA_USE=='Y')
                    {
                        $pdf=new Print_Ledger_Simple($cn, $p_ledger,$p_from,$p_to,$p_filter_operation);
                        return $pdf;
                    }
                    if ($own->MY_TVA_USE=='N')
                    {
                        $pdf=new Print_Ledger_Simple_without_vat($cn,
                                $p_ledger,$p_from,$p_to,$p_filter_operation);
                        return $pdf;
                    }
                }
                elseif ($jrn_type=='FIN')
                {
                    $pdf=new Print_Ledger_Financial($cn, $p_ledger,$p_from,$p_to);
                    return $pdf;
                } else 
                {
                    return new Print_Ledger_Detail($cn, $p_ledger,$p_from,$p_to);
                }
                break;

            case 'L':
                //----------------------------------------------------------------------
                // Simple Printing Purchase Ledger
                //---------------------------------------------------------------------
                $own=new Noalyss_Parameter_Folder($cn);
                $jrn_type=$p_ledger->get_type();


                if ($jrn_type=='ACH'||$jrn_type=='VEN')
                {
                    if (
                            ($jrn_type=='ACH'&&$cn->get_value('select count(qp_id) from quant_purchase')
                            ==0)||
                            ($jrn_type=='VEN'&&$cn->get_value('select count(qs_id) from quant_sold')
                            ==0)
                    )
                    {
                        $pdf=new Print_Ledger_Simple_without_vat($cn,
                                $p_ledger,$p_from,$p_to,$p_filter_operation);
                        $pdf->set_error(_('Ce journal ne peut être imprimé en mode simple'));
                        return $pdf;
                    }
                    if ($own->MY_TVA_USE=='Y')
                    {
                        $pdf=new Print_Ledger_Simple($cn, $p_ledger,$p_from,$p_to,$p_filter_operation);
                        return $pdf;
                    }
                    if ($own->MY_TVA_USE=='N')
                    {
                        $pdf=new Print_Ledger_Simple_without_vat($cn,
                                $p_ledger,$p_from,$p_to,$p_filter_operation);
                        return $pdf;
                    }
                }

                if ($jrn_type=='FIN')
                {  
                    $pdf=new Print_Ledger_Financial($cn, $p_ledger,$p_from,$p_to);
                    return $pdf;
                }
                $pdf=new Print_Ledger_Misc($cn, $p_ledger,$p_from,$p_to);
                return $pdf;
                break;
            case 'E':
                /**********************************************************
                 * Print Detail Operation + Item
                 * ********************************************************* */
                $own=new Noalyss_Parameter_Folder($cn);
                $jrn_type=$p_ledger->get_type();
                if ($jrn_type=='FIN')
                {
                    $pdf=new Print_Ledger_Detail($cn, $p_ledger,$p_from,$p_to);
                    return $pdf;
                }
                if ($jrn_type=='ODS'||$p_ledger->id==0)
                {
                    $pdf=new Print_Ledger_Detail($cn, $p_ledger,$p_from,$p_to);
                    return $pdf;
                }
                if (
                        ($jrn_type=='ACH'&&$cn->get_value('select count(qp_id) from quant_purchase')
                        ==0)||
                        ($jrn_type=='VEN'&&$cn->get_value('select count(qs_id) from quant_sold')
                        ==0)
                )
                {
                    $pdf=new Print_Ledger_Simple_without_vat($cn, $p_ledger,$p_from,$p_to,$p_filter_operation);
                    $pdf->set_error('Ce journal ne peut être imprimé en mode simple');
                    return $pdf;
                }
                $pdf=new Print_Ledger_Detail_Item($cn, $p_ledger,$p_from,$p_to,$p_filter_operation);
                return $pdf;
            case 'A':
                /***********************************************************
                 * Accounting
                 */
                $pdf=new Print_Ledger_Detail($cn, $p_ledger,$p_from,$p_to);
                return $pdf;
                break;
        } // end switch
    }

// end function
    /**
     * @brief find all the active ledger  for the exerice of the periode
     * and readable by the current user
     * @global type $g_user
     * @param int  $get_from_periode
     * @return array of ledger id
     */
    static function available_ledger($get_from_periode)
    {
        global $g_user;
        $cn=Dossier::connect();
        // Find periode 
        $periode=new Periode($cn, $get_from_periode);
        $exercice=$periode->get_exercice($get_from_periode);

        if ($g_user->Admin()==0&&$g_user->is_local_admin()==0&&$g_user->get_status_security_ledger()
                ==1)
        {
            $sql="select jrn_def_id 
                 from jrn_def join jrn_type on jrn_def_type=jrn_type_id
                 join user_sec_jrn on uj_jrn_id=jrn_def_id
                 where
                 uj_login=$1
                 and uj_priv in ('R','W')
                 and ( jrn_enable=1 
                        or 
                        exists (select 1 from jrn
                        where  
                            jr_def_id=jrn_def_id
                        and jr_tech_per in (select p_id from parm_periode where p_exercice=$2)))
                         order by jrn_def_name
                 ";
            $a_jrn=$cn->get_array($sql, array($g_user->login, $exercice));
        }
        else
        {
            $a_jrn=$cn->get_array("select jrn_def_id
                                 from jrn_def join jrn_type on jrn_def_type=jrn_type_id
                                 where
                                 jrn_enable=1 
                                 or exists(select 1 from jrn 
                                    where  
                                            jr_def_id=jrn_def_id 
                                        and jr_tech_per in (select p_id from parm_periode where p_exercice=$1))
                                        order by jrn_def_name
                                                         ", [$exercice]);
        }
        $a=[];
        $nb_jrn=count($a_jrn);
        for ($i=0; $i<$nb_jrn; $i++)
        {
            $a[]=$a_jrn[$i]['jrn_def_id'];
        }
        return $a;
    }
    /**
     * Build a SQL clause to filter operation depending if they are paid, unpaid or no filter
     * @return string SQL Clause
     */
    protected function build_filter_operation()
    {
        switch ($this->get_filter_operation())
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
                throw new Exception(_("Filtre invalide", 5));
        }
        return $sql_filter;
    }
}

?>
