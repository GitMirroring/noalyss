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
require_once NOALYSS_INCLUDE.'/database/tag_sql.class.php';

/**
 * @file
 * @brief Tag operations or actions to linked them together
 */

/**
 * @class Tag
 * @brief Tag operations or actions to linked them together
 */
class Tag
{
    private $data; //<! Tag_SQL
    private $cn;
    function __construct($p_cn,$id=-1)
    {
        $this->cn=$p_cn;
        $id = (trim($id)=="")?-1:$id;
        $this->data=new Tag_SQL($p_cn,$id);
    }
    public function get_data()
    {
        return $this->data;
    }

    public function set_data($data)
    {
        $this->data=$data;
        return $this;
    }

    /**
     * Show the list of available tag
     * @return HTML
     */
    function show_list()
    {
        $ret=$this->data->seek(' order by t_tag');
        if ( $this->cn->count($ret) == 0) return "";
        require_once NOALYSS_TEMPLATE.'/tag_list.php';
    }
    
    /**
     * Display a inner window with the detail of a tag
     */
    function form_add()
    {
        $data=$this->data;
        require_once NOALYSS_TEMPLATE.'/tag_detail.php';
    }
    /**
     * Show the tag you can add to a document
     */
    function show_form_add()
    {
        echo h2(_("Ajout d'un dossier (ou tag)"));
       
        $this->form_add();
    }
    function save($p_array)
    {
        if ( trim($p_array['t_tag'])=="" ) return ;
        $this->data->t_id=$p_array['t_id'];
        $this->data->t_tag=  strip_tags($p_array['t_tag']);
        $this->data->t_description=strip_tags($p_array['t_description']);
        $this->data->t_actif=$p_array['t_actif'];
        $this->data->t_color=$p_array['tagcell_color'];
        $this->data->save();
    }
    function remove($p_array)
    {
        $this->data->t_id=$p_array['t_id'];
        $this->data->delete();
    }
    /***
     * query the active tag and returns the database handler
     */
    function query_active_tag()
    {  
        $ret=$this->cn->exec_sql(" select t_id,t_tag,t_description,'t' as tag_type ,t_color
                    from tags 
                    where t_actif='Y' 
                union all 
                select tg_id,tg_name ,'G','g' ,1 from tag_group order by 2");
        return $ret;
    }
}

?>
