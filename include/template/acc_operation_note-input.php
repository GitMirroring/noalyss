<?php
/*
 *   This file is part of NOALYSS.
 *    NOALYSS is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    NOALYSS is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with NOALYSS; if not, write to the Free Software
 *    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *    Copyright Author Dany De Bontridder danydb@noalyss.eu  2002-2022
 */
/**
 * \brief display input textarea for operation note
 * \see Acc_Operation_Note
 */
//@var $note (itextarea);

$note=new ITextarea("jrn_note_input");
$note->set_enrichText("no-toolbar");
$note->heigh=130;
$note->value=$p_current;

?>

<div class="inner_box" style="display:none;width:40em" id="jrn_note_div">
<?=\HtmlInput::title_box(_("Note"),"jrn_note_div","hide")?>
<?=$note->input()?>

<ul class="aligned-block">
    <li>
        <button class="button" onclick="document.getElementById('jrn_note_td').update(tinyMCE.get('jrn_note_input').getContent());$('jrn_note_div').hide();return false;">
        <?=_("Fermer")?>
        </button>
    </li>

</ul>

</div>