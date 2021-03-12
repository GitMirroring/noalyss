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
require_once NOALYSS_INCLUDE."/lib/select_box.class.php";
$http=new HttpInput();
// Create select box for new Action
Follow_Up::show_action_add(array("sa"=>"add_action"));
?>
<div class="content" style="display:inline" >
	<div style="display:inline">
            <input id="bt_search" type="button" class="smallbutton" onclick="$('search_action').style.display='block'" value="<?php echo _('Recherche') ?>">
         
	</div>
</div>  