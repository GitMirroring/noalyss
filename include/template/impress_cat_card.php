<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt
?><table>
<tr>
  <td><?php echo _('Categorie')?> </td>
  <td><?php echo $str_categorie?></td>
</tr>
<tr>
  <td><?php echo _('Type')?></td>
  <td><?php echo $str_histo?></td>
</tr>
<tr id="trstart">
  <td><?php echo _('Depuis')?></td>
  <td> <?php echo $str_start?></td>
</tr>
<tr id="trend">
  <td><?php echo _('Jusque')?></td>
  <td><?php echo $str_end?></td>
<tr id="allcard">
	<td><?php echo _('Pour toutes les catégories')?></td>
	<td><?php echo $str_icall?></td>
</tr>
<tr id="inactive_card">
        <td><?=_('Inclure les fiches inactives')?></td>
        <td><?=$str_inactive?></td>
</tr>
</table>
<script>
    id$('histo').addEventListener('click',function(evt) {
        let val = id$('histo').value;
        if ( val == -1 || val == 3) {
            id$('inactive_card').show();
        } else {
            id$('inactive_card').hide();
        }
    });
</script>
