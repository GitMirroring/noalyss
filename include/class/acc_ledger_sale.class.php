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

/*!
 * \file
 * \brief class for the sold, herits from acc_ledger
 */
require_once NOALYSS_INCLUDE.'/lib/user_common.php';
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';

/*!
 * \brief Handle the ledger of sold,
 *
 * @exception throw an exception is something is wrong
 */

class Acc_Ledger_Sale extends Acc_Ledger {

    function __construct($p_cn, $p_init) {
        parent::__construct($p_cn, $p_init);
        $this->ledger_type = 'VEN';
    }

    /*!\brief verify that the data are correct before inserting or confirming
     * \param an array (usually $_POST)
     * \return String
     * \throw Exception if an error occurs
     */
    public function verify_operation($p_array) {
        global $g_parameter, $g_user;
        
        if (is_array($p_array ) == false || empty($p_array))
                    throw new Exception ("Array empty");
        
        extract($p_array, EXTR_SKIP);
        
        /*
         * Check needed value
         */
        check_parameter($p_array,'p_jrn,e_date,e_client');

        /* check for a double reload */
        if (isset($mt) && $this->db->count_sql('select jr_mt from jrn where jr_mt=$1', array($mt)) != 0)
            throw new Exception(_('Double Encodage'), 5);

        /* check if we can write into this ledger */
        if ($g_user->check_jrn($p_jrn) != 'W')
            throw new Exception(_('Accès interdit'), 20);

        /* check if there is a customer */
        if (noalyss_strlentrim($e_client) == 0)
            throw new Exception(_('Vous n\'avez pas donné de client'), 11);

        /*  check if the date is valid */
        if (isDate($e_date) == null) {
            throw new Exception(_('Date invalide'), 2);
        }

        $oPeriode = new Periode($this->db);
        if ($this->check_periode() == true && isset($p_array['period'])) {
            $tperiode = $period;
            /* check that the datum is in the choosen periode */
            $oPeriode->p_id = $period;
            list ($min, $max) = $oPeriode->get_date_limit();

            if (cmpDate($e_date, $min) < 0 ||
                    cmpDate($e_date, $max) > 0)
                throw new Exception(_('Date et periode ne correspondent pas'), 6);
        }
        else {
            $per = new Periode($this->db);
            $tperiode = $per->find_periode($e_date);
        }

        /* check if the periode is closed */
        if ($this->is_closed($tperiode) == 1) {
            throw new Exception(_('Periode fermee'), 6);
        }
        /* check if we are using the strict mode */
        if ($this->check_strict() == true) {
            /* if we use the strict mode, we get the date of the last
              operation */
            $last_date = $this->get_last_date();
            if ($last_date != null && cmpDate($e_date, $last_date) < 0)
                throw new Exception(_('Vous utilisez le mode strict la dernière operation est date du ')
                . $last_date . _(' vous ne pouvez pas encoder à une date antérieure'), 13);
        }


        $fiche = new Fiche($this->db);
        $fiche->get_by_qcode($e_client);

        if ($fiche->get_f_enable() == '0')
            throw new Exception(sprintf(_("La fiche %s n'est plus utilisée"),$e_client), 50);

        if ($fiche->empty_attribute(ATTR_DEF_ACCOUNT) == true)
            throw new Exception(_('La fiche ') . $e_client . _('n\'a pas de poste comptable'), 8);



        /* get the account and explode if necessary */
        $sposte = $fiche->strAttribut(ATTR_DEF_ACCOUNT);
        // if 2 accounts, take only the debit one for customer
        if (strpos($sposte, ',') != 0) {
            $array = explode(',', $sposte);
            $poste_val = $array[0];
        } else {
            $poste_val = $sposte;
        }
        /* The account exists */

        $poste = new Acc_Account_Ledger($this->db, $poste_val);

        if ($poste->load() == false) {
            throw new Exception(_('Pour la fiche ') . $e_client . _(' le poste comptable [') . $poste->id . _('] n\'existe pas'), 9);
        }

        /* Check if the card belong to the ledger */
        $fiche = new Fiche($this->db);
        $fiche->get_by_qcode($e_client, 'deb');
        if ($fiche->belong_ledger($p_jrn) != 1)
            throw new Exception(_('La fiche ') . $e_client . _('n\'est pas accessible à ce journal'), 10);

        $nb = 0;

        //----------------------------------------
        // foreach item
        //----------------------------------------
        for ($i = 0; $i < $nb_item; $i++) {
            if (! isset (${'e_march' . $i}) || noalyss_strlentrim(${'e_march' . $i}) == 0)
                continue;
            /* check if all card has a ATTR_DEF_ACCOUNT */
            $fiche = new Fiche($this->db);
            $fiche->get_by_qcode(${'e_march' . $i});
            if ($fiche->get_f_enable() == '0')
                throw new Exception(sprintf(_("La fiche %s n'est plus utilisée"), ${'e_march' . $i}), 50);


            /* check if amount are numeric and */
            if (isNumber(${'e_march' . $i . '_price'}) == 0)
                throw new Exception(_('La fiche ') . ${'e_march' . $i} . _('a un montant invalide [') . ${'e_march' . $i} . ']', 6);
            if (isNumber(${'e_quant' . $i}) == 0) 
                throw new Exception(_('La fiche ') . ${'e_march' . $i} . _('a une quantité invalide [') . ${'e_quant' . $i} . ']', 7);

            if ($fiche->empty_attribute(ATTR_DEF_ACCOUNT) == true)
                throw new Exception(_('La fiche ') . ${'e_march' . $i} . _('n\'a pas de poste comptable'), 8);

            // Check if the given tva id is valid
            if ($g_parameter->MY_TVA_USE == 'Y') {
                $tva_rate =  Acc_Tva::build($this->db,${'e_march' . $i . '_tva_id'});
                $tva_rate->load();
                if ($tva_rate->tva_id === -1 )
                    throw new Exception(_('La fiche ') . ${'e_march' . $i} . _('a un code tva invalide') . ' [' . ${'e_march' . $i . '_tva_id'} . ']', 13);

                $tva_rate->load();
                /*
                 * check if the accounting for VAT are valid
                 */
                $a_poste = explode(',', $tva_rate->tva_poste);

                if (
                       
                        $this->db->get_value('select count(*) from tmp_pcmn where pcm_val=$1', array($a_poste[1])) == 0)
                          throw new Exception(_(" La TVA " . $tva_rate->tva_label . " utilise des postes comptables inexistants"));
            }
            // if 2 accounts, take only the credit one
            /* The account exists */
            $sposte = $fiche->strAttribut(ATTR_DEF_ACCOUNT);

            if (strpos($sposte, ',') != 0) {
                $array = explode(',', $sposte);
                $poste_val = $array[1];
            } else {
                $poste_val = $sposte;
            }
            $poste = new Acc_Account_Ledger($this->db, $poste_val);
            if ($poste->load() == false) {
                throw new Exception(_('Pour la fiche ') . ${'e_march' . $i} . _(' le poste comptable [') . $poste->id . _('n\'existe pas'), 9);
            }
            /* Check if the card belong to the ledger */
            $fiche = new Fiche($this->db);
            $fiche->get_by_qcode(${'e_march' . $i});
            if ($fiche->belong_ledger($p_jrn, 'cred') != 1)
                throw new Exception(_('La fiche ') . ${'e_march' . $i} . _('n\'est pas accessible à ce journal'), 10);
           
            if ( ${"e_quant".$i} != 0 && trim(${"e_quant".$i}) !="" ) {$nb++;}

        }
        if ($nb == 0)
            throw new Exception(_('Il n\'y a aucune marchandise'), 12);
        //------------------------------------------------------
        // The "Paid By"  check
        //------------------------------------------------------

        if ($e_mp != 0) {
            $this->check_payment($e_mp, ${"e_mp_qcode_" . $e_mp});
            // check for the currency , if we use a financial ledger and a card which is a bank account (with his own
            // ledger , then the currency of the operation must be the same
            $this->check_currency(${"e_mp_qcode_" . $e_mp},$p_currency_code);
        }
        
        
        
        
        // 
        // Check payment date
        if ( isset ($mp_date) && trim ($mp_date) != "" && isDate($mp_date) == null)  {
            throw new Exception(_('Date de paiement invalide'),13);
            
        }
        
        // check that MP is in a not closed and exists
        if ( isset ($mp_date) && trim ($mp_date) != "" && isDate($mp_date) == $mp_date )  {
              $periode=new Periode($this->cn); 
              $periode->find_periode($mp_date);
              $periode->set_ledger($this->id);
              if ( $periode->is_closed() ) {
                  throw new Exception(_("Période fermée")." $mp_date ");
              }
            
        }
        // check limit date
        if ( isset ($e_ech) && trim ($e_ech)!="" && isDate($e_ech) == null )
        {
            throw new Exception(_('Date échéance invalide'),14);
            
        }
        // Check currency_rate if valid
        if ( isNumber($p_currency_rate) == 0 || $p_currency_rate <=0 ) {
            throw new Exception(_('Taux devise invalide'),15);
        }
        $this->check_currency_setting($p_currency_code);
    }

