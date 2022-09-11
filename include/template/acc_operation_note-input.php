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
?>

<div class="inner_box" style="display:none;width:40em" id="jrn_note_div">
<?=\HtmlInput::title_box(_("Note"),"jrn_note_div","hide")?>

<textarea id="jrn_note_input" name= "jrn_note_input" class="input_text" cols="60" rows="7"><?=$p_current?></textarea>
<ul class="aligned-block">
    <li>
        <?=HtmlInput::button_hide("jrn_note_div")?>
    </li>

</ul>

</div>