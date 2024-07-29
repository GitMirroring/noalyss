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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 26/07/24
/*! 
 * \file
 * \brief display a form for detail of tax
 */
global $cn;
$http=new \HttpInput();
$ivatnumber = new ITva_Popup("vat_code");
$ivatnumber->value=$http->get("vat_code","string","");
$idatestart = new IDate("from");
$idateend = new IDate("to");

global $g_user, $http;

$a_limit = $g_user->get_limit_current_exercice();
$idatestart->value = $http->get('from', 'date', $a_limit[0]);
$idateend->value = $http->get('to', 'date', $a_limit[1]);


?>
<div class="content">
<p class="text-muted">
    Il peut y avoir des différences entre la TVA calculée et à récupérer à cause de TVA Non Déductible, reprise
    à charge du gérant ou d'arrondi.
</p>
    <form method="GET">
        <div class="form-inline">
            <label for="from">Début</label>
            <?= $idatestart->input(); ?>
            <label for="to">Jusque</label>
            <?= $idateend->input(); ?>

            <label for="vat_code">Code TVA</label>
            <?= $ivatnumber->input(); ?>
            <label for="p_jrn">Journal</label>
            <?php
            $a_ledger_purchase=$g_user->get_ledger('ACH',3);
            $a_ledger_sale=$g_user->get_ledger('VEN',3);
            $a_ledger=array_merge($a_ledger_sale??[],$a_ledger_purchase??[]);
            if ( DEBUGNOALYSS > 1 ) echo \Noalyss\Dbg::hidden_info("a_ledger",$a_ledger);
            $select_value=array();
            $select_value[]=array('value'=>-1,"label"=>'Tous vente et Achat');
            foreach($a_ledger as $i_ledger) :
                $select_value[]=array("value"=>$i_ledger["jrn_def_id"],"label"=>$i_ledger['jrn_def_name']);
            endforeach;
            $select=new \ISelect("p_jrn");
            $select->value=$select_value;
            $select->selected=$http->get("p_jrn","number",-1);
            echo $select->input();
            ?>
        </div>


        <ul class="aligned-block">
            <li>

            <?= HtmlInput::submit("display", _("Afficher")); ?>
            </li>
        </ul>
        <?php
        echo \HtmlInput::hidden("ac", $http->request("ac"));
        echo \Dossier::hidden();
        ?>
    </form>

</div>
<hr>