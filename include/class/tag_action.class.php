<?php

/*
 * * Copyright (C) 2020 Dany De Bontridder <dany@alchimerys.be>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

 * 
 * Author : Dany De Bontridder danydb@noalyss.eu
 * 
 */


/**
 * @file
 * @brief  concerns the tags linked to an action
 */

/**
 * @class Tag_Action
 * @brief  concerns the tags linked to an action
 */
class Tag_Action extends Tag
{
    function __construct($p_cn, $p_id=-1 )
    {
        parent::__construct($p_cn, $p_id);
    }

    /**
     * @brief let select a tag to add
     */
    function select($p_prefix="")
    {
        $ret=parent::query_active_tag();
        require_once NOALYSS_TEMPLATE.'/tag_select.php';
    }

    /**
     * @brief Show a button to select tag for Search
     * @return HTML
     */
    static function select_tag_search($p_prefix)
    {
        $r="";
        $r.=HtmlInput::button("choose_tag", _("Etiquette"),
                        'onclick="search_display_tag('.Dossier::id().',\''.$p_prefix.'\',\'Tag_Action\')"', "smallbutton");
        return $r;
    }

    /**
     * @brief clear the search cell
     */
    static function add_clear_button($p_prefix)
    {
        $clear=HtmlInput::button('clear', 'X', 'onclick="search_clear_tag('.Dossier::id().',\''.$p_prefix.'\');"',
                        'smallbutton');
        return $clear;
    }

    /**
     *@brief  In the screen search add this data to the cell
     */
    function update_search_cell($p_prefix)
    {
        $data=$this->get_data();
        printf ('<span id="sp_%s%s" class="tagcell tagcell-color%s">',$p_prefix,$data->t_id,$data->t_color);
        echo h($data->t_tag);
        echo HtmlInput::hidden($p_prefix.'tag[]', $data->t_id);
        $js=sprintf("$('sp_".$p_prefix.$data->t_id."').remove();");
        echo Icon_Action::trash(uniqid(), $js);
        echo '</span>';
    }

    /**
     * @brief let select a tag to add to the search
     */
    function select_search($p_prefix,$title=true)
    {
        $res="";
        $ret=$this->get_data()->seek(' order by t_tag');
        require_once NOALYSS_TEMPLATE.'/tag_search_select.php';
        if ($title)         return HtmlInput::title_box('Tag', $p_prefix.'tag_div').$res;
        else return $res;
    }

}
