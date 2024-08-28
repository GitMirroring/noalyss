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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 27/07/24
/*! 
 * \file
 * \brief  button to export Tax_Detail in CSV
 */
$form_id=uniqid("export");
?>
<ul class="aligned-block">
    <li>

<form id="<?=$form_id?>" method="get" action="export.php" onsubmit="download_document_form('<?=$form_id?>')">
    <?php
    echo Dossier::hidden();
    echo \HtmlInput::hidden("act", "CSV:p1tva");
    echo \HtmlInput::hidden("from", $this->from);
    echo \HtmlInput::hidden("to", $this->to);
    echo \HtmlInput::hidden("vat_code", $this->tva_code);
    echo \HtmlInput::hidden("p_jrn", $this->ledger_id);
    echo \HtmlInput::submit("export_csv", _("Export CSV"));
    ?>
</form>
    </li>
</ul>
