<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
/**
 * @file
 * @brief Display a form to enter an operation Sale or Purchase 
 */

$default_currency=new Acc_Currency(Dossier::connect(),0);
\Noalyss\Dbg::echo_file(__FILE__);
?>
	<div id="jrn_name_div">
	<h1 id="jrn_name"> <?php echo $this->get_name()?></h1>
</div>
<table>
    <tr>
        <td> 
            <?php echo _('Journal')?>
        </td>
        <td>
			 <?php echo $f_jrn?>
        </td>
    </tr>
    <tr>
        <td> <?php echo _('Modèle opération') ?></td>
        <td>
            <?php echo $str_op_template;?>
        </td>
    </tr>
    <tr>
        <td>
            <?php echo _('Date')?> 
        </td>
        <td>
            <?php echo $f_date ?>
        </td>
    </tr>
    <tr>
        <td>
        <?php echo _('Echeance')?>  
        </td>
        <td>
            <?php echo $f_echeance?>
        </td>
    </tr>
    <tr>
        <td><?php echo $f_type?></td>
        <td>
            <?php echo $f_client_qcode?><?php echo $f_client_bt?><?php echo $str_add_button_tiers;?> <?php echo $f_client?>
        </td>
    </tr>
    
    <tr>
            <?php echo $f_periode?>
    </tr>
    <tr>
        <td>
            <?php echo _('Numéro Pièce')?> 
        </td>
        <td>
            <?php echo $f_pj?>
        </td>
    </tr>
    <tr>
        <td>
             <?php echo _('Libellé')?> 
             <?php echo $label ; ?>
        </td>
        <td>
            <?php echo $f_desc, Icon_Action::longer("e_comm",20)?>
        </td>
    </tr>
    <tr>
        <td>
            <?=_("Note")?>
            <?php echo Icon_Action::show_note('jrn_note_div') ?>
        </td>
        <td >
            <pre id="jrn_note_td"></pre>
        </td>
    </tr>

    <tr>
        <td>
            <?=_("Devise")?>
        </td>
        <td>
            <?=$currency_select->input()?>
           <?=$currency_input->change('CurrencyCompute(\'p_currency_rate\',\'p_currency_euro\');')?>

        </td>
    </tr>
</table>
<?php
// note for operation
$note=(isset($p_array['jrn_note_input']))?$p_array['jrn_note_input']:'';
Acc_Operation_Note::input($note)
?>
      
<br>
<?php
$hidden=($this->has_quantity()==0)?'d-none':'';
?>
<h2><?php echo $f_legend_detail?></h2>
<table id="sold_item" >
<tr>
<th style="width:auto"colspan="2">Code <?php echo Icon_Action::infobulle(0)?></th>
      <th class="visible_gt800 visible_gt1155"><?php echo _('Dénomination')?></th>
<?php if ($flag_tva =='Y') : ?>
      <th  class="text-center" ><?php echo _('prix/unité htva')?><?php echo Icon_Action::infobulle(6)?></th>
      <th class="text-center col_quant <?=$hidden?>"><?php echo _('quantité')?></th>
      <th class="text-center visible_gt800" ><?php echo _('Total HTVA')?></th>
	  <th><?php echo _('tva')?></th>
      <th class="text-center visible_gt800"><?php echo _('tot.tva')?></th>
      <th class="text-center "><?php echo _('tvac')?></th>
<?php else: ?>
	  <th class="text-center "><?php echo _('prix/unité ')?><?php echo Icon_Action::infobulle(6)?></th>
      <th class="text-center  col_quant <?=$hidden?>"><?php echo _('quantité')?></th>
      <th class="text-center "><?php echo _('Total ')?></th>
<?php endif;?>



</tr>
<?php foreach ($array as $item) {
echo '<tr>';
// echo "<td>";
echo $item['quick_code'];
// echo "</td>";
echo '<td>'.$item['bt'].$item['card_add'].'</td>';
?>
<td class="visible_gt800 visible_gt1155"><?php echo $item['denom'] ?></td>
<?php 
echo td($item['pu'],'class=" text-center"');
echo td($item['quantity' ],'class="col_quant '. $hidden.' text-center"');
echo td($item['htva'],' class="visible_gt800 text-center" ');
if ($flag_tva=='Y')  {
	echo td($item['tva']);
	echo td($item['amount_tva'].$item['hidden'],' class="visible_gt800 text-center" ');

}
echo td($item['tvac'],'class="text-center"');
echo '</tr>';
}

?>
<tfoot id="sum">
    <tr  class="highlight">
    <td> <?php echo _("Total")?>  
    <span id="currency_code"></span>
    </td>
    <td>   </td>
    <td class="visible_gt800 visible_gt1155"> </td>
    <td> </td>
    <td> </td>
    <td style="text-align:center" class="visible_gt800">  <span id="htva">0.0</span></td>
 <?php if ( $flag_tva=='Y' )  : ?>    
    <td> </td>
    <td style="text-align:center" class="visible_gt800">  <span id="tva">0.0</span> </td>
    <td style="text-align:center">  <span id="tvac" >0.0</span> </td>
  <?php    endif;     ?>  
    </tr>
    
    <tr id="row_currency" class="highlight" style="display:none">
    <td> <?php printf( _("Total %s"),$default_currency->get_code() );?>

    </td>
        <td class="num visible_gt800"></td>
<?php if ($flag_tva=='Y')  {?>
        <td style="text-align:center" class="visible_gt800 visible_gt1155"></td>
        <td></td>
        <td class="visible_gt800"></td>
<?php }         ?>
        <td></td>
        <td></td>
        <td></td>
        <td style="text-align:center">
            <span id="p_currency_euro"></span>
        </td>
    </tr>
</tfoot>
</table>

<?php echo HtmlInput::button('act',_('Actualiser'),'onClick="compute_all_ledger();"'); ?>


<script>
    if ($('p_currency_code').value != 0) {
        $('row_currency').show();
    }
    compute_all_ledger();
</script>  



