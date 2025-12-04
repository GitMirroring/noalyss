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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 17/04/23
/*! 
 * \file
 * \brief main display for situation
 */
$http=new \HttpInput();
$Operation=new Follow_Up($cn);
$last_operation=$Operation->get_today();
$late_operation=$Operation->get_late();

$Ledger=new Acc_Ledger($cn,0);

// Supplier late and now
$supplier_now=$Ledger->get_supplier_now();
$supplier_late=$Ledger->get_supplier_late();

// Customer late and now
$customer_now=$Ledger->get_customer_now();
$customer_late=$Ledger->get_customer_late();

$gDossier_id=Dossier::id();

// var refresh : string javascript code to refresh this widget
$refresh=sprintf("event_display_main('%s')",Dossier::id());

$nb_last_operation=count($last_operation);
$nb_late_operation=count($late_operation);
$nb_supplier_now=count($supplier_now);
$nb_supplier_late=count($supplier_late);
$nb_customer_now=count($customer_now);
$nb_customer_late=count($customer_late);

?>
  <?php echo HtmlInput::title_box(_("Résumé"),"situation_div",'none','','n','','',$refresh)?>
<div id="content">
    <div id="so_event_main_id">
        <div class="sect1">
            <h2> <?=_("Action")?></h2>
            <div class="sect1_today <?=($nb_last_operation>0)?"highlight":""?> ">
                <h3>
                       <?php echo date('d.m.y'); ?>
                </h3>
                
                <?php if ($nb_last_operation>0): ?>
                <A class="mtitle" style="font-weight: bolder;"onclick="event_display_detail(<?=$gDossier_id?>,'action_now')">
                        <span class="notice">
                        <?php echo count($last_operation) ?>
                        &nbsp;<?php echo _("détail"); ?>
                        </span>
                </A>
                <?php else: ?>
                 0
                <?php endif; ?>
            </div>
            <div class="sect1_late  <?=($nb_late_operation>0)?"highlight":""?>">
                 <h3>
                      <?php echo _('En retard') ?>
                </h3>
                <?php if ($nb_late_operation >0): ?>
				<A class="mtitle"  style="font-weight: bolder" onclick="event_display_detail(<?=$gDossier_id?>,'action_late')">
				<span class="notice"><?php echo count($late_operation) ?>
					&nbsp;<?php echo _("détail"); ?>
                                </span>
				</A>
			<?php else: ?>
				 0
			<?php endif; ?>
            </div>
        </div>
        <div class="sect1">
            <h2>
                <?php echo _("Paiement fournisseur"); ?>
            </h2>
                
            <div class="sect1_today  <?=($nb_supplier_now>0)?"highlight":""?>">
                <h3>
                       <?php echo date('d.m.y'); ?>
                </h3>
                <?php if ($nb_supplier_now >0): ?>
				<A class="mtitle"  style="font-weight: bolder" onclick="event_display_detail(<?=$gDossier_id?>,'supplier_now')">
				<span class="notice"><?php echo count($supplier_now) ?>&nbsp;<?php echo _("détail"); ?></span>

				</A>
			<?php else: ?>
				 0
			<?php endif; ?>
            </div>
             <div class="sect1_late  <?=($nb_supplier_late>0)?"highlight":""?>">
                 <h3>
                      <?php echo _('En retard') ?>
                </h3>
                 <?php if ( $nb_supplier_late >0): ?>
				<A class="mtitle"  style="font-weight: bolder" onclick="event_display_detail(<?=$gDossier_id?>,'supplier_late')">
				<span class="notice"><?php echo count($supplier_late) ?>&nbsp;<?php echo _("détail"); ?></span>

				</A>
			<?php else: ?>
				 0
			<?php endif; ?>
             </div>
        </div>
        
         <div class="sect1">
            <h2>
                <?php echo _("Paiement client"); ?>
            </h2>
                
            <div class="sect1_today  <?=($nb_customer_now>0)?"highlight":""?>">
                <h3>
                       <?php echo date('d.m.y'); ?>
                </h3>
                <?php if ($nb_customer_now>0): ?>
				<A class="mtitle"  style="font-weight: bolder" onclick="event_display_detail(<?=$gDossier_id?>,'customer_now')">
				<span class="notice"><?php echo count($customer_now) ?>&nbsp;<?php echo _("détail"); ?></span>

				</A>
			<?php else: ?>
				 0
			<?php endif; ?>
            </div>
             <div class="sect1_late  <?=($nb_customer_late>0)?"highlight":""?>">
                 <h3>
                      <?php echo _('En retard') ?>
                </h3>
                 <?php if ($nb_customer_late >0): ?>
				<A class="mtitle"  style="font-weight: bolder" onclick="event_display_detail(<?=$gDossier_id?>,'customer_late')">
				<span class="notice"><?php echo count($customer_late) ?>&nbsp;<?php echo _("détail"); ?></span>

				</A>
			<?php else: ?>
				 0
			<?php endif; ?>
             </div>
        </div>
        
    </div>
    
    
</div>
<ul class="aligned-block">
	<li>
		<?=\HtmlInput::button_action(_("Rafraîchir"),sprintf("event_display_main('%s')",Dossier::id()),uniqid(),"smallbutton")?>
	</li>

</ul>
