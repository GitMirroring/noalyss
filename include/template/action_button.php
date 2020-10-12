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
/* $Revision$ */

// Copyright Author Dany De Bontridder danydb@aevalys.eu

/**
 * @file
 * @brief show button in the list of actions
 *
 */
$http=new HttpInput();
?>
<div class="content" style="display:inline" >
	<div style="display:inline">
            <input id="bt_search" type="button" class="smallbutton" onclick="$('search_action').style.display='block'" value="<?php echo _('Recherche') ?>">
            <input id="bt_add" type="button" class="smallbutton" onclick="$('b_add_action').style.display='block';document.getElementById('action_type').focus()" value="<?php echo _('Ajout') ?>">
            <div id="b_add_action" style="display:none" class="inner_box">
                <?php echo HtmlInput::title_box(_("Ajout d'une action"), "b_add_action","hide");?>
		<form  method="get" style="display:inline" action="do.php">
			<?php echo dossier::hidden();
                        $a_typeAction=new ISelect("action_type");
                        $a_typeAction->rowsize=10;
                        $a_typeAction->value=$cn->make_array("select dt_id,dt_value from document_type order by 2");
                        echo _("Type action ");
                        echo $a_typeAction->input();
			?>
                        
			<input type="hidden" name="ac" value="<?php echo  $http->request('ac')?>">
			<input type="hidden" name="sa" value="add_action">
			<?php echo  $supl_hidden?>
                    <ul class="aligned-block">
                        <li>
                            
			<input type="submit" class="smallbutton" name="submit_query" value="<?php echo  _("Ajout Action")?>">
                        </li>
                        <li>
                            <?php echo HtmlInput::button_hide("b_add_action");?>
                        </li>
                    </ul>

                        

		</form>
            </div>
	</div>
</div>    