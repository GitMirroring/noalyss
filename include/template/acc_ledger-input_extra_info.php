<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
if (DEBUGNOALYSS>1) { echo __FILE__;}
/**
 * @file
 * @brief display supplemental information to save when entering an operation of 
 * sale, purchase, financial or for a misc. operation
 */
$http=new HttpInput();
$ledger_type = $this->get_type();
$a_show=[];
$a_div=["template"=>"modele_div_id",
    "repo"=>"repo_div_id" ,
    "invoice"=>"facturation_div_id",
    "reverse"=>"reverse_div_id",
    "type_operation"=>'operationtype_div_id',
    "document"=>"document_div_id"];

//-- Define option to display 
// template : template of operation , invoice : doc. to generate, document : document
// to upload , reverse : reverse operation
 if ( $ledger_type  == 'ACH' || $ledger_type == 'VEN') {
     $a_show=['template','repo','invoice','reverse'];
     $show="facturation_div_id";
 } elseif ($ledger_type=='ODS') {
     $a_show=['template','document','reverse','type_operation'];
     $show="document_div_id";
 }
 
 $str_tab = "";$sep="";
 foreach ($a_show as $tab) { $str_tab .=$sep. "'".$a_div[$tab]."'";$sep =",";}
?>
<div id="tab_id" >
    <script>
        var a_tab = [ <?=$str_tab?>]; 
        console.table(a_tab);
    </script>
    
<ul class="tabs">
    <?php if (in_array('invoice',$a_show))	 :?>
    <li class="tabs_selected" style="float: none"><a href="javascript:void(0)" title="<?php echo _("Générer une facture ou charger un document")?>"  onclick="unselect_other_tab(this.parentNode.parentNode);this.parentNode.className='tabs_selected';show_tabs(a_tab,'facturation_div_id')"><?php echo _('Facture')?></a></li>
    <?php endif; ?>
    
    <?php if (in_array('document',$a_show))	 :?>
    <li class="tabs" style="float: none"> <a href="javascript:void(0)" title="<?php echo _("Document")?>"  onclick="unselect_other_tab(this.parentNode.parentNode);this.parentNode.className='tabs_selected';show_tabs(a_tab,'document_div_id')"> <?php echo _('Document')?> </a></li>
    <?php endif; ?>
    
    <?php if (in_array('repo',$a_show))	 :?>
    <li class="tabs" style="float: none"> <a href="javascript:void(0)" title="<?php echo _("Choix du dépôt")?>"  onclick="unselect_other_tab(this.parentNode.parentNode);this.parentNode.className='tabs_selected';show_tabs(a_tab,'repo_div_id')"> <?php echo _('Dépôt')?> </a></li>
    <?php endif; ?>
    
    <?php if (in_array('template',$a_show))	 :?>
    <li class="tabs" style="float: none"> <a href="javascript:void(0)" title="<?php echo _("Modèle à sauver")?>"  onclick="unselect_other_tab(this.parentNode.parentNode);this.parentNode.className='tabs_selected';show_tabs(a_tab,'modele_div_id')"> <?php echo _('Modèle')?> </a></li>
    <?php endif; ?>
    
    <?php if (in_array('reverse',$a_show))	 :?>
    <li class="tabs" style="float: none"> <a href="javascript:void(0)" title="<?php echo _("Extourne")?>"  onclick="unselect_other_tab(this.parentNode.parentNode);this.parentNode.className='tabs_selected';show_tabs(a_tab,'reverse_div_id')"> <?php echo _('Extourne')?> </a></li>
    <?php endif; ?>
    
    <?php if (in_array('type_operation',$a_show))	 :?>
        <li class="tabs" style="float: none"> <a href="javascript:void(0)" title="<?php echo _("Type opération")?>"  onclick="unselect_other_tab(this.parentNode.parentNode);this.parentNode.className='tabs_selected';show_tabs(a_tab,'operationtype_div_id')"> <?php echo _('Type opération')?> </a></li>
    <?php endif; ?>
</ul>
</div>
<?php
if (in_array('repo',$a_show))	 {
    echo $this->select_depot(false, -1); 
}
if (in_array('invoice',$a_show)) {
    echo $this->extra_info();
}
?>
<div id="document_div_id" style="display:none;height:185px;height:10rem">
  <?php
     $file = new IFile();
    $file->setAlertOnSize(true);
    $file->table = 0;
    echo '<p class="decale">';
    echo _("Ajoutez une pièce justificative ");
    echo $file->input("pj", "");
    echo '</p>';
    ?>
</div>
<div id="modele_div_id" style="display:none;height:185px;height:10rem">
<?php                echo Pre_operation::save_propose();?>
</div>

               
<div id="reverse_div_id" style="display:none;height:185px;height:10rem">
<?php
    $reverse_date=new IDate('reverse_date');
    $reverse_ck=new ICheckBox('reverse_ck');
    echo _('Extourne opération')." ".$reverse_ck->input()." ";
    echo $reverse_date->input();
    $msg_reverse=new IText("ext_label");
    $msg_reverse->placeholder=_("Message extourne");
    $msg_reverse->size=60;
    echo _("Message")." ".$msg_reverse->input();
?>    
</div>

<div id="operationtype_div_id" style="display:none;height:185px;height:10rem">
        <?php
            $status=$http->request("jr_optype","string","NOR");
            echo Acc_Operation::select_operation_type($status)->input();
        ?>
</div>
               
		
		
                
<script>
    show_tabs(a_tab,'<?=$show?>');
</script>
