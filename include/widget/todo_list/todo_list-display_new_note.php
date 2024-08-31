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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 11/08/24
/*! 
 * \file
 * \brief 
 */
?>
<div id="add_todo_list" class="box" style="display:none">

    <form method="post">
        <?php
        $wDate=new IDate('p_date_todo');
        $wDate->id='p_date_todo';
        $wTitle=new IText('p_title');
        $wDesc=new ITextArea('p_desc');
        $wDesc->heigh=5;
        $wDesc->width=40;
        echo HtmlInput::title_box(_("Note"),"add_todo_list","hide",'',"n");
        echo _("Date")." ".$wDate->input().'<br>';
        echo _("Titre")." ".$wTitle->input().'<br>';
        echo _("Description")."<br>".$wDesc->input().'<br>';
        echo dossier::hidden();
        echo HtmlInput::hidden('tl_id',0);
        echo HtmlInput::submit('save_todo_list',_('Sauve'),'onClick="Effect.Fold(\'add_todo_list\');return true;"');
        echo HtmlInput::button('hide',_('Annuler'),'onClick="Effect.Fold(\'add_todo_list\');return true;"');
        ?>
    </form>
</div>
