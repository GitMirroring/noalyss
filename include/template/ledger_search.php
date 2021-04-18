<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
?>
<table id="<?=$this->div?>table_search">
    
<tr>
<td style="text-align:right;width:30em">
<?php echo _('Dans le journal')?>
</td>
<td>
   <?php echo $f_ledger; ?>
    <span id="ledger_id<?php echo $this->div;?>">
        <?php
        echo $hid_jrn;
        ?>
    </span>
</td>
</tr>

<tr>
<td style="text-align:right;width:30em">
<?php echo _('Et Compris entre les date')?>
</td>
<td>
<?php echo $f_date_start->input();  ?> <?php echo _('et')?> <?php echo $f_date_end->input();  ?>

</td>
</tr>
<tr>
<td style="text-align:right;width:30em">
<?php echo _('Et paiement compris entre les date ').Icon_Action::infobulle(36); ?>
</td>
<td>
<?php echo $f_date_paid_start->input();  ?> <?php echo _('et')?> <?php echo $f_date_paid_end->input();  ?>
<?php echo $date_start_hidden,$date_end_hidden;?>
</td>
</tr>

<tr>
<td style="text-align:right;width:30em">
<?php echo _('Et contenant dans le libellé, pièce justificative ou n° interne')?>
</td>

<td>
<?php echo $f_descript->input(); ?>
</td>
</tr>
<tr>
<td style="text-align:right;width:30em">
<?php echo _('Et compris entre les montants')?>
</td>
<td >
    <?php echo Icon_Action::clean_zone(uniqid(),sprintf("$('%s').value=0",$f_amount_min->id));
          echo $f_amount_min->input();  ?>
     <?php echo _('et')?> <?php
       echo HtmlInput::button_action("=",sprintf("$('%s').value=%s.value",$f_amount_max->id,$f_amount_min->id) ,uniqid(), "smallbutton");
       echo $f_amount_max->input(); ; ?>
</td>
</tr>
<tr>
<td style="text-align:right;width:30em">
	<?php echo _('Et utilisant la fiche (quick code)')?>
</td>
<td>
   <?php echo $f_qcode->input(); echo $f_qcode->search(); ?>
</td>
</tr>
<tr>
<td style="text-align:right;width:30em">
	<?php echo _('Et utilisant le poste comptable').$info?>
</td>

<td>
<?php
 echo Icon_Action::clean_zone(uniqid(),sprintf("$('%s').value=''",$f_accounting->id));
echo $f_accounting->input();  ?>
</td>
</tr>

<tr>
<td style="text-align:right;width:30em">
	<?php echo _('Etat')?>
</td>

<td>
<?php echo $f_paid->input();  ?>
</td>
</tr>

<tr>
<td style="text-align:right;width:30em">
	<?php echo _('Devise')?>
</td>

<td>
<?php echo $sCurrency->input();  ?>
</td>
</tr>

<tr>
    
<td  style="text-align:right;width:30em">
    
        <?php
         $iselect= new ISelect($this->div."tag_option");
                $iselect->value=array(
                    array("value"=>0,"label"=>_("Toutes les étiquettes")),
                    array("value"=>1,"label"=>_("Au moins une étiquette"))
                    );
                
                $iselect->set_value($http->request($this->div."tag_option","number",0));
                echo $iselect->input(); 
               
        ?>
        <?php
        echo Tag_Operation::select_tag_search($this->div);
        ?>
</td>
    <td >
        <span id="<?=$this->div?>tag_choose_td">
            <?php 
            $aTag= $http->request($this->div."tag","string",0);
                if (is_array($aTag) ) {
                    $nb_tag=count($aTag);
                    for ($j=0;$j< $nb_tag;$j++) {
                        $tag_operation=new Tag_Operation($this->cn,$aTag[$j]);
                        $tag_operation->update_search_cell($this->div);
                    }
                }
            ?>
        
        </span>
    </td>
</tr>
    
</table>
