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
//var @a_error (array) contains error

if ( count($a_error)  == 0) return;

$error_message=new \Noalyss\XMLDocument\Error_Message($a_error);
var_dump($a_error);
?>
<div  class="notice"  >
    <h3><?=_("Société")?></h3>
    <p class="text-muted">
        <?=_("A corriger dans COMPANY")?>
    </p>
    <?php
//----------------------------------------------------------------------------
// company
//----------------------------------------------------------------------------
        $nb_error=count($a_error['company']);
        for ($i=0;$i<$nb_error;$i++):
    ?>
    <div class="notice-item">
        <?=$error_message->get_message_error(code:$a_error['company'][$i],type:'company')?>
    </div>
    
    <?php
    endfor;
    ?>
     <h3><?=_("Client")?></h3>
    <p class="text-muted">
        <?=_("A corriger dans la fiche")?>
    </p>
    <?php
//----------------------------------------------------------------------------
// Customer
//----------------------------------------------------------------------------
        $nb_error=count($a_error['customer']);
        for ($i=0;$i<$nb_error;$i++):
    ?>
    <div class="notice-item">
        <?=$error_message->get_message_error(code:$a_error['customer'][$i],type:'customer')?>
    </div>
    
    <?php
    endfor;
    ?>
      <h3><?=_("Opération")?></h3>
    <p class="text-muted">
        <?=_("A corriger dans l'opération")?>
    </p>
    <?php
     if ( ! isset($this->data['due_date']) || $this->data['due_date']==""):
    ?>
    <div class="notice-item">
        <?=_("Date échéance nécessaire")?>
    </div>
    <?php
         
     endif;
//----------------------------------------------------------------------------
// Item VAT
//----------------------------------------------------------------------------
        $a_vat_error=$this->check_VAT();
        $nb_error=count($a_vat_error);
        for ($i=0;$i<$nb_error;$i++):
    ?>
    <div class="notice-item">
        <?=$a_vat_error[$i]?>
    </div>
    
    <?php
    endfor;
    ?>
</div>