    /*!
     * \brief insert into the database, it calls first the verify function,
     * store the value of the inserted operation in $this->jr_id and this->jr_internal
     *
     *  It generates the document if gen_invoice is set and save the middle of payment if any ($e_mp)
     *
     *  It also create a second operation if there is a payment
     *
     * \param $p_array is usually $_POST or a predefined operation
     * \return string : internal number 
     * \note throw an Exception
     */

    public function insert($p_array = null) {
        global $g_parameter,$g_user;
        // load ledger definition
        $this->load();
        extract($p_array, EXTR_SKIP);
        $this->verify($p_array);

        $group = $this->db->get_next_seq("s_oa_group"); /* for analytic */
        $seq = $this->db->get_next_seq('s_grpt');
        $this->id = $p_jrn;
        $internal = $this->compute_internal_code($seq);
        $this->internal = $internal;

        $oPeriode = new Periode($this->db);
        $check_periode = $this->check_periode();

        if ($check_periode == true && isset($p_array['period']))
            $tperiode = $period;
        else
            $tperiode = $oPeriode->find_periode($e_date);

        $cust = new Fiche($this->db);
        $cust->get_by_qcode($e_client);
        $sposte = $cust->strAttribut(ATTR_DEF_ACCOUNT);

        // if 2 accounts, take only the debit one for the customer
        //
        if (strpos($sposte, ',') != 0) {
            $array = explode(',', $sposte);
            $poste = $array[0];
        } else {
            $poste = $sposte;
        }

        bcscale(4);
        try {
            /// @var  $tot_amount : total amount of the sales (credit)
            $tot_amount = 0;
            /// @var $tot_tva : total amount of the VAT
            $tot_tva = 0;
            // tot debit if item's amount < 0
            $tot_debit = 0;
            /// @var $tot_amount_cur : total amount in currency
            $tot_amount_cur=0;

            $this->db->start();
            /// @var $tva array that will contain all the VAT Amount
            $tva = array();
            /// @var $tva_reverse array that contain all the VAT autoreverse AND negative
            $tva_reverse = array();

             // find the currency from v_currency_last_value
            /// @var $currency_rate_ref Acc_Currency , currency object for this operation
            $currency_rate_ref=new Acc_Currency($this->db, $p_currency_code);

            /* Save all the items without vat */
            for ($i = 0; $i < $nb_item; $i++) {
                /// @var $n_both float auto-reverse amount
                $n_both = 0;
                if ( empty(${'e_march'.$i}) || empty(${'e_quant'.$i}) ) continue;

                /* First we save all the items without vat */
                $fiche = new Fiche($this->db);
                $fiche->get_by_qcode(${"e_march" . $i});
                $amount_currency = bcmul(${'e_march' . $i . '_price'}, ${'e_quant' . $i});
                
                // convert amount to currency
                $amount=bcdiv($amount_currency,$p_currency_rate);
                
                $tot_amount = bcadd($tot_amount, $amount);
                $tot_amount = round($tot_amount, 2);
                $acc_operation = new Acc_Operation($this->db);
                $acc_operation->date = $e_date;
                $sposte = $fiche->strAttribut(ATTR_DEF_ACCOUNT);

                // if 2 accounts, take only the credit one
                if (strpos($sposte, ',') != 0) {
                    $array = explode(',', $sposte);
                    $poste_val = $array[1];
                } else {
                    $poste_val = $sposte;
                }

                $acc_operation->poste = $poste_val;
                $acc_operation->amount = $amount;
                $acc_operation->grpt = $seq;
                $acc_operation->jrn = $p_jrn;
                $acc_operation->type = 'c';
                $acc_operation->periode = $tperiode;
                if ($g_parameter->MY_UPDLAB=='Y')
                {
                    $acc_operation->desc=strip_tags(${"e_march".$i."_label"});
                }
                else
                {
                    $acc_operation->desc=null;
                }

                $acc_operation->qcode = ${"e_march" . $i};
                if ($amount<0)
                {
                    $tot_debit=round(bcadd($tot_debit, abs($amount)),2);
                }

                $j_id = $acc_operation->insert_jrnx();

                if ($g_parameter->MY_TVA_USE == 'Y') {
                    /* Compute sum vat */
                    $oTva =  Acc_Tva::build($this->db, trim(${'e_march' . $i . '_tva_id'}));
                    $idx_tva =$oTva->get_parameter("id");
                    /// @var  $auto_reverse = if the oTVA autoreverse, fetch it once for this item,
                    $auto_reverse=$oTva->get_parameter("both_side");

                    $tva_item_currency = ${'e_march' . $i . '_tva_amount'};

                    /* if empty then we need to compute it */
                    if (trim($tva_item_currency) == '' || ${'e_march'.$i.'_tva_amount'} == 0) {
                        $tva_item_currency = bcmul($amount, $oTva->get_parameter('rate'));
		            	$tva_item=round($tva_item_currency,2);
                    }
                    $tva_item=bcdiv($tva_item_currency,$p_currency_rate);
                    $tva_item=round($tva_item,2);

                    $tva[$idx_tva]=(isset($tva[$idx_tva]))?$tva[$idx_tva]:0;

                    if ( $auto_reverse == 0)
                    {
                        $tva[$idx_tva]=bcadd($tva_item,$tva[$idx_tva]);
                        $tva[$idx_tva]=round($tva[$idx_tva],2);
                        $tot_tva = bcadd($tva_item, $tot_tva);
                        $tot_tva = round($tot_tva, 2);
                    }
                    else
                    {
                        $n_both = $tva_item;
                         $tva_item_currency = 0;
                        if ($n_both<0)
                        {
                            $tot_debit=round(bcadd($tot_debit, abs($n_both)),2);
                            $tva_reverse[$idx_tva]=(isset($tva_reverse[$idx_tva]))?$tva_reverse[$idx_tva]:0;
                            $tva_reverse[$idx_tva]=bcadd($tva_item,$tva_reverse[$idx_tva]);
                            $tva_reverse[$idx_tva]=round($tva_reverse[$idx_tva],2);

                        } else {
                            $tva[$idx_tva]=bcadd($tva_item,$tva[$idx_tva]);
                            $tva[$idx_tva]=round($tva[$idx_tva],2);

                        }
                    }
                }

                /* Save the stock */
                /* if the quantity is < 0 then the stock increase (return of
                 *  material)
                 */
                $nNeg = (${"e_quant" . $i} < 0) ? -1 : 1;

                // always save quantity but in withStock we can find
                // what card need a stock management
                if ($g_parameter->MY_STOCK = 'Y' && isset($repo))
                {
                    $dir=(${'e_quant'.$i} < 0 ) ? 'd':'c';
                    Stock_Goods::insert_goods($this->db, array('j_id' => $j_id, 'goods' => ${'e_march' . $i}, 'quant' => $nNeg * ${'e_quant' . $i}, 'dir' => $dir, 'repo' => $repo));
                }


                if ($g_parameter->MY_ANALYTIC != "nu" && $g_parameter->match_analytic($poste_val)) {
                    // for each item, insert into operation_analytique */
                    $op = new Anc_Operation($this->db);
                    $op->set_currency_rate($p_currency_rate);
                    $op->oa_group = $group;
                    $op->j_id = $j_id;
                    $op->oa_date = $e_date;
                    $op->oa_debit = 'f';
                    $op->oa_description = sql_string($e_comm);
                    $op->save_form_plan($_POST, $i, $j_id);
                }
                if (empty( ${'e_march' . $i . '_price'}  ) ) ${'e_march' . $i . '_price'}  = 0;
                if (empty( ${'e_march' . $i }  ) ) ${'e_march' . $i }  = 0;
                if (empty( ${'e_quant' . $i }  ) ) ${'e_quant' . $i }  = 0;
                
                $price_euro=bcdiv(${'e_march'.$i.'_price'}, $p_currency_rate);
                
                if ($g_parameter->MY_TVA_USE == 'Y') {
                    /* save into quant_sold */
                    $r = $this->db->exec_sql("select insert_quant_sold ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10)", array(null, /* 1 */
                        $j_id, /* 2 */
                        ${'e_march' . $i}, /* 3 */
                        ${'e_quant' . $i}, /* 4 */
                        round($amount, 2), /* 5 */
                        $tva_item, /* 6 */
                        $oTva->get_parameter("id"), /* 7 */
                        $e_client, /* 8 */
                        $n_both, /* 9 */
                        $price_euro/* Price /unit */ 
                        ));
                } else {
                    $tva_item_currency=0;
                    
                    $r = $this->db->exec_sql("select insert_quant_sold ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10) ", array(null, /* 1 */
                        $j_id, /* 2 */
                        ${'e_march' . $i}, /* 3 */
                        ${'e_quant' . $i}, /* 4 */
                        $amount, // 5
                        0,
                        null,
                        $e_client,
                        0, /* 9 */
                        $price_euro         /* Price /unit */ 
                        ));
                }  // if ( $g_parameter->MY_TVA_USE=='Y') {
                 /*
                 * Insert also in operation_currency
                 */
                $operation_currency=new Operation_currency_SQL($this->db);
                $operation_currency->oc_amount=$amount_currency;
                $operation_currency->oc_vat_amount=$tva_item_currency;
                $operation_currency->oc_price_unit=${'e_march'.$i.'_price'};
                $operation_currency->j_id=$j_id;
                $operation_currency->insert();
                $tot_amount_cur=round(bcadd($tot_amount_cur,$amount_currency,4),4);
                $tot_amount_cur=round(bcadd($tot_amount_cur,$tva_item_currency,4),4);
            }// end loop : save all items

            /*** save other tax ****/
            if ( $this->has_other_tax() && isset($p_array['other_tax'])) {
                $row=$this->db->get_row("select ac_id,ac_label,ac_accounting 
                                                from acc_other_tax 
                                                where ac_id=$1 ",
                    [$p_array['other_tax']]);
                if ( ! empty ($row )) {
                    $other_tax_amount=bcmul($p_array['other_tax_amount'],$p_currency_rate);
                    $acc_operation=new Acc_Operation($this->db);
                    $acc_operation->date=$e_date;
                    $acc_operation->poste=$row['ac_accounting'];
                    $acc_operation->amount=$other_tax_amount;
                    $acc_operation->grpt=$seq;
                    $acc_operation->jrn=$p_jrn;
                    $acc_operation->type='c';
                    $acc_operation->periode=$tperiode;
                    $acc_operation->desc=$row['ac_label'];
                    $jrn_tax_sql=new Jrn_Tax_SQL($this->db);
                    $jrn_tax_sql->j_id=$acc_operation->insert_jrnx();
                    $jrn_tax_sql->ac_id=$row['ac_id'];
                    $jrn_tax_sql->pcm_val=$row['ac_accounting'];
                    $jrn_tax_sql->insert();
                    $tot_amount=bcadd($tot_amount,$other_tax_amount);
                    if ( $p_array['other_tax_amount'] < 0 ) {
                        $tot_debit=bcadd($tot_debit,abs($other_tax_amount));
                    }
                    $operation_currency=new Operation_currency_SQL($this->db);
                    $operation_currency->oc_amount=$p_array['other_tax_amount'];
                    $operation_currency->oc_vat_amount=0;
                    $operation_currency->oc_price_unit=0;
                    $operation_currency->j_id=$jrn_tax_sql->j_id;
                    $operation_currency->insert();
                }

            }


            /*  save total customer */
            $cust_amount = bcadd($tot_amount, $tot_tva);
            $cust_amount = round($cust_amount,2);
            if ( DEBUGNOALYSS > 1 ) { 
                echo __LINE__." cust_amount $cust_amount<br>"; 
                echo __LINE__." tot_amount $tot_amount<br>"; 
                echo __LINE__." tot_tva $tot_tva<br>"; 
            
            }

            $acc_operation = new Acc_Operation($this->db);
            $acc_operation->date = $e_date;
            $acc_operation->poste = $poste;
            $acc_operation->amount = $cust_amount;
            $acc_operation->grpt = $seq;
            $acc_operation->jrn = $p_jrn;
            $acc_operation->type = 'd';
            $acc_operation->periode = $tperiode;
            $acc_operation->qcode = ${"e_client"};
            if ($cust_amount>0)
            {
                $tot_debit=bcadd($tot_debit, $cust_amount);
                $tot_debit=round($tot_debit, 2);
            }
            $let_tiers = $acc_operation->insert_jrnx();
            
            // --- insert also the currency amount for the customer 
            $operation_currency=new Operation_currency_SQL($this->db);
            $operation_currency->oc_amount=$tot_amount_cur;
            $operation_currency->oc_vat_amount=0;
            $operation_currency->oc_price_unit=0;
            $operation_currency->j_id=$let_tiers ;
            $operation_currency->insert();
                

            /**************************************************************************************************
             * save all vat
             * $i contains the tva_id and value contains the vat amount
             * if if ($g_parameter->MY_TVA_USE == 'Y' )
             ************************************************************************************************** */
            if ($g_parameter->MY_TVA_USE == 'Y') {

                foreach ($tva as $i => $value) {
                    $oTva =  Acc_Tva::build($this->db,$i);
                    $poste_vat = $oTva->get_side('c');

                    $cust_amount = bcadd($tot_amount, $tot_tva);
                    $acc_operation = new Acc_Operation($this->db);
                    $acc_operation->date = $e_date;
                    $acc_operation->poste = $poste_vat;
                    $acc_operation->amount = $value;
                    $acc_operation->grpt = $seq;
                    $acc_operation->jrn = $p_jrn;
                    $acc_operation->type = 'c';
                    $acc_operation->periode = $tperiode;
                    if ($value<0)
                    {
                        $tot_debit=bcadd($tot_debit, abs($value));
                        $tot_debit=round($tot_debit, 2);
                    }
                    if ( $oTva->get_parameter("both_side") == 1 && $value ==0 ) continue;
                    $acc_operation->insert_jrnx();

                    // if TVA is on both side, we deduce it immediately
                    if ($oTva->get_parameter("both_side") == 1   ) {
                        // $x temp variable is the tva_reverse_account and will be used to check $poste_vat
                        $x=$oTva->get_parameter("tva_reverse_account");

                        $poste_vat =(trim($x??"")=="")? $oTva->get_side('d'):$x;
                        if ($poste_vat == '#') $poste_vat=$oTva->get_side('c');
                        $cust_amount = bcadd($tot_amount, $tot_tva);
                        $acc_operation = new Acc_Operation($this->db);
                        $acc_operation->date = $e_date;
                        $acc_operation->poste = $poste_vat;
                        $acc_operation->amount = $value;
                        $acc_operation->grpt = $seq;
                        $acc_operation->jrn = $p_jrn;
                        $acc_operation->type = 'd';
                        $acc_operation->periode = $tperiode;
                        $acc_operation->insert_jrnx();
                        $tot_debit = bcadd($tot_debit, $value);
                        $tot_debit = round($tot_debit, 2);
                        $n_both = $value;
                    }

                }
                foreach ($tva_reverse as  $i => $value) {
                    $oTva =  Acc_Tva::build($this->db,$i);
                    $poste_vat = $oTva->get_side('c');
                    if ( $poste_vat == '#')
                    {
                        $poste_vat=$oTva->get_side('d');
                    }

                    $acc_operation = new Acc_Operation($this->db);
                    $acc_operation->date = $e_date;
                    $acc_operation->poste = $poste_vat;
                    $acc_operation->amount = $value;
                    $acc_operation->grpt = $seq;
                    $acc_operation->jrn = $p_jrn;
                    $acc_operation->type = 'c';
                    $acc_operation->periode = $tperiode;
                    if ($value<0)
                    {
                        $tot_debit=bcadd($tot_debit, abs($value));
                        $tot_debit=round($tot_debit, 2);
                    }
                    $acc_operation->insert_jrnx();

                    // if TVA is on both side, we deduce it immediately
                    $poste_vat = $oTva->get_side('d');
                    if ( $poste_vat == '#')
                    {
                        $poste_vat=$oTva->get_side('c');
                    }
                    $acc_operation = new Acc_Operation($this->db);
                    $acc_operation->date = $e_date;
                    $acc_operation->poste = $poste_vat;
                    $acc_operation->amount = $value;
                    $acc_operation->grpt = $seq;
                    $acc_operation->jrn = $p_jrn;
                    $acc_operation->type = 'd';
                    $acc_operation->periode = $tperiode;
                    $acc_operation->insert_jrnx();
                    $tot_debit = bcadd($tot_debit, $value);
                    $tot_debit = round($tot_debit, 2);
                    $n_both = $value;

                }

            } // if ($g_parameter->MY_TVA_USE=='Y')
            /*
             * Balance the amount on D and C , the difference must be inserted as "difference due to a rounded value"
             * Value are retrieve thanks $seq
             */
            
            /* insert into jrn */
            if ( DEBUGNOALYSS > 1 ) { echo __LINE__." tot_debit ".round($tot_debit,2)."<br>"; }
            $acc_operation = new Acc_Operation($this->db);
            $acc_operation->date = $e_date;
            $acc_operation->echeance = $e_ech;
            $acc_operation->amount = abs(round($tot_debit, 2));
            $acc_operation->desc = $e_comm;
            $acc_operation->grpt = $seq;
            $acc_operation->jrn = $p_jrn;
            $acc_operation->periode = $tperiode;
            $acc_operation->pj = $e_pj;
            $acc_operation->mt = $mt;
            $acc_operation->currency_id=$p_currency_code;
            $acc_operation->currency_rate=$p_currency_rate;
            $acc_operation->currency_rate_ref=$currency_rate_ref->get_rate();
            
            if ( ! $this->jr_id=$acc_operation->insert_jrn() ) {
                throw new Exception (_("Erreur de balance"));
            }

            $this->pj = $acc_operation->update_receipt();

            /**
             *
             * if the given receipt number is equal to one computed then increment
             */
            if ($e_pj == $this->pj && noalyss_strlentrim($e_pj) != 0) {
                $this->inc_seq_pj();
            }
            $this->db->exec_sql("update jrn set jr_internal=$1  where jr_grpt_id =  $2" ,[$internal,$seq]);
            

            /* update quant_sold */
            $this->db->exec_sql('update quant_sold set qs_internal = $1 
                    where j_id in (select j_id from jrnx where j_grpt=$2)'
                    , array($internal, $seq));

            /* Save the attachment or generate doc */
            if (isset($_FILES['pj'])) {
                if (noalyss_strlentrim($_FILES['pj']['name']) != 0)
                    $this->db->save_receipt($seq);
                else
                /* Generate an invoice and save it into the database */
                if (isset($_POST['gen_invoice'])) {
                    $file = $this->create_document($internal, $p_array);
                    $this->doc=HtmlInput::show_receipt_document($this->jr_id,h($file));
                }
            }
            //----------------------------------------
            // Save the payer
            //----------------------------------------
            if ($e_mp != 0) {
                /** 
                 * Date
                 */
                $pay_date=($mp_date=="")?$e_date:$mp_date;
                
                /* mp */
                $mp = new Acc_Payment($this->db, $e_mp);
                $mp->load();


                /* jrnx */
                $acseq = $this->db->get_next_seq('s_grpt');
                $acjrn = new Acc_Ledger($this->db, $mp->get_parameter('ledger_target'));
                $acinternal = $acjrn->compute_internal_code($acseq);

                /*
                 * for the use of the card of the bank
                 */
                if ( $acjrn->get_type()=='FIN') {
                    $acjrn=new Acc_Ledger_Fin($this->db, $mp->get_parameter('ledger_target'));
                    $acfiche=new Fiche($this->db,$acjrn->get_bank());
                    $fqcode=$acfiche->strAttribut(ATTR_DEF_QUICKCODE);
                } else {
                    $fqcode = ${'e_mp_qcode_' . $e_mp};
                    $acfiche = new Fiche($this->db);
                    $acfiche->get_by_qcode($fqcode);
                }

                /* Insert paid by  */
                $acc_pay = new Acc_Operation($this->db);
                $acc_pay->date = $pay_date;
                /* get the account and explode if necessary */
                $sposte = $acfiche->strAttribut(ATTR_DEF_ACCOUNT);
                // if 2 accounts, take only the debit one for customer
                if (strpos($sposte, ',') != 0) {
                    $array = explode(',', $sposte);
                    $poste_val = $array[0];
                } else {
                    $poste_val = $sposte;
                }
                 // Convert paid amount in EUR
                $acompte_eur=bcdiv($acompte, $p_currency_rate);   

                $famount=bcsub($cust_amount,$acompte_eur);
                $acc_pay->poste = $poste_val;
                $acc_pay->qcode = $fqcode;
                $acc_pay->amount = abs(round($famount, 2));
                $acc_pay->desc = null;

                $acc_pay->grpt = $acseq;
                $acc_pay->jrn = $mp->get_parameter('ledger_target');
                $acc_pay->periode = $tperiode;
                $acc_pay->type = ($famount >= 0) ? 'd' : 'c';
                $let_pay=$acc_pay->insert_jrnx();

                /* Insert supplier  */
                $acc_pay = new Acc_Operation($this->db);
                $acc_pay->date = $pay_date;
                $acc_pay->poste = $poste;
                $acc_pay->qcode = $e_client;
                $acc_pay->amount = abs(round($famount, 2));
                $acc_pay->desc = null;
                $acc_pay->grpt = $acseq;
                $acc_pay->jrn = $mp->get_parameter('ledger_target');
                $acc_pay->periode = $tperiode;
                $acc_pay->type = ($famount >= 0) ? 'c' : 'd';
                $let_other = $acc_pay->insert_jrnx();

                // insert into operation_currency customer
                $operation_currency=new Operation_currency_SQL($this->db);
                $operation_currency->oc_amount=bcsub($tot_amount_cur,$acompte);
                $operation_currency->oc_vat_amount=0;
                $operation_currency->oc_price_unit=0;
                $operation_currency->j_id=$let_other;
                $operation_currency->insert();                
                
                // insert into operation_currency bank
                $operation_currency=new Operation_currency_SQL($this->db);
                $operation_currency->oc_amount=bcsub($tot_amount_cur,$acompte);
                $operation_currency->oc_vat_amount=0;
                $operation_currency->oc_price_unit=0;
                $operation_currency->j_id=$let_pay;
                $operation_currency->insert();                
                
                // Add info for currency
                $acc_pay->currency_id=$p_currency_code;
                $acc_pay->currency_rate=$p_currency_rate;
                $acc_pay->currency_rate_ref=$currency_rate_ref->get_rate();
                
                
                /* insert into jrn */
                $acc_pay->mt = $mt;
                $acjrn->grpt_id = $acseq;
                $acc_pay->desc = (!isset($e_comm_paiement) || noalyss_strlentrim($e_comm_paiement) == 0) ? $e_comm : $e_comm_paiement;
                $mp_jr_id = $acc_pay->insert_jrn();
                $acjrn->update_internal_code($acinternal);
                // add an automatic PJ if ODS
                if ($acjrn->get_type()=="ODS") {
                    $acc_pay->pj=$acjrn->guess_pj();
                    $acc_pay->update_receipt();
                }
                $r1 = $this->get_id($internal);
                $r2 = $this->get_id($acinternal);

                /*
                 * add lettering
                 */
                $oletter = new Lettering($this->db);
                $oletter->insert_couple($let_tiers, $let_other);


                /* set the flag paid */
                $Res = $this->db->exec_sql("update jrn set jr_rapt='paid' where jr_id=$1", array($r1));

                /* Reconcialiation */
                $rec = new Acc_Reconciliation($this->db);
                $rec->set_jr_id($r1);
                $rec->insert($r2);


                /*
                 * save also into quant_fin
                 */

                /* get ledger property */
                $ledger = new Acc_Ledger_Fin($this->db, $acc_pay->jrn);
                $prop = $ledger->get_propertie();

                /* if ledger is FIN then insert into quant_fin */
                if ($prop['jrn_def_type'] == 'FIN') {
                    $ledger->insert_quant_fin($acfiche->id, $mp_jr_id, $cust->id, bcmul($famount, 1),$let_other);
                }
            }
            /*----------------------------------------------
           * Save the note
           ----------------------------------------------*/
            if (isset($p_array['jrn_note_input']) && !empty($p_array['jrn_note_input'])) {
                $acc_operation_note=Acc_Operation_Note::build_jrn_id(-1);
                $acc_operation_note->setNote($p_array['jrn_note_input']);
                $acc_operation_note->setOperation_id( $this->jr_id);
                $acc_operation_note->save();
            }
        } catch (Exception $e) {
              record_log($e);
            echo '<span class="error">' .
            'Erreur dans l\'enregistrement ' .
            __FILE__ . ':' . __LINE__ . ' ' .
            $e->getMessage();
            echo $e->getTraceAsString();

            $this->db->rollback();
            throw new Exception ($e);
        }
        $this->db->commit();

        return $internal;
    }

