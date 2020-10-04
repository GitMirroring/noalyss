<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2002-2020) Author Dany De Bontridder <danydb@noalyss.eu>


/**
 * @file
 * @brief manage tag_group table
 * 
 * 
 */
require_once NOALYSS_INCLUDE.'/lib/manage_table_sql.class.php';
require_once NOALYSS_INCLUDE.'/database/tag_group_sql.class.php';

class Tag_Group_MTable extends Manage_Table_SQL
{

    function __construct(Tag_Group_SQL $p_obj)
    {
        parent::__construct($p_obj);
        $this->set_property_visible("tg_id", false);
        $this->set_property_updatable("tg_id", false);
        $this->set_col_label("tg_name", _('Nom'));
    }

    function input()
    {


        parent::input();


        echo '<div id="d_tag_group_add">';
        $this->input_tag();
        echo '</div>';
    }

    /**
     * Display a select widget for adding new tag 
     */
    function input_tag()
    {
        $cn=Dossier::connect();
        $data_sql=$this->get_table();
        
        // Display containing tag_id 
        $aTag=$cn->get_array("select jt_id,t_tag 
                               from tags join jnt_tag_group_tag on (tag_id=t_id) 
                               where tag_group_id=$1 order by 2", [$data_sql->tg_id]);
        echo '<ol id="ol_tag_group">';
        $nb=count($aTag);
        $dossier_id=Dossier::id();
        for ($i=0; $i<$nb; $i++)
        {
            // js to remove id
            $js=Icon_Action::trash(uniqid(), sprintf("o_tagGroup.remove('%s','%s')", $aTag[$i]['jt_id'], $dossier_id));
            printf('<li id="t%s"> %s %s %s</li>', $aTag[$i]['jt_id'],$aTag[$i]['t_tag'],$dossier_id,$js);
        }
        echo '</ol>';


        // Add new tag
        $select=new ISelect("is_tag", [], "s_tag_group");
        $select->value=$cn->make_array("select t_id,t_tag 
                               from tags 
                               where 
                                t_id not in (select tag_id from jnt_tag_group_tag 
                               where tag_group_id=$1 ) order by 2", 0,[$data_sql->tg_id]);
        echo $select->input();
        echo HtmlInput::button('b_add_group', _("Ajout"),
                sprintf("onclick=\"o_tagGroup.add_tag('%s','%s','%s'); \"", $data_sql->tg_id, $select->id, $dossier_id));
    }

}
