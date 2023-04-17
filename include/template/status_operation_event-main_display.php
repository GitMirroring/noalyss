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
?>
  <?php echo HtmlInput::title_box(_("Situation"),"situation_div",'none','','n')?>
    <table class='result'>
		<tr>
			<th>

			</th>
			<th>
                            <?php echo date('d.m.y'); ?>
			</th>
                        <th>
                            <?php echo _('En retard') ?>
                        </th>
		</tr>
		<tr>
			<td>
				<?php echo _("Action"); ?>
			</td>
			<td>
				<?php if (count($last_operation)>0): ?>
				<A class="mtitle" style="font-weight: bolder;"onclick="event_display_detail(<?=$gDossier_id?>,'action_now')">
					<span class="notice">
					<?php echo count($last_operation) ?>
					&nbsp;<?php echo _("détail"); ?>
					</span>
				</A>
			<?php else: ?>
				 0
			<?php endif; ?>
			</td>

			<td >
			<?php if (count($late_operation)>0): ?>
				<A class="mtitle"  style="font-weight: bolder" onclick="event_display_detail(<?=$gDossier_id?>,'action_late')">
				<span class="notice"><?php echo count($late_operation) ?>
					&nbsp;<?php echo _("détail"); ?>
                                </span>
				</A>
			<?php else: ?>
				 0
			<?php endif; ?>
			</td>

		</tr>
		<tr>
			<td>
				<?php echo _("Paiement fournisseur"); ?>
			</td>
			<td >
			<?php if (count($supplier_now)>0): ?>
				<A class="mtitle"  style="font-weight: bolder" onclick="event_display_detail(<?=$gDossier_id?>,'supplier_now')">
				<span class="notice"><?php echo count($supplier_now) ?>&nbsp;<?php echo _("détail"); ?></span>

				</A>
			<?php else: ?>
				 0
			<?php endif; ?>
			</td>
			<td >
			<?php if (count($supplier_late)>0): ?>
				<A class="mtitle"  style="font-weight: bolder" onclick="event_display_detail(<?=$gDossier_id?>,'supplier_late')">
				<span class="notice"><?php echo count($supplier_late) ?>&nbsp;<?php echo _("détail"); ?></span>

				</A>
			<?php else: ?>
				 0
			<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo _("Paiement client"); ?>
			</td>
			<td>
				<?php if (count($customer_now)>0): ?>
				<A class="mtitle"  style="font-weight: bolder" onclick="event_display_detail(<?=$gDossier_id?>,'customer_now')">
				<span class="notice"><?php echo count($customer_now) ?>&nbsp;<?php echo _("détail"); ?></span>

				</A>
			<?php else: ?>
				 0
			<?php endif; ?>
			</td>
			<td>
				<?php if (count($customer_late)>0): ?>
				<A class="mtitle"  style="font-weight: bolder" onclick="event_display_detail(<?=$gDossier_id?>,'customer_late')">
				<span class="notice"><?php echo count($customer_late) ?>&nbsp;<?php echo _("détail"); ?></span>

				</A>
			<?php else: ?>
				 0
			<?php endif; ?>
			</td>
		</tr>
	</table>
<ul class="aligned-block">
	<li>
		<?=\HtmlInput::button_action(_("Rafraîchir"),sprintf("event_display_main('%s')",Dossier::id()),uniqid(),"smallbutton")?>
	</li>

</ul>
