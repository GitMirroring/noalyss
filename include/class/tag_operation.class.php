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

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief   concerns the tags linked to an accountancy writing
 */

/**
 * @class Tag_Operation
 * @brief   concerns the tags linked to an accountancy writing
 */
class Tag_Operation extends Tag
{

    private $jrn_id=-1;

    function __construct($p_cn, $p_id=-1)
    {
        parent::__construct($p_cn, $p_id);
    }

    public function get_jrn_id()
    {
        return $this->jrn_id;
    }

    public function set_jrn_id($jrn_id)
    {
        $this->jrn_id=$jrn_id;
        return $this;
    }

    /**
     * let select a tag to add
     */
    function select($p_prefix="")
    {
        $ret=parent::query_active_tag();
        require_once NOALYSS_TEMPLATE.'/tag_select.php';
    }

    /**
     * Show a button to select tag for Search
     * @param $p_jr jr_id of the operation
     * @param $p_div prefix of the dialog box
     * @return HTML
     */
    static function button_search($p_jr, $p_div)
    {
        $r="";
        $js=sprintf("onclick=\"new operation_tag('%s').select('%s','%s')\"", $p_div, dossier::id(), $p_jr);
        $r.=HtmlInput::button('tag_bt', _('Ajout étiquette'), $js, 'smallbutton');
        return $r;
    }

    /**
     * clear the search cell
     */
    static function add_clear_button($p_prefix)
    {
        $clear=HtmlInput::button('clear', 'X', 'onclick="search_clear_tag('.Dossier::id().',\''.$p_prefix.'\');"',
                        'smallbutton');
        return $clear;
    }

    /**
     * In the screen search add this data to the cell
     */
    function update_search_cell($p_prefix)
    {
        $data=$this->get_data();
        echo '<span id="sp_'.$p_prefix.$data->t_id.'" class="tagcell">';
        echo h($data->t_tag);
        echo HtmlInput::hidden($p_prefix.'tag[]', $data->t_id);
        $js=sprintf("$('sp_".$p_prefix.$data->t_id."').remove();");
        echo Icon_Action::trash(uniqid(), $js);
        echo '</span>';
    }

    /**
     * Links a tag to an operation
     * @param int $p_tag_id tag.t_id 
     */
    function tag_add($p_tag_id)
    {
        if ($this->jrn_id==-1)
        {
            throw new Exception("TO99 jrn_id unset");
        }
        $count=$this->cn->get_value('select count(*) from operation_tag'.
                ' where jrn_id=$1 and tag_id=$2', array($this->jrn_id, $p_tag_id));
        if ($count>0)
            return;
        $sql=' insert into operation_tag (jrn_id,tag_id) values ($1,$2)';
        $this->cn->exec_sql($sql, array($this->jrn_id, $p_tag_id));
    }

    /**
     * @brief show the cell content in Display for the tags
     * @param $div prefix of the DOM id 
     * called also by ajax
     */
    function tag_cell($p_div)
    {
        global $g_user;
        $a_tag=$this->tag_get();
        $c=count($a_tag);
        $ledger=$this->cn->get_value("select jr_def_id from jrn where jr_id=$1", [$this->jrn_id]);
        $access=$g_user->get_ledger_access($ledger);
        for ($e=0; $e<$c; $e++)
        {
            echo '<span class="tagcell tagcell-color'.$a_tag[$e]['t_color'].'">';
            echo $a_tag[$e]['t_tag'];
            if ($access=='W')
            {
                $js_remove=sprintf("new operation_tag('%s').remove('%s','%s','%s')", $p_div, dossier::id(),
                        $this->jrn_id, $a_tag[$e]['t_id']);
                echo Icon_Action::trash(uniqid(), $js_remove);
            }
            echo '</span>';
            echo '&nbsp;';
            echo '&nbsp;';
        }
    }

    /**
     * @brief get the tags of the current objet
     * @return an array idx [ag_id,t_id,at_id,t_tag]
     */
    function tag_get()
    {
        if ($this->jrn_id==-1)
        {
            throw new Exception("TO01 jrn_id unset");
        }
        $sql='select b.jrn_id,a.t_id,a.t_id,a.t_tag,a.t_color'
                .' from '
                .' tags as a join operation_tag as b on (a.t_id=b.tag_id)'
                .' where jrn_id=$1 '
                .' order by a.t_tag';
        $array=$this->cn->get_array($sql, array($this->jrn_id));
        return $array;
    }

    /**
     * Remove a tag 
     * @param int  $p_tag_id tag id
     */
    function tag_remove($p_tag_id)
    {
        if ($this->jrn_id==-1)
        {
            throw new Exception("TO01 jrn_id unset");
        }
        $this->cn->exec_sql("delete from operation_tag where jrn_id=$1 and tag_id=$2", [$this->jrn_id, $p_tag_id]);
    }

    /**
     * let select a tag to add to the search
     */
    function select_search($p_prefix)
    {
        $res="";
        $ret=$this->get_data()->seek(' order by t_tag');
        require_once NOALYSS_TEMPLATE.'/tag_search_select.php';
        return HtmlInput::title_box('Tag', $p_prefix.'tag_div').$res;
    }
    /**
     * Show a button to select tag for Search
     * @return HTML
     */
    static function select_tag_search($p_prefix)
    {
        $r="";
        $r.=HtmlInput::button("choose_tag", _("Etiquette"),
                        'onclick="search_display_tag('.Dossier::id().',\''.$p_prefix.'\',\'Tag_Operation\')"', "smallbutton");
        return $r;
    }

}

?>