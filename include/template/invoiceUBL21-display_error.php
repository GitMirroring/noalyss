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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file
 * @brief display errors for generating e-invoices, called from 
 * invoiceUBL21-display-error.php
 */
///@var $a_vat_error (array) contains error for VAT
$a_vat_error=$this->check_VAT();

///@var $total_error (int) total of errors found in e-invoice
$total_error=count ($a_error['general'])
                + count($a_error['operation']) 
                + count($a_error['customer'])
                + count($a_error['company']) 
                +count($a_vat_error);

if ( $total_error == 0 ) :
        return;
endif;
$error_message=new \Noalyss\XMLDocument\Error_Message($a_error);
?>
<button onclick="$('invoice_error_popover').show();return false" class="button bt-error "><i class="icon-attention"></i> <?=_("Erreurs Facture électronique {$total_error}")?></button>
<div  style="display:none" id="invoice_error_popover">
    <?php
    echo HtmlInput::title_box(_("Erreurs"), "invoice_error_popover","hide");
//----------------------------------------------------------------------------
// company
//----------------------------------------------------------------------------
        $nb_error=count($a_error['company']);
        for ($i=0;$i<$nb_error;$i++):
    ?>
    
    <?php if ($i == 0 ):?>
    <h3><?=_("Société")?></h3>
    <p class="text-muted">
        <?=_("A corriger dans COMPANY")?>
    </p>
    <ol>
    <?php endif;?>
    <li class="notice-item">
        <?=$error_message->get_message_error(code:$a_error['company'][$i],type:'company')?>
    </li>
    
    <?php
    endfor;
     if ( $nb_error!=0) print '</ol>';
    ?>
      <?php
//----------------------------------------------------------------------------
// Customer
//----------------------------------------------------------------------------
        $nb_error=count($a_error['customer']);
        for ($i=0;$i<$nb_error;$i++):
    ?>
    <?php  if ($i == 0) :?>
     <h3><?=_("Client")?></h3>
    <p class="text-muted">
        <?=_("A corriger dans la fiche")?>
    </p>
    <p>
        <?php 
        $card=new \Fiche ($this->cn,$this->data['customer']['card_id']);
        echo \HtmlInput::card_detail($card->get_attribute(ATTR_DEF_QUICKCODE)
                ,$card->get_attribute(ATTR_DEF_NAME));
        ?>
    </p>
    <ol>
    <?php endif;?>
    
    <li class="notice-item">
        <?=$error_message->get_message_error(code:$a_error['customer'][$i],type:'customer')?>
    </li>
    
    <?php
    endfor;
    if ( $nb_error!=0) print '</ol>';
    ?>
    <?php
   //----------------------------------------------------------------------------
// Item VAT
//----------------------------------------------------------------------------
        $a_vat_error=$this->check_VAT();
        $nb_error=count($a_vat_error);
        for ($i=0;$i<$nb_error;$i++):
    ?>
    <?php  if ($i == 0) :?>
      <h3><?=_("TVA")?></h3>
    <p class="text-muted">
        <?=_("A corriger dans la configuration TVA (C0TVA)")?>
    </p>
    <ol>
    <?php endif;?>
    <li class="notice-item">
        <?=$a_vat_error[$i]?>
    </li>
    
    <?php
    endfor;
     if ( $nb_error!=0) print '</ol>';
    ?>
    <button onclick="$('invoice_error_popover').hide();return false" class="button"><?=_("Fermer")?></button>

</div>