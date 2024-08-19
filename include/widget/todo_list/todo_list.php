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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 10/08/24
/*! 
 * \file
 * \brief widget Todo List
 * \note this widget is  included in Noalyss Core and a part of the code (javascript + css + ajax)
 *  are still included in NOALYSS Code, this code should move here and will be part of a "cleansing code" process
 */

namespace Noalyss\Widget;

/**
 * \class Todo_List
 * \brief widget Todo List
 * @note this widget is  included in Noalyss Core and a part of the code (javascript + css + ajax)
 * are still included in NOALYSS Code, this code should move here and will be part of a "cleansing code" process
 */
class Todo_List extends Widget
{


    function display()
    {
        global $cn;
        echo '<div class="box widget-box" id="todo_list" >';
        echo \HtmlInput::title_box(_('Pense-Bête'), "todo_listg_div", 'zoom', "zoom_todo()", 'n');
        echo \Dossier::hidden();
        $todo = new \Todo_List($cn);
        $array = $todo->load_all();
        $a_todo = \Todo_List::to_object($cn, $array);

        echo \HtmlInput::button('add', _('Ajout'), 'onClick="add_todo()"', 'smallbutton');
        echo '<table id="table_todo" class="sortable" style="width:100%">';
        echo '<tr><th class=" sorttable_sorted_reverse" id="todo_list_date">Date</th><th>Titre</th><th></th>';
        if (!empty ($array)) {
            $nb = 0;
            $today = date('d.m.Y');

            foreach ($a_todo as $row) {
                if ($nb % 2 == 0) $odd = 'odd '; else $odd = 'even ';
                $nb++;
                echo $row->display_row($odd);
            }
        }
        echo '</table>';
        echo $this->display_new_note();
        echo '</div>';
    }

    /**************************************************************************
     * Ajout d'une nouvelle note
     *************************************************************************/
    private function display_new_note()
    {
        require_once 'todo_list-display_new_note.php';
    }

}


