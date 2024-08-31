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
// Copyright Author Dany De Bontridder danydb@noalyss.eu 28/07/24
/*! 
 * \file
 * \brief manage Tax_Detail
 */

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');
$http=new HttpInput();
try {
    $tax_detail=new Tax_Detail(
        $http->get('tva_id')
        ,$http->get("date_from",'date')
        ,$http->get("date_to",'date')
        ,$http->get("ledger_id",'number')
    );
    $boxid=$http->get("boxid");

} catch (\Exception $e) {
    echo $e->getMessage();
    return;
}
echo \HtmlInput::title_box("Détail TVA", $boxid);
?>
<div>
    <p class="text-muted">
        Il peut y avoir des différences entre la TVA calculée et à récupérer à cause de TVA Non Déductible, reprise
        à charge du gérant ou d'arrondi.
    </p>
    <?php
    $tax_detail->html();
    ?>
</div>
<ul class="aligned-block">
    <li>
        <?=\HtmlInput::button_close($boxid)?>
    </li>
    <li>
        <?=$tax_detail->button_export_csv()?>
    </li>
</ul>