    /*!
     * @brief show the summary of the operation and propose to save it
     * @param array contains normally $_POST. It proposes also to save
     * the Analytic accountancy
     * @param $p_summary false for the feedback, true to show the summary
     * @return string
     *
     */

    function confirm($p_array, $p_summary = false) {
        global $g_parameter,$g_user;
        extract($p_array, EXTR_SKIP);
        if ( !isset($p_array['jrn_note_input'])) {$p_array['jrn_note_input']='';}
        // don't need to verify for a summary
        if (!$p_summary)
        {
            $this->verify($p_array);
        }
        $anc = null;
        // to show a select list for the analytic & VAT USE
        // if analytic is op (optionnel) there is a blank line

        bcscale(4);
        $client = new Fiche($this->db);
        $client->get_by_qcode($e_client, true);

        $client_name = $client->getName() .
                ' ' . $client->strAttribut(ATTR_DEF_ADRESS) . ' ' .
                $client->strAttribut(ATTR_DEF_CP) . ' ' .
                $client->strAttribut(ATTR_DEF_CITY);
        $lPeriode = new Periode($this->db);
        if ($this->check_periode() == true) {
            $lPeriode->p_id = $period;
        } else {
            $lPeriode->find_periode($e_date);
        }
        $date_limit = $lPeriode->get_date_limit();
        $r = "";
        $r .= '<div id="summary_op1" >';
        $r.='<TABLE>';
        if ( $p_summary ) {
            $jr_id=$this->db->get_value('select jr_id from jrn where jr_internal=$1',array($this->internal));
            $r.="<tr>";
            $r.='<td>';
            $r.=_('Détail opération ');
            $r.='</td>';
            $r.='<td>';
            $r.=sprintf ('<a class="line" style="display:inline" href="javascript:modifyOperation(%d,%d)">%s</a>',
                    $jr_id,dossier::id(),$this->internal);
            $r.='</td>';
            $r.="</tr>";
        }
        $r.='<tr>';
        $span=$this->warn_manual_receipt($p_array);
        if ( ! $p_summary) {
            $r.='<td>' . _('Numéro Pièce') .$span.'</td><td>'. hb($e_pj) . '</td>';
        } else {
            if ( $g_parameter->MY_PJ_SUGGEST=="A" ||$g_user->check_action(UPDRECEIPT)==0)
                $e_pj=$this->pj;
             if ( strcmp($this->pj,$e_pj) != 0 )
            {
                $r.='<td>' . _('Numéro Pièce') .$span.'</td><td>'. hb($this->pj) .
                        '<span class="notice"> '._('Attention numéro pièce existante, elle a du être adaptée').'</span></td>';
            } else {
                $r.='<td>' . _('Numéro Pièce') .$span.'</td><td>'. hb($this->pj) . '</td>';
            }
        }
        $r.='</tr>';
        $r.='<tr>';
        $r.='<td> ' . _('Date') . '</td><td> ' . hb($e_date) . '</td>';
        $r.='</tr>';
        $r.='<tr>';
        $r.='<td>' . _('Echeance') . '</td><td> ' . hb($e_ech) . '</td>';
        $r.='</tr>';
        $r.='<tr>';
        $r.='<td> ' . _('Période Comptable') . '</td><td> ' .hb( $date_limit['p_start'] . '-' . $date_limit['p_end']) . '</td>';
        $r.='</tr>';
        $r.='</table>';
        $r.='</div>';
        $r .= '<div id="summary_op2">';
        $r.='<table>';
        $r.='<tr>';
        $r.='<td> ' . _('Journal') . '</td><td> ' . hb($this->get_name()) . '</td>';
        $r.='</tr>';
        $r.='<tr>';
        $r.='<td> ' . _('Libellé') . '</td><td> ' . hb($e_comm) . '</td>';
        $r.='</tr>';
        
        $r.='<tr>';
        $r.='<td> ' . _('Client') . '</td><td> ' . hb($e_client . ':' . $client_name) . '</td>';
        $r.='</tr>';
        $r.='</table>';
        $r.='<pre>'._('Note').' '.h($p_array['jrn_note_input']).'</pre>';
        $r.='</div>';
        $r.='<div style="float:none;clear:both">';
        $r.='</div>';
        
        $r.='<h2 class="h-section" class="h-section">' . _('Détail articles vendus') . '</h2>';
        $r.='<p class="decale">';
        $r.='<table class="result" >';
        $r.='<TR>';
        $r.="<th>" . _('Code') . "</th>";
        $r.="<th>" . _('Dénomination') . "</th>";
        $r.="<th style=\"text-align:right\">" . _('prix') . "</th>";
        $r.="<th style=\"text-align:right\">" . _('quantité') . "</th>";


        if ($g_parameter->MY_TVA_USE == 'Y') {
            $r.="<th style=\"text-align:right\">" . _('tva') . "</th>";
            $r.='<th style="text-align:right"> ' . _('Montant TVA') . '</th>';
            $r.='<th style="text-align:right">' . _('Montant HTVA') . '</th>';
            $r.='<th style="text-align:right">' . _('Montant TVAC') . '</th>';
        } else {
            $r.='<th style="text-align:right">' . _('Montant') . '</th>';
        }
        /* if we use the AC */
        if ($g_parameter->MY_ANALYTIC != 'nu') {
            $anc = new Anc_Plan($this->db);
            $a_anc = $anc->get_list();
            $x = count($a_anc);
            /* set the width of the col */
            $r.='<th colspan="' . $x . '">' . _('Compt. Analytique') . '</th>';

            /* add hidden variables pa[] to hold the value of pa_id */
            $r.=Anc_Plan::hidden($a_anc);
        }
        $r.='</tr>';
        $tot_amount = 0.0;
        $tot_tva = 0.0;
        for ($i = 0; $i < $nb_item; $i++) {
            if (noalyss_strlentrim(${"e_march" . $i}) == 0)
                continue;

            /* retrieve information for card */
            $fiche = new Fiche($this->db);
            $fiche->get_by_qcode(${"e_march" . $i});
            if ($g_parameter->MY_UPDLAB == 'Y')
                $fiche_name = h(${"e_march" . $i . "_label"});
            else
                $fiche_name = $fiche->strAttribut(ATTR_DEF_NAME);
            if ($g_parameter->MY_TVA_USE == 'Y') {
                $idx_tva = ${"e_march" . $i . "_tva_id"};
                $oTva =  Acc_Tva::build($this->db,$idx_tva);
                $oTva->load();
            }
            $op = new Acc_Compute();
            $amount = bcmul(${"e_march" . $i . "_price"}, ${'e_quant' . $i});
            $op->set_parameter("amount", $amount);
            if ($g_parameter->MY_TVA_USE == 'Y') {
                $op->set_parameter('amount_vat_rate', $oTva->get_parameter('rate'));
                $op->compute_vat();
                $tva_computed = $op->get_parameter('amount_vat');
                $tva_item = ${"e_march" . $i . "_tva_amount"};
                if (isset($tva[$idx_tva]))
                    $tva[$idx_tva]=bcadd($tva[$idx_tva],$tva_item,2);
                else
                    $tva[$idx_tva] = $tva_item;
                $tot_tva = round(bcadd($tva_item, $tot_tva), 2);
            }
            $tot_amount = round(bcadd($tot_amount, $amount), 2);

            $r.='<tr>';
            $r.='<td>';
            $r.=${"e_march" . $i};
            $r.='</td>';
            $r.='<TD style="border-bottom:1px dotted grey;">';
            $r.=$fiche_name;
            $r.='</td>';
            $r.='<td class="num">';
            $r.=nbm(${"e_march" . $i . "_price"},4);
            $r.='</td>';
            $r.='<td class="num">';
            $r.=nbm(${"e_quant" . $i},4);
            $r.='</td>';
            $both_side=0;
            if ($g_parameter->MY_TVA_USE == 'Y') {
                $r.='<td class="num">';
                $r.=$oTva->get_parameter('label');
                $r.='</td>';
                $both_side=$oTva->get_parameter("both_side");
                /* warning if tva_computed and given are not the
                  same */
                if (bcsub($tva_item, $tva_computed) != 0 && ! ($tva_item == 0 && $both_side == 1)) {
                    $r.='<td style="background-color:red" class="num">';
                    $r.=Icon_Action::infobulle(28);
                    $r.='<a href="#" class="error" style="display:inline" title="' . _("Attention Différence entre TVA calculée et donnée") . '">'
                            . nbm($tva_item) . '<a>';
                } else {
                    $r.='<td  class="num">';
                    $r.=nbm($tva_item);
                }
                $r.='</td>';
                $r.='<td class="num">';
                $r.=nbm($amount);
                $r.='</td>';
                $tot_row = bcadd($tva_item, $amount);
                $r.=td(nbm($tot_row), 'class="num"');
            } else {
                $r.='<td class="num">';
                $r.=nbm($amount);
                $r.='</td>';
            }
            // encode the pa
            if ($g_parameter->MY_ANALYTIC != 'nu' 
                    && $g_parameter->match_analytic($fiche->strAttribut(ATTR_DEF_ACCOUNT))==TRUE) { // use of AA
                // show form
                $anc_op = new Anc_Operation($this->db);
                $null = ($g_parameter->MY_ANALYTIC == 'op') ? 1 : 0;
                $r.='<td>';
                $p_mode = ($p_summary == false) ? 1 : 0;
                $p_array['pa_id'] = $a_anc;
                /* op is the operation it contains either a sequence or a jrnx.j_id */
                $r.=HtmlInput::hidden('op[]=', $i);
                $r.=$anc_op->display_form_plan($p_array, $null, $p_mode, $i,  round($amount,2));
                $r.='</td>';
            }


            $r.='</tr>';
        } // end loop item
        //
        // Add the sum
        $decalage=($g_parameter->MY_TVA_USE == 'Y')?'<td></td><td></td><td></td><td></td>':'<td></td>';
         $tot = bcadd($tot_amount, $tot_tva, 2);
        $tot_eur=round(bcdiv($tot, $p_currency_rate),2);
        $tot_str=nbm($tot);
        $str_tot=_('Totaux');
        
        // Get currency code
        $default_currency=new Acc_Currency($this->db,0);
        $str_code=$default_currency->get_code();
        if ( $p_currency_code != 0 ) {
            $acc_currency=new Acc_Currency($this->db);
            $acc_currency->set_id($p_currency_code);
            $str_code=$acc_currency->get_code();
        }
        // Format amount
        $tot_amount=nbm($tot_amount);
        $tot_tva=nbm($tot_tva);
    $rate=_("Taux ");      
if ( $g_parameter->MY_TVA_USE=="Y")        {
            $r.=<<<EOF
    <tr class="highlight">
        {$decalage}            
         <td>
                    {$str_tot} {$str_code}
         </td>
        <td class="num">
            {$tot_tva}
        </td>
        <td class="num">
            {$tot_amount}
        </td>
        <td class="num">
            {$tot_str} {$str_code}
        </td>
       </tr>
EOF;
        if ($p_currency_code !=0) {
          $sql_currency=new Currency_SQL($this->cn,0);
          $iso_code=$sql_currency->getp("cr_code_iso");

    $r.=<<<EOF
    <tr class="highlight">
        {$decalage}            
         <td>

         </td>
        <td class="num">

        </td>
        <td class="num">
            {$rate} {$p_currency_rate}
        </td>
        <td class="num">
            {$tot_eur}  {$iso_code}
        </td>
    </tr>
EOF;
            } 

        } else {
    $sql_currency=new Currency_SQL($this->cn,$p_currency_code);
    $str_code=$sql_currency->getp("cr_code_iso");
    $sql_currencydefault=new Currency_SQL($this->cn,0);
    $iso_code=$sql_currencydefault->getp("cr_code_iso");
            // without VAT
            $r.=<<<EOF
    <tr class="highlight">
        {$decalage}            
         <td>
                    {$str_tot} {$str_code}
         </td>
        <td class="num">

        </td>
        <td class="num">

        </td>
        <td class="num">
            {$tot_str} {$str_code}
        </td>
            </tr>
    <tr class="highlight">
        {$decalage}            
         <td>
         </td>
        <td>

        </td>
        <td>
     {$rate} {$p_currency_rate}
        </td>
        <td class="num">
           {$tot_eur} {$iso_code}
        </td>
    </tr>
EOF;
    }
        $r.='</table>';
        $r.='</p>';
        if ($g_parameter->MY_ANALYTIC != 'nu' && ! $p_summary) // use of AA
            $r.='<input type="button" class="button" value="' . _('Vérifiez Imputation Analytique') . '" onClick="verify_ca(\'\');">';
        $r.='<div id="total_div_id" >';
        $r.='<h2 class="h-section">Totaux</h2>';
        $other_tax_label="";
        $other_tax_amount="";
        if ( $this->has_other_tax() && isset($p_array['other_tax'])) {
            $other_tax_label=_("Autre taxe");
            $other_tax_amount=htmlspecialchars($p_array['other_tax_amount']);
        }
        /* use VAT */
        if ($g_parameter->MY_TVA_USE == 'Y') {
            $r.='<table>';
            $r.='<tr><td>Total HTVA</td>';
            $r.=td(hb($tot_amount ),'class="num"');
            foreach ($tva as $i => $value) {
                $oTva=Acc_Tva::build($this->cn, $i);
                $oTva->load();

                $r.='<tr><td>  TVA ' . $oTva->get_parameter('label').'</td>';
                $r.=td(hb(nbm($tva[$i])),'class="num"');
            }
            $r.='<tr>'.td(_('Total TVA')).td(hb($tot_tva),'class="num"');
            if ( ! empty($other_tax_label) ) {
                $r.='<tr>'.td($other_tax_label).td(hb($other_tax_amount),'class="num"');
            }
            if ( $other_tax_amount!="") {$tot=bcadd($tot,$other_tax_amount,2);}
            $r.='<tr>'.td(_('Total TVAC')).td(hb($tot),'class="num"');
            $r.='</table>';
        } else {
            if ( ! empty($other_tax_label) ) {
                $r.='<tr>'.td($other_tax_label).td(hb($other_tax_amount),'class="num"');
            }
            if ( $other_tax_amount!="") {$tot=bcadd($tot,$other_tax_amount,2);}
            $r.='<br>Total '.hb($tot);
        }
        $r.='</div>';
  
            
        /*  Add hidden */
        $r.=HtmlInput::hidden('e_client', $e_client);
        $r.=HtmlInput::hidden('nb_item', $nb_item);
        $r.=HtmlInput::hidden('p_jrn', $p_jrn);
        $r.=HtmlInput::hidden('jrn_note_input',h($p_array['jrn_note_input']));
        $mt = microtime(true);
        $r.=HtmlInput::hidden('mt', $mt);
        $r.=HtmlInput::post_to_hidden(['p_currency_rate','p_currency_code']);
        
        if (isset($period))
        {
            $r.=HtmlInput::hidden('period', $period);
        }
        /* \todo comment les types hidden gérent ils des contenus avec des quotes, double quote ou < > ??? */
        $r.=HtmlInput::hidden('e_comm', $e_comm);
        $r.=HtmlInput::hidden('e_date', $e_date);
        $r.=HtmlInput::hidden('e_ech', $e_ech);
        $r.=HtmlInput::hidden('e_pj', $e_pj);
        $r.=HtmlInput::hidden('e_pj_suggest', $e_pj_suggest);
        if ( $this->has_other_tax() && isset($p_array["other_tax"])) {
            $r.=HtmlInput::hidden("other_tax",$p_array['other_tax']);
            $r.=HtmlInput::hidden("other_tax_amount",$p_array['other_tax_amount']);
        }
        $e_mp = (isset($e_mp)) ? $e_mp : 0;
        $r.=HtmlInput::hidden('e_mp', $e_mp);
        
        if ( isset($repo) )  {
            // Show the available repository
            $r.= $this->select_depot($p_summary,$repo);
        }

        /* if the paymethod is not 0 and if a quick code is given */
        if ($e_mp != 0 && noalyss_strlentrim(${'e_mp_qcode_' . $e_mp}) != 0) {
            $r.=HtmlInput::hidden('e_mp_qcode_' . $e_mp, ${'e_mp_qcode_' . $e_mp});
            $r.=HtmlInput::hidden('acompte', $acompte);
            $r.=HtmlInput::hidden('e_comm_paiement', $e_comm_paiement);
            /* needed for generating a invoice */
            $r.=HtmlInput::hidden('qcode_benef', ${'e_mp_qcode_' . $e_mp});
            $r.=HtmlInput::hidden('mp_date', ${'mp_date'});

            $fname = new Fiche($this->db);
            $fname->get_by_qcode(${'e_mp_qcode_' . $e_mp});
            $r.='<h2 class="h-section">' . "Payé par " . ${'e_mp_qcode_' . $e_mp} .
                    " le ".${"mp_date"}.
                    " " . $fname->getName() . '</h2> ' . '<p class="decale">' . _('Déduction acompte ') . h($acompte) . '</p>' .
                    _('Libellé :') . h($e_comm_paiement) ;
            $r.='<br>';
        }

        $r.=HtmlInput::hidden('jrn_type', $jrn_type);
        for ($i = 0; $i < $nb_item; $i++) {
            $r.=HtmlInput::hidden("e_march" . $i, ${"e_march" . $i});
            if (isset(${"e_march" . $i . "_label"}))
                $r.=HtmlInput::hidden("e_march" . $i . "_label", ${"e_march" . $i . "_label"});
            $r.=HtmlInput::hidden("e_march" . $i . "_price", ${"e_march" . $i . "_price"});
            if ($g_parameter->MY_TVA_USE == 'Y') {
                $r.=HtmlInput::hidden("e_march" . $i . "_tva_id", ${"e_march" . $i . "_tva_id"});
                $r.=HtmlInput::hidden("e_march" . $i . "_tva_amount", ${"e_march" . $i . "_tva_amount"});
            }
            $r.=HtmlInput::hidden("e_quant" . $i, ${"e_quant" . $i});
        }
        /* 
         * warning if the amount is positive and expecting a negative one
         */
        $negative=$this->display_negative_warning($tot);
        if ( $negative != "") {
         $r.=span($negative,'class="warning" ');   
        }
        return $r;
    }

