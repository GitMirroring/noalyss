<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
?><?php 
require_once NOALYSS_TEMPLATE.'/ledger_detail_top.php';
 $str_anc="";
 $cn=Dossier::connect();
 // find out exercice
 $periode_id=new Periode($cn,$obj->det->jr_tech_per);
 $exercice=$periode_id->get_exercice();
$owner = new Noalyss_Parameter_Folder($cn);
//* @var $div (string) current DIV 
//@var $dossier_id (int) folder id 
$dossier_id=Dossier::id();

//@var $jr_id (int) jrn.jr_id
//@var $obj (Acc_Operation) current operation detail 

?>
<?php 
?>
<div class="content">

    <?php if ( $access=='W') : ?>
<form class="print" onsubmit="return op_save(this);">
   <?php endif; ?>

    <?php echo HtmlInput::hidden('whatdiv',$div).HtmlInput::hidden('jr_id',$jr_id).dossier::hidden();?>
  <table style="width:100%">
      <tr>
          <td>
            <table>
                <tr>
                    <td>
                        <?php
                        $date=new IDate('p_date');
                        if (  $g_parameter->MY_STRICT=='Y' || $g_user->check_action(UPDDATE)==0) {
                            $date->setReadOnly(true);
                        }
                        $date->value=format_date($obj->det->jr_date);
                         echo td(_('Date')).td($date->input());

                         ?>
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php 
                          $itext=new IText('lib');
                          $itext->value=strip_tags($obj->det->jr_comment??"");
                          $itext->size=40;
                          echo td(_('Libellé')).td($itext->input());


                        ?>
                    </td>
                </tr>
                 <tr>
                     <td>
                        <?php echo td(_('Montant')).td(nbm($obj->det->jr_montant),' class="inum"');?>
                     </td>
                 </tr>
                  <tr>
                      <td>
                        <?php 
                        $itext=new IText('npj');
                        if ($owner->MY_PJ_SUGGEST=='A' || $g_user->check_action(UPDRECEIPT)==0)
                            $itext->setReadOnly(true);
                        $itext->value=strip_tags($obj->det->jr_pj_number??"");
                        echo td(_('Pièce')).td($itext->input());
                        ?>
                    </td>
                  </tr>
            </table>
        </td>
                <td style="width:50%;height:100%;vertical-align:top;text-align: center">
                    <table style="width:99%;height:8rem;vertical-align:top;">
                        <tr style="height: 5%">
                            <td style="text-align:center;vertical-align: top">
                                  <?php
                                $inote = new ITextarea('jrn_note');
                                $inote->set_enrichText("minimal");
                                $inote->id="jrn_note{$div}";
                                $inote->style=' class="itextarea" style="width:90%;height:100%;"';
                                $inote->value = $obj->det->note_html;
                                $inote->heigh=200;
                                echo $inote->input();
                               
                                ?>
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

<div class="myfieldset">
<?php 
  $owner=new Noalyss_Parameter_Folder($cn);
?>
<table class="result">
<tr>
<?php 
    echo th(_('Poste Comptable'));
    echo th(_('Quick Code'));
    echo th(_('Libellé'));
    echo th(_('Débit'), 'style="text-align:right"');
    echo th(_('Crédit'), 'style="text-align:right"');
    if ( $obj->det->currency_id != 0 ) {
                        echo th(_("Devise"),' class="num" ');
    }
    
    if ($owner->MY_ANALYTIC != 'nu' /* && $div == 'popup' */ ){
      $anc=new Anc_Plan($cn);
      $a_anc=$anc->get_list(' order by pa_id ');
      $x=count($a_anc);
      /* set the width of the col */
       $str_anc.='<tr><th>Code</th><th>Poste</th><th>Montant</th><th colspan="' . $x . '">' . _('Compt. Analytique') . '</th>';

      /* add hidden variables pa[] to hold the value of pa_id */
      $str_anc.= Anc_Plan::hidden($a_anc);
    }
