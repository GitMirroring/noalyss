<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
$str_anc="";
global $div,$g_parameter,$cn,$access,$jr_id,$obj;
?><?php require_once NOALYSS_TEMPLATE.'/ledger_detail_top.php'; ?>
<div class="content" style="padding:0;">
    <?php
    $owner = new Noalyss_Parameter_Folder($cn);
    ?>

    <?php if ($access == 'W') : ?>
        <form  class="print" onsubmit="return op_save(this);">
        <?php endif; ?>

        <?php echo HtmlInput::hidden('whatdiv', $div) . HtmlInput::hidden('jr_id', $jr_id) . dossier::hidden(); ?>
        <table style="width:100%">
            <tr><td>
                    <table>
                            <td></td>
                        <?php
                        $date = new IDate('p_date');
                        $date->value = format_date($obj->det->jr_date);
                        echo td(_('Date')) . td($date->input());
                        ?>
                        <tr>
                            <td></td>
                            <?php
                            $date_ech = new IDate('p_ech');
                            $date_ech->value = format_date($obj->det->jr_ech);
                            echo td(_('Echeance')) . td($date_ech->input());
                            ?>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <?php echo _("Date paiement")?>
                            </td>
                            <td>
                                <?php
                                $date_paid = new IDate('p_date_paid');
                                $date_paid->value = format_date($obj->det->jr_date_paid);
                                echo $date_paid->input();
                                ?>
                            </td>
                        </tr>

                        <tr><td>
                                <?php
                                $bk = new Fiche($cn, $obj->det->array[0]['qp_supplier']);
                                echo td(_('Fournisseur'));

                                $view_card_detail = HtmlInput::card_detail($bk->get_quick_code(), h($bk->getName()), ' class="line" ');
                                echo td($view_card_detail);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                $itext = new IText('npj');
                                $itext->value = strip_tags($obj->det->jr_pj_number);
                                echo td(_('Pièce')) . td($itext->input());
                                ?>
                            </td>
                        <tr>
                            <td>
                                <?php
                                $itext = new IText('lib');
                                $itext->value = strip_tags($obj->det->jr_comment??"");
                                $itext->size = 40;
                                echo td(_('Libellé')) . td($itext->input(), ' colspan="2" ');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Payé</td>
                            <td>
                                <?php
                                $ipaid = new ICheckBox("ipaid", 'paid');
                                $ipaid->selected = ($obj->det->jr_rapt == 'paid');
                                echo $ipaid->input();
                                ?>
                            </td>
                        </tr>

                    </table>
                </td>
                <td style="width:50%;height:100%;vertical-align:top;text-align: center">
                    <table style="width:99%;height:8rem;vertical-align:top;">
                        <tr style="height: 5%">
                            <td style="text-align:center;vertical-align: top">
                                Note
                            </td></tr>
                        <tr>
                            <td style="text-align:center;vertical-align: top">
                                <?php
                                $inote = new ITextarea('jrn_note');
                                $inote->style=' class="itextarea" style="width:90%;height:100%;"';
                                $inote->value = strip_tags($obj->det->note);
                                echo $inote->input();
                                ?>

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div id="operation_tag_td<?=$div?>">
                                    <?php
                                    /******************************************************************************************************************
                                     * Tags on operation
                                     *****************************************************************************************************************/
                                    $tag_operation=new Tag_Operation($cn);
                                    $tag_operation->set_jrn_id($obj->det->jr_id);
                                    $tag_operation->tag_cell($div);
                                    ?>

                          
                                </div>
                                      <?php
                                // Button add tags
                                if ( $access=='W') { echo Tag_Operation::button_search($obj->det->jr_id,$div);}
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

        </table>
            <table class="result" style="margin-left:4px">
                <?php
                bcscale(2);
                $total_htva = 0;
                $total_tvac = 0;
                echo th(_('Quick Code'));
                echo th(_('Description'));

                echo th(_('Prix/Un.'), 'style="text-align:right"');
                echo th(_('Quantité'), 'style="text-align:right"');
                if ($owner->MY_TVA_USE == 'Y')
                {
                    echo th(_('Taux TVA'), 'style="text-align:right"');
                } else
                {
                    echo th('');
                }
                echo th(_('Non ded'), 'style="text-align:right"');

                if ($owner->MY_TVA_USE == 'Y')
                {
                    echo th(_('HTVA'), 'style="text-align:right"');
                    echo th(_('TVA ND'), 'style="text-align:right"');
                    echo th(_('TVA'), 'style="text-align:right"');
                    echo th(_('TVAC'), 'style="text-align:right"');
                } else
                    echo th(_('Total'), 'style="text-align:right"');
                if ( $obj->det->currency_id != 0 ) {
                    $currency=$obj->db->get_value("select cr_code_iso from currency where id=$1",
                            [$obj->det->currency_id]);
                    echo th($currency, 'style="text-align:right"');
                }
                if ($owner->MY_ANALYTIC != 'nu' )
                {
                    $anc = new Anc_Plan($cn);
                    $a_anc = $anc->get_list(' order by pa_id ');
                    $x = count($a_anc);
                    /* set the width of the col */
                    /* add hidden variables pa[] to hold the value of pa_id */
                      $str_anc.='<tr>'.
                           '<th>'.
                           _('Code').
                           '</th>'.
                           '<th>'.
                           _('Poste').
                           '</th>'.
                           '<th>'.
                           _('Montant').
                           '</th>'.
                           '<th colspan="' . $x . '">' 
                           . _('Compt. Analytique') .Anc_Plan::hidden($a_anc). 
                           '</th>'.
                           '</tr>';

                }
                echo '</tr>';
                $sum_charge_euro=0;
                for ($e = 0; $e < count($obj->det->array); $e++)
                {
                    $row = '';
                    $q = $obj->det->array[$e];
                    $fiche = new Fiche($cn, $q['qp_fiche']);
                    $qcode=$fiche->strAttribut(ATTR_DEF_QUICKCODE);
                    $view_card_detail = HtmlInput::card_detail($qcode, "", ' class="line" ');
                    $row = td($view_card_detail);
                    $sym_tva = '';

                    if ($owner->MY_TVA_USE == 'Y' && $q['qp_vat_code'] != '')
                    {
                        /* retrieve TVA symbol */
                        $tva = new Acc_Tva($cn, $q['qp_vat_code']);
                        $tva->load();
                        $sym_tva = h($tva->get_parameter('label'));
                    }
                    if ($owner->MY_UPDLAB == 'Y')
                    {
                        $l_lib = ($q['j_text'] == '') ? $fiche->strAttribut(ATTR_DEF_NAME) : $q['j_text'];
                        $hidden = HtmlInput::hidden("j_id[]", $q['j_id']);
                        $input = new IText("e_march" . $q['j_id'] . "_label", $l_lib);
                        $input->css_size = "100%";
                    } else
                    {
                        $input = new ISpan("e_march" . $q['j_id'] . "_label");
                        $hidden = HtmlInput::hidden("j_id[]", $q['j_id']);
                        $input->value = $fiche->strAttribut(ATTR_DEF_NAME);
                    }
                    $row.=td($input->input() . $hidden);
                    $pu = $q['qp_unit'];
                    $row.=td(nbm($pu,4), 'class="num"');
                    $row.=td(nbm($q['qp_quantite'],4), 'class="num"');
                    $row.=td($sym_tva, 'style="text-align:center"');

                    $no_ded = bcadd($q['qp_dep_priv'], $q['qp_nd_amount']);
                    $row.=td(nbm($no_ded), ' style="text-align:right"');
                    $htva = $q['qp_price'];


                    $row.=td(nbm($htva), 'class="num"');
                    $tva_rounded=round($q['qp_vat'],2);
                    $tvac = bcadd($htva, $tva_rounded);
                    $tvac = bcadd($tvac, $q['qp_nd_tva']);
                    $tvac = bcadd($tvac, $q['qp_nd_tva_recup']);
                    $tvac = bcsub ($tvac,$q['qp_vat_sided']);
                    if ($owner->MY_TVA_USE == 'Y')
                    {
                        $tva_amount_nd = bcadd($q['qp_nd_tva_recup'], $q['qp_nd_tva']);
                        $class = "";
                        if ($q['qp_vat_sided'] <> 0)
                        {
                            $class = ' style="text-decoration:line-through"';
                        }
                        $row.=td(nbm($tva_amount_nd), 'class="num" ' . $class);
                        $row.=td(nbm($tva_rounded), 'class="num" ' . $class);
                        $row.=td(nbm($tvac), 'class="num"');
                    }
                    $total_tvac=bcadd($total_tvac,$tvac);
                    $total_htva=bcadd($htva,$total_htva);
                    /* Analytic accountancy */
                    if ($owner->MY_ANALYTIC != "nu" /*&& $div == 'popup'*/ )
                    {
                        $poste = $fiche->strAttribut(ATTR_DEF_ACCOUNT);
                        if ( $g_parameter->match_analytic($poste))
                        {
                            $anc_op = new Anc_Operation($cn);
                            $anc_op->j_id = $q['j_id'];
                            $anc_op->in_div=$div;
                            $side=($q['j_debit'] == 'f')?'C':'D';

                            echo HtmlInput::hidden('opanc[]', $anc_op->j_id);
                            /* compute total price */
                            bcscale(2);
                            $str_anc.='<tr>';
                            $str_anc.=td($qcode);
                            $str_anc.=td($poste);
                            $str_anc.=td(nbm($htva)." {$side}");
                            $str_anc.=$anc_op->display_table(1, $htva, $div);
                        } 
                    }
                     $class=($e%2==0)?' class="even"':'class="odd"';
                     /*
                      * Display Currency in a column, if invoice not recorded in EUR
                      */
                     if ( $obj->det->currency_id != 0 ) {
                         $value=$obj->db->get_value("select  oc_amount+oc_vat_amount from operation_currency where j_id=$1",[$q['j_id']]);
                         $row.=td(nbm($value,4),' class="num"');
                         $sum_charge_euro=bcadd($sum_charge_euro,$value,4);
                         
                     }
                     echo tr($row,$class);
                }

                if ($owner->MY_TVA_USE == 'Y')
                    $row = td(_('Total'), ' style="font-style:italic;text-align:right;font-weight: bolder;width:auto" colspan="6"');
                else
                    $row = td(_('Total'), ' style="font-style:italic;text-align:right;font-weight: bolder;width:auto" colspan="6"');
                /**
                 * display additional tax if any + currency
                 */
                $sum_add_tax=0;$sum_add_tax_cur=0;
                Additional_Tax::display_row($jr_id,$sum_add_tax,$sum_add_tax_cur,2);
                $sum_charge_euro=bcadd($sum_charge_euro,$sum_add_tax_cur);

                $total_tvac=bcadd($sum_add_tax,$total_tvac);
                if ($owner->MY_TVA_USE == 'N') {
                    $total_htva=bcadd($sum_add_tax,$total_htva);
                }
                $row.=td(nbm($total_htva), 'class="num" style="font-style:italic;font-weight: bolder;"');
                if ($owner->MY_TVA_USE == 'Y')
                    $row.=td("") . td("").td(nbm($total_tvac), 'class="num" style="font-style:italic;font-weight: bolder;"');
                /**
                 * display additional tax if any + currency
                 */


                //Display total in currency
                if ( $obj->det->currency_id != "" && $obj->det->currency_id > 0) 
                {
                    $currency=new Acc_Currency($obj->db, $obj->det->currency_id);
                    $row.= td(nbm($sum_charge_euro,4),' class="num" style="font-style:italic;font-weight: bolder;"');
                }
                echo tr($row);
                
                ?>
            </table>
<?php


/*
 * Info about currency if not in euro
 */
    // Add a row with currency and amount
    if ( $obj->det->currency_id != "" && $obj->det->currency_id > 0) 
    {
        $currency=new Acc_Currency($obj->db, $obj->det->currency_id);
        $four_space="&nbsp;"."&nbsp;"."&nbsp;"."&nbsp;";
        
        echo  $currency->get_code(),$four_space;
        echo _("Taux utilisé"),"&nbsp;", nbm($obj->det->currency_rate,4),$four_space;
        echo _("Taux Réf"), "&nbsp;",nbm($obj->det->currency_rate_ref,4).$four_space;
        echo _("Montant en devise"), "&nbsp;",nbm($sum_charge_euro,4).$four_space;
    }
?>



<?php
require_once NOALYSS_TEMPLATE.'/ledger_detail_bottom.php';
?>
</div>