    /*!\brief the function extra info allows to
     * - add a attachment
     * - generate an invoice
     * - insert extra info
     * \return string
     */

    public function extra_info() {
        $r = '<div id="facturation_div_id" style="height:185px;height:10rem">';
        // check for upload piece
        $file = new IFile();
        $file->table = 0;
        $file->setAlertOnSize(true);
        $r.='<p class="decale">';
        $r.=_("Ajoutez une pièce justificative ");
        $r.=$file->input("pj", "");

        if ($this->db->count_sql("select md_id,md_name from document_modele where md_affect='VEN' ") > 0) {


            $r.=_('ou générer une facture') . ' <input type="checkbox" name="gen_invoice" CHECKED>';
            // We propose to generate  the invoice and some template
            $doc_gen = new ISelect();
            $doc_gen->name = "gen_doc";
            $doc_gen->value = $this->db->make_array(
                    "select md_id,md_name " .
                    " from document_modele where md_affect='VEN' order by 2");
            $r.=$doc_gen->input() . '<br>';
        }
        $r.='<br>';
        $obj = new IText();
        $r.=_('Numero de bon de commande : ') . $obj->input('bon_comm') . '<br>';
        $r.=_('Communication ou autre information  : ') . $obj->input('other_info') . '<br>';
        $r.='</p>';
        $r.='</div>';
        return $r;
    }

   