echo '</tr>';
$amount_idx=0; $sum_prod_currency=0;
  for ($e=0;$e<count($obj->det->array);$e++) {
    $row=''; $q=$obj->det->array;
    $view_history = HtmlInput::history_account($q[$e]['j_poste'], $q[$e]['j_poste'], "", $exercice);
    $row.=td($view_history);

    if ( $q[$e]['j_qcode'] !='') {
      $fiche=new Fiche($cn);
      $fiche->get_by_qcode($q[$e]['j_qcode']);
      $view_history=HtmlInput::history_card($fiche->id, $q[$e]['j_qcode'], "", $exercice);
    }
    else
      $view_history='';
    $row.=td($view_history);
	$l_lib = $q[$e]['j_text'] ;

    if ( $l_lib!='')
	{
	 $l_lib=$q[$e]['j_text'];
	}
      else  if ( $q[$e]['j_qcode'] !='') {
      // nom de la fiche
      $ff=new Fiche($cn);
      $ff->get_by_qcode( $q[$e]['j_qcode']);
      $l_lib=$ff->get_attribute(ATTR_DEF_NAME);
    } else {
      // libellé du compte
      $name=$cn->get_value('select pcm_lib from tmp_pcmn where pcm_val=$1',array($q[$e]['j_poste']));
      $l_lib=$name;
    }
    $l_lib=strip_tags($l_lib);
    if ($owner->MY_UPDLAB == 'Y')
    {
        $hidden = HtmlInput::hidden("j_id[]", $q[$e]['j_id']);
        $input = new IText("e_march" . $q[$e]['j_id'] . "_label", $l_lib);
        $input->css_size="100%";
    }
    else
    {
        $input = new ISpan("e_march" . $q[$e]['j_id'] . "_label");
		$input->value=$l_lib;
        $hidden = HtmlInput::hidden("j_id[]", $q[$e]['j_id']);
    }
     $row.=td($input->input().$hidden);
    $montant=td(nbm($q[$e]['j_montant']),'class="num"');
    $row.=($q[$e]['j_debit']=='t')?$montant:td('');
    $row.=($q[$e]['j_debit']=='f')?$montant:td('');
    /*
     * Compute total in currency if not default one
     */
    if ( $obj->det->currency_id != 0  && $q[$e]['j_debit']=='f' ) {
         $value=$obj->db->get_value("select  oc_amount+oc_vat_amount from operation_currency where j_id=$1",[$q[$e]['j_id']]);
         $sum_prod_currency=bcadd($sum_prod_currency,$value,2);

    }
    /* Analytic accountancy */
    if ( $owner->MY_ANALYTIC != "nu" /*&& $div=='popup'*/){
      if (  $g_parameter->match_analytic($q[$e]['j_poste'])) {

	echo HtmlInput::hidden("amount_t".$amount_idx,$q[$e]['j_montant']);
	$anc_op=new Anc_Operation($cn);
	$anc_op->j_id=$q[$e]['j_id'];
        $anc_op->in_div=$div;
        $side=($q[$e]['j_debit'] == 'f')?'C':'D';

        $str_anc.='<tr>';
	$str_anc.=HtmlInput::hidden('opanc[]',$anc_op->j_id);
        $str_anc.=td($q[$e]['j_qcode']);
        $str_anc.=td($q[$e]['j_poste']);
        $str_anc.=td(nbm($q[$e]['j_montant'])." {$side}");
	$str_anc.=$anc_op->display_table(1,$q[$e]['j_montant'],$div);
        $str_anc.='</tr>';
	$amount_idx++;
      }
    }
    $class=($e%2==0)?' class="even"':'class="odd"';
    if ( $obj->det->currency_id != 0 ) {
        $cur_amount=$cn->get_value("select oc_amount from operation_currency where j_id=$1",
                [$q[$e]['j_id']]);
        $row.=td(nbm($cur_amount,2),' class="num" ');
    }
    echo tr($row,$class);

  }
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
        echo _("Montant en devise"), "&nbsp;",nbm($sum_prod_currency,4).$four_space;    
        
    }
?>      
</div>
<?php 
require_once NOALYSS_TEMPLATE.'/ledger_detail_bottom.php';
?>
</div>
