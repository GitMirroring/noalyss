<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
?><?php
require_once  NOALYSS_INCLUDE.'/class/acc_operation.class.php';
require_once  NOALYSS_INCLUDE.'/class/acc_reconciliation.class.php';
$amount_deb=0;$amount_cred=0;
$gDossier=dossier::id();
global $g_failed;

if ( count($this->content) == 0 ) :
?>
  <h2 class="error"><?php echo _('Désolé aucun résultat trouvé')?></h2>

<?php exit();
  endif;?>
  <table class="result">
<tr>
<th>
   <?php echo _('Lettrage')?>
</th>
<th>
   <?php echo _('Date')?>
</th>
<th>
   <?php echo _('Ref')?>
</th>
<th>
   <?php echo _('Interne')?>
</th>
<th>
   <?php echo _('Description')?>
</th>
<th style="text-align:right">
   <?php echo _('Débit')?>
</th>
<th style="text-align:right">
   <?php echo _('Crédit')?>
</th>
<th style="text-align:center">
  <?php echo _('Op. concernée')?>
</th>
</tr>

<?php
for ($i=0;$i<count($this->content);$i++):
  $class="";
$class= ( ($i % 2) == 0 ) ? "odd":"even";
?>
  <tr <?php echo "class=\"$class\""; ?> >
<td>
<?php
$letter=($this->content[$i]['letter']==-1)?_("aucun lettrage"):strtoupper(base_convert($this->content[$i]['letter'],10,36));

$object=sprintf('{ gDossier : %s , j_id : %s , obj_type:\'%s\',search_start:\'%s\',search_end:\'%s\',op:\'dl\'} ',
        $gDossier, 
        $this->content[$i]['j_id'], 
        $this->object_type,
        $this->get_parameter('start'),
        $this->get_parameter('end')
        );

$js=sprintf("dsp_letter(%s)",$object);

?>
<A class="detail" style="text-decoration: underline" onclick="<?php echo $js?>"><?php echo $letter?>
<?php if ( $this->content[$i]['letter_diff'] != 0) echo $g_failed;	?>
	</A>
</td>
<td> <?php echo   smaller_date($this->content[$i]['j_date_fmt'])?> </td>
<td> <?php echo $this->content[$i]['jr_pj_number']?> </td>

<?php
$r=sprintf('<A class="detail" style="text-decoration:underline"  href="javascript:void(0)" onclick="viewOperation(\'%s\',\'%s\')" >%s</A>',
	     $this->content[$i]['jr_id'], $gDossier, $this->content[$i]['jr_internal']);
?>
  <td> <?php echo $r?> </td>
  <td> <?php echo h($this->content[$i]['jr_comment'])?> </td>
  <?php if ($this->content[$i]['j_debit']=='t') : ?>
  <td style="text-align:right"> <?php echo nbm($this->content[$i]['j_montant'])?> </td>
  <td></td>
  <?php else : ?>
  <td></td>
  <td style="text-align:right"> <?php echo nbm($this->content[$i]['j_montant'])?> </td>
  <?php endif ?>
<td style="text-align:center">
<?php
    // Rapprochement
    $rec=new Acc_Reconciliation($this->db);
    $rec->set_jr_id($this->content[$i]['jr_id']);
    $a=$rec->get();
    if ( $a != null ) {
      foreach ($a as $key => $element)
      {
	$operation=new Acc_Operation($this->db);
	$operation->jr_id=$element;
	$l_amount=$this->db->get_value("select jr_montant from jrn ".
					 " where jr_id=$element");
	echo "<A class=\"detail\"  href=\"javascript:void(0)\" onclick=\"viewOperation('".$element."',".$gDossier.")\" > ".$operation->get_internal()." [ ".nb($l_amount)." &euro; ]</A>";
      }//for
    }// if ( $a != null ) {
// compute amount
$amount_deb+=($this->content[$i]['j_debit']=='t')?$this->content[$i]['j_montant']:0;
$amount_cred+=($this->content[$i]['j_debit']=='f')?$this->content[$i]['j_montant']:0;

?>
</td>
</tr>

<?php
    endfor;
?>
<tr class="highlight">
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td class="num"> <?php echo nbm($amount_deb,2);?> </td>
    <td class="num"> <?php echo nbm($amount_cred,2);?> </td>
    <td class="num">
        <?php 
        bcscale(2);
        $solde=bcsub($amount_deb,$amount_cred);
        if ( $solde > 0 )  
            printf (_("Solde débiteur : %s"),nbm($solde));
        elseif ($solde < 0)  
            printf (_("Solde créditeur : %s"),nbm(abs($solde)));

                
        ?>

    </td>
</table>
