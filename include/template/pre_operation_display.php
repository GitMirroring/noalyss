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

// Copyright Author Dany De Bontridder danydb@aevalys.eu

/**
 * @file
 * display pre_operation value
 */

$name=new IText("opd_name",$array["e_comm"]);
$description=new IText("od_description",$array["od_description"]);
$description->style=' class="itextarea" style="width:30em;height:4em;vertical-align:top"';

?>
<?php echo _("Nom du modèle"),$name->input();?>
<p>
    <?php echo  _('Description (max 50 car.)'); ?>
    <?php echo $description->input();?>
</p>
<p>
    <?php echo _("Journal") ?>
    <?php echo $select_ledger->input(); ?>
</p>