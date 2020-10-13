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
$selbox=new Select_Box(uniqid(), _("Ajout action"));
$selbox->set_position("normal");
$selbox->set_filter("yes");
$aDocumentType=$cn->get_array("select dt_id,dt_value from document_type order by 2");
$nbDocumentType=count($aDocumentType);
$ac=$http->request("ac");
$dossier_id=Dossier::id();
$sup_parameter=HtmlInput::array_to_string(["sc","sb","f_id","qcode"], $_REQUEST,"&amp;");
for ($i=0;$i<$nbDocumentType;$i++) {
    $selbox->add_url($aDocumentType[$i]['dt_value'], 
            "do.php?".http_build_query([ "ac"=>$ac,"gDossier"=>$dossier_id,"sa"=>"add_action"
                    ,"action_type"=>$aDocumentType[$i]["dt_id"]]).$sup_parameter);
    
}
?>
<div class="content" style="display:inline" >
	<div style="display:inline">
            <input id="bt_search" type="button" class="smallbutton" onclick="$('search_action').style.display='block'" value="<?php echo _('Recherche') ?>">
            <?=$selbox->input();?>
         
	</div>
</div>    