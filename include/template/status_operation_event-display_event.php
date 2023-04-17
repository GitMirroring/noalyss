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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 14/04/23
/*! 
 * \file
 * \brief Display event
 */
 ?>

	<?php
		echo HtmlInput::title_box($p_title, $this->dialog_box_id,"close","","y")
	?>
	<ol>
	<?php if (count($p_array)> 0) :

	for($i=0;$i<count($p_array);$i++):
	?>
	<li>
	<span>
	<?php echo smaller_date($p_array[$i]['ag_timestamp_fmt']) , " ",
                hb($p_array[$i]['ag_hour']);
                ?>
	</span>
		<?php echo HtmlInput::detail_action($p_array[$i]['ag_id'],h($p_array[$i]['ag_ref']))?>
		<span  style="font-weight: bolder ">
			<?php echo h($p_array[$i]['vw_name'])?>
		</span>
	<span>
	<?php echo h(mb_substr($p_array[$i]['ag_title'],0,50,'UTF-8'))?>
	</span>
	<span style="font-style: italic">
	<?php echo $p_array[$i]['dt_value']?>
	</span>
	</li>
	<?php endfor;?>
	</ol>
	<?php else : ?>
	<h2 class='notice'><?php echo _("Aucune action en retard")?></h2>
	<?php endif; ?>
	<ul class="aligned-block">
		<li>
			<?=\HtmlInput::button_action(_("Rafraîchir"),sprintf("event_display_detail('%s','%s')",Dossier::id(),$p_what),uniqid(),"smallbutton")?>
		</li>
		<li>
			<?php echo HtmlInput::button_close($this->dialog_box_id);?>
		</li>
	</ul>

</div>