    /*!\brief display the form for entering data for invoice,
     * \param $p_array is null or you can put the predef operation or the $_POST
     *
     * \return HTML string
     */

    function input($p_array = null, $p_readonly = 0) {
        global $g_parameter, $g_user;
        // load ledger definition
        $this->load();
        $http=new HttpInput();
        $http->set_array([]);
        if ($p_array != null) {
            extract($p_array, EXTR_SKIP);
            $http->set_array($p_array);
        }
        if ( !isset($p_array['jrn_note_input'])) {$p_array['jrn_note_input']='';}
        $flag_tva = $g_parameter->MY_TVA_USE;
        /* Add button */
        
        $str_add_button_tiers = "";
        $add_card=FALSE;
        if ($g_user->check_action(FICADD) == 1) {
            $add_card=TRUE; 
             $str_add_button_tiers = $this->add_card("deb", "e_client");
        }
        
        // The first day of the periode
        $oPeriode = new Periode($this->db);
        list ($l_date_start, $l_date_end) = $oPeriode->get_date_limit($g_user->get_periode());
        if ($g_parameter->MY_DATE_SUGGEST == 'Y')
            $op_date = (!isset($e_date) ) ? $l_date_start : $e_date;
        else
            $op_date = (!isset($e_date) ) ? '' : $e_date;

        $e_ech = (isset($e_ech)) ? $e_ech : "";
        $e_comm = (isset($e_comm)) ? $e_comm : "";

        $r = '';
        $r.=dossier::hidden();
        $f_legend = _('Client');

        $Echeance = new IDate();
        $Echeance->setReadOnly(false);

        $Echeance->tabindex = 2;
        $label = Icon_Action::infobulle(4);
        $f_echeance = $Echeance->input('e_ech', $e_ech, _('Echéance') . $label);
        $Date = new IDate();
        $Date->setReadOnly(false);

        $f_date = $Date->input("e_date", $op_date);

        $f_periode = '';
        // Periode
        //--
        if ($this->check_periode() == true) {
            $l_user_per = $g_user->get_periode();
            $def = (isset($periode)) ? $periode : $l_user_per;

            $period = new IPeriod("period");
            $period->user = $g_user;
            $period->cn = $this->db;
            $period->value = $def;
            $period->type = OPEN;
            try {
                $l_form_per = $period->input();
            } catch (Exception $e) {
                if ($e->getCode() == 1) {
                    throw new Exception( _("Aucune période ouverte") );
                }
            }
            $label = Icon_Action::infobulle(3);
            $f_periode = '<td>' . _("Période comptable") . "</td> <td> $label " . $l_form_per . '</td>';
        }
        /* if we suggest the next pj, then we need a javascript */
        $add_js = "";
        if ($g_parameter->MY_PJ_SUGGEST != 'N') {
            $add_js = "update_pj();";
        }
        if ($g_parameter->MY_DATE_SUGGEST == 'Y') {
            $add_js.='get_last_date();';
        }
        $add_js.='update_name();';
        $add_js.='update_pay_method();';
        $add_js.='update_row("sold_item");';
        $add_js.='update_other_tax();';
        $add_js.='update_visibility_quantity();';

        $wLedger = $this->select_ledger('VEN', 2,FALSE);
        if ($wLedger == null)
            throw new Exception(_('Pas de journal disponible'));
        $wLedger->table = 0;
        $wLedger->javascript = "onChange='update_predef(\"ven\",\"f\",\"".$_REQUEST['ac']."\");$add_js'";
        $wLedger->label = " Journal " . Icon_Action::infobulle(2);

        $f_jrn = $wLedger->input();

        $Commentaire = new IText();
        $Commentaire->table = 0;
        $Commentaire->setReadOnly(false);
        $Commentaire->size = (empty($e_comm))?60:strlen($e_comm)+5;
        $Commentaire->size = ($Commentaire->size<60)?60:$Commentaire->size;
        $Commentaire->tabindex = 3;

        $label = Icon_Action::infobulle(1);

        $f_desc = $Commentaire->input("e_comm", $e_comm) ;
        // PJ
        //--
        /* suggest PJ ? */
        $default_pj = '';
        if ($g_parameter->MY_PJ_SUGGEST != 'N') {
            $default_pj = $this->guess_pj();
        }

        $pj = new IText();
        if ( $g_parameter->MY_PJ_SUGGEST=='A'||$g_user->check_action(UPDRECEIPT)==0)
        {
               $pj->setReadOnly(true);
               $pj->id="e_pj";
        }

        $pj->table = 0;
        $pj->name = "e_pj";
        $pj->size = 10;
        $pj->value = (isset($e_pj)) ? $e_pj : $default_pj;
        $f_pj = $pj->input() . HtmlInput::hidden('e_pj_suggest', $default_pj);
        // Display the customer
        //--
        $fiche = 'deb';

        // Save old value and set a new one
        //--
        $e_client = ( isset($e_client) ) ? $e_client : "";
        $e_client_label = "&nbsp;"; //str_pad("",100,".");
        // retrieve e_client_label
        //--

        if (noalyss_strlentrim($e_client) != 0) {
            $fClient = new Fiche($this->db);
            $fClient->get_by_qcode($e_client);
            $e_client_label = $fClient->strAttribut(ATTR_DEF_NAME) . ' ' .
                    ' Adresse : ' . $fClient->strAttribut(ATTR_DEF_ADRESS) . ' ' .
                    $fClient->strAttribut(ATTR_DEF_CP) . ' ' .
                    $fClient->strAttribut(ATTR_DEF_CITY) . ' ';
        }

        $W1 = new ICard();
        $W1->label = "Client " . Icon_Action::infobulle(0);
        $W1->name = "e_client";
        $W1->tabindex = 3;
        $W1->value = $e_client;
        $W1->table = 0;
        $W1->set_dblclick("fill_ipopcard(this);");
        $W1->set_attribute('ipopup', 'ipopcard');

        // name of the field to update with the name of the card
        $W1->set_attribute('label', 'e_client_label');
        // name of the field to update with the name of the card
        $W1->set_attribute('typecard', 'deb');

        // Add the callback function to filter the card on the jrn
        $W1->set_callback('filter_card');
        $W1->set_function('fill_data');
        $W1->javascript = sprintf(' onchange="fill_data_onchange(\'%s\');" ', $W1->name);
        $f_client_qcode = $W1->input();
        $client_label = new ISpan();
        $client_label->table = 0;
        $f_client = $client_label->input("e_client_label", $e_client_label);
        $f_client_bt = $W1->search();


        // Record the current number of article
        $Hid = new IHidden();
        $p_article = ( isset($nb_item)) ? $nb_item : $this->get_min_row();
        $r.=$Hid->input("nb_item", $p_article);
        $p_article = ($p_article < $this->get_min_row()) ? $this->get_min_row() : $p_article;


        $f_legend_detail = _("Détail articles vendus");

        // For each article
        //--
        for ($i = 0; $i < $p_article; $i++) {
            // Code id, price & vat code
            //--
            $march = (isset(${"e_march$i"})) ? ${"e_march$i"} : "";
            $march_price = (isset(${"e_march" . $i . "_price"})) ? ${"e_march" . $i . "_price"} : ""  ;
            if ($flag_tva == 'Y') {
                $march_tva_id = (isset(${"e_march$i" . "_tva_id"})) ? ${"e_march$i" . "_tva_id"} : "";
                $march_tva_amount = (isset(${"e_march$i" . "_tva_amount"})) ? ${"e_march$i" . "_tva_amount"} : "";
            }
            $march_label = (isset(${"e_march" . $i . "_label"})) ? ${"e_march" . $i . "_label"} : "";

            // retrieve the tva label and name
            //--
            if (noalyss_strlentrim($march) != 0 && noalyss_strlentrim($march_label) == 0) {
                $fMarch = new Fiche($this->db);
                $fMarch->get_by_qcode($march);
                $march_label = $fMarch->strAttribut(ATTR_DEF_NAME);
                if ($flag_tva == 'Y') {
                    if (!(isset(${"e_march$i" . "_tva_id"})))
                        $march_tva_id = $fMarch->strAttribut(ATTR_DEF_TVA);
                }
            }
            // Show input
            //--
            $W1 = new ICard();
            $W1->label = "";
            $W1->name = "e_march" . $i;
            $W1->value = $march;
            $W1->table = 0;
            $W1->set_attribute('typecard', 'cred');            
            $W1->set_dblclick("fill_ipopcard(this);");
            $W1->set_attribute('ipopup', 'ipopcard');

            // name of the field to update with the name of the card
            $W1->set_attribute('label', 'e_march' . $i . '_label');
            // name of the field with the price
            $W1->set_attribute('price', 'e_march' . $i . '_price');
            // name of the field with the TVA_ID
            $W1->set_attribute('tvaid', 'e_march' . $i . '_tva_id');
            // Add the callback function to filter the card on the jrn
            $W1->set_callback('filter_card');
            $W1->set_function('fill_data');
            $W1->javascript = sprintf(' onchange="fill_data_onchange(\'%s\');" ', $W1->name);

            $W1->readonly = false;

            $array[$i]['quick_code'] = $W1->input();
            $array[$i]['bt'] = $W1->search();
            $array[$i]['card_add']=($add_card==TRUE)?$this->add_card("cred", $W1->id):"";
            // For computing we need some hidden field for holding the value
            $array[$i]['hidden'] = '';
            if ($flag_tva == 'Y')
                $array[$i]['hidden'].=HtmlInput::hidden('tva_march' . $i, 0);

            $htva = new INum('htva_march' . $i);
            $htva->readOnly = 1;
            $htva->value = 0;
            $array[$i]['htva'] = $htva->input();

            if ($g_parameter->MY_TVA_USE == 'Y')
                $tvac = new INum('tvac_march' . $i);
            else
                $tvac = new IHidden('tvac_march' . $i);

            $tvac->readOnly = 1;
            $tvac->value = 0;
            $array[$i]['tvac'] = $tvac->input();

            if ( $g_parameter->MY_UPDLAB == 'Y')
            {
                $Span=new IText("e_march".$i."_label");
                $Span->style='class="input_text label_item"';
            } else
            {
                $Span=new ISpan("e_march".$i."_label");
                $Span->extra='class="label_item"';
            }
            $Span->value = $march_label;
            $Span->setReadOnly(false);
            // card's name, price
            //--
            $array[$i]['denom'] = $Span->input("e_march" . $i . "_label", $march_label);
            // price
            $Price = new INum();
            $Price->setReadOnly(false);
            $Price->size = 9;
            $Price->javascript = "onblur=\"format_number(this,4);clean_tva($i);compute_ledger($i)\"";
            $array[$i]['pu'] = $Price->input("e_march" . $i . "_price", $march_price);
            $array[$i]['tva'] = '';
            $array[$i]['amount_tva'] = '';
            // if tva is not needed then no tva field
            if ($flag_tva == 'Y') {
                // vat label
                //--
                $Tva = new ITva_Popup($this->db);
                $Tva->in_table = true;
                $Tva->set_attribute('compute', $i);
                $Tva->set_filter("sale");

                $Tva->js = 'onblur="clean_tva(' . $i . ');compute_ledger(' . $i . ')"';
                $Tva->value = $march_tva_id;
                $array[$i]['tva'] = $Tva->input("e_march$i" . "_tva_id");
                // vat amount
                //--
                $wTva_amount = new INum();
                $wTva_amount->readOnly = false;
                $wTva_amount->size = 6;
                $wTva_amount->javascript = "onblur='format_number(this);compute_ledger($i)'";
                $array[$i]['amount_tva'] = $wTva_amount->input("e_march" . $i . "_tva_amount", $march_tva_amount);
            }
            // quantity
            //--
            $quant = (isset(${"e_quant$i"})) ? ${"e_quant$i"} : "1";
            $Quantity = new INum();
            
            $Quantity->setReadOnly(false);
            $Quantity->size = 8;
            $Quantity->javascript = "onchange=\"format_number(this,2);clean_tva($i);compute_ledger($i);\"";
            $array[$i]['quantity'] = $Quantity->input("e_quant" . $i, $quant);
        }// foreach article
        $f_type = _('Client');
         
        // Currency
        $currency_select = $this->CurrencyInput("currency_code", "p_currency_rate" , "p_currency_euro");
        $currency_select->selected=$http->extract('p_currency_code','string',0);
        
        $currency_input=new INum("p_currency_rate");
        $currency_input->id="p_currency_rate";
        $currency_input->prec=8;
        $currency_input->value=$http->extract('p_currency_rate','string',1);
        $currency_input->javascript='onchange="format_number(this,4);CurrencyCompute(\'p_currency_rate\',\'p_currency_euro\');"';
        
        $currency=new Acc_Currency($this->db,0);
        
        // 
        // Button for template operation
        //
        ob_start();
        echo '<div id="predef_form">';
        echo HtmlInput::hidden('p_jrn_predef', $this->id);
        $op=new Pre_operation($this->db);
        $op->set_jrn_type("VEN");
        $op->set_p_jrn($this->id);
        $op->set_od_direct('f');
        $url=http_build_query(array('p_jrn_predef'=>$this->id, 'ac'=>$http->request('ac'),
            'gDossier'=>dossier::id()));
        echo $op->form_get('do.php?'.$url);
        echo '</div>';
        $str_op_template=ob_get_contents();
        ob_end_clean();

        ob_start();
        require_once NOALYSS_TEMPLATE.'/form_ledger_detail.php';
        $form_ledger_detail=ob_get_contents();
        ob_end_clean();

        $r.=$form_ledger_detail;

        // Set correctly the REQUEST param for jrn_type
        $r.=HtmlInput::hidden('jrn_type', 'VEN');
        $r.= Html_Input_Noalyss::ledger_add_item("O");
        $r.= create_script("$('" . $Date->id . "').focus()");
        $r.='<div id="additional_tax_div">';
        $r.=$this->input_additional_tax();
        $r.='</div>';
        return $r;
    }
    /**
     * Retrieve data from the view v_detail_sale , gives all the row of an operation
     * 
     * @remark  $g_user connected user
     * @param $p_from jrn.jr_tech_per from 
     * @param type $p_end jrn.jr_tech_per to
     * @param $p_filter_operation valid option : all, paid, unpaid
     * @return type
     */
    function get_detail_sale($p_from,$p_end,$p_filter_operation='all')
    {
        global $g_user;
        // Journal valide
        if ( $this->id == 0 ) die (__FILE__.":".__LINE__." Journal invalide");
        
        // Securite
        if ( $g_user->get_ledger_access($this->id) == 'X' ) return null;
        
        switch ( $p_filter_operation)
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
        // get the data from the view
        $sql = "select * 
                from v_detail_sale
                 where 
                jr_def_id = $1 
                and  jr_date >= (select p_start from parm_periode where p_id = $2) 
                {$sql_filter}
		and  jr_date <= (select p_end from parm_periode where p_id  = $3) "
                .' order by jr_date,substring(jr_pj_number,\'[0-9]+$\')::numeric asc ';
        $ret = $this->db->exec_sql($sql, array($this->id,$p_from, $p_end));
        return $ret;
    }
    /**
     * @brief compute an array with the heading cells for the
     * details, used for the export in CSV
     * @return array
     */
    static function heading_detail_sale()
    {
        $array['jr_id'] = _('Numéro opération');
        $array['jr_date'] = _('Date');
        $array['jr_date_paid'] = _('Date paiement');
        $array['jr_ech'] = _('Date échéance');
        $array['jr_tech_per'] = _('Période');
        $array['jr_comment'] = _('Libellé');
        $array['jr_pj_number'] = _('Pièce');
        $array['jr_internal'] = _('Interne');
        $array['jr_def_id'] = _('Code journal');
        $array['j_poste'] = _('Poste');
        $array['j_text'] = _('Commentaire');
        $array['j_qcode'] = _('Code Item');
        $array['jr_rapt'] = _('Payé');
        $array['item_card'] = _('N° item');
        $array['item_name'] = _('Nom fiche');
        $array['qs_client'] = _('N° fiche fournisseur');
        $array['tiers_name'] = _('Nom fournisseur');
        $array['quick_code'] = _('Code fournisseur');
        $array['tva_label'] = _('Nom TVA');
        $array['tva_comment'] = _('Commentaire TVA');
        $array['tva_both_side'] = _('TVA annulée');
        $array['vat_sided'] = _('TVA Non Payé');
        $array['vat_code'] = _('Code TVA');
        $array['vat'] = _('Montant TVA');
        $array['price'] = _('Total HTVA');
        $array['quantity'] = _('quantité');
        $array['price_per_unit'] = _('PU');
        $array['htva'] = _('HTVA Opération');
        $array['tot_vat'] = _('TVA Opération');
        $array['tot_vat_np'] = _('TVA ND');
        $array['other_tax'] = _("Autre taxe");
        $array['oc_amount'] = _('Mont. Devise');
        $array['oc_vat_amount'] = _('Mont. TVA Devise');
        $array['cr_code_iso'] = _('Devise');

        
        return $array;
    }
    
    
    
}

