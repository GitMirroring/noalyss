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
/** \file
 * \brief included file for customizing with the vat (account,rate...)
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

$cn=Dossier::connect();
$own=new Noalyss_Parameter_Folder($cn);

echo '<div class="content">';
if ($own->MY_TVA_USE == 'N')
{
    echo '<h2 class="error">'._("Vous n'êtes pas assujetti à la TVA").'</h2>';
    return;
}

$tva_rate=new V_Tva_Rate_SQL($cn);

$manage_table=new Tva_Rate_MTable($tva_rate);

$manage_table->set_callback("ajax_misc.php");
$manage_table->add_json_param("op", "tva_parameter");
$manage_table->create_js_script();
$manage_table->display_table();
echo '</div>';
?>
<div class="row">
    <div class="col-3 offset-3" >
        <p class="notice" style="padding: 1rem">
            <span class="font-weight-bold">Attention :</span> ajouter les codes TVA nécessaires à votre déclaration TVA et adapter en fonction
        les rapports avancés ou le module de TVA.
        </p>
    </div>
</div>
<script>
    <?=$manage_table->get_object_name()?>.afterSaveFct=function(p_param,p_xmltext) {

    try {

        var xml = p_xmltext.responseXML;
        var old_tva_id=getNodeText(xml.getElementsByTagName("previous_id")[0]);
        if (old_tva_id != p_param.getAttribute("ctl_pk_id")) {
            var ctl_row=getNodeText(xml.getElementsByTagName("ctl")[0])+"_"+old_tva_id;
            $(ctl_row).remove();
        }

    } catch (e) {
        console.error(e.message);
    }
}
</script>
