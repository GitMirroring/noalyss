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
 * @brief Others concerned card in an action
 * @see Follow_Up
 */

/**
 * @class
 * @brief Others concerned card in an action
 * @see Follow_Up
 */

class Follow_Up_Other_Concerned
{
    private $ag_id; /*!< action_gestion.ag_id*/
    private $db; /*!< Database handle */
    
    function __construct($p_database,$p_action_gestion_id)
    {
        $this->db=$p_database;
        $this->ag_id=$p_action_gestion_id;
    }
    public function get_ag_id()
    {
        return $this->ag_id;
    }

    public function set_ag_id($ag_id)
    {
        $this->ag_id=$ag_id;
        return $this;
    }
    public function get_db()
    {
        return $this->db;
    }

    public function set_db($cn)
    {
        $this->db=$cn;
        return $this;
    }

    
         /**
     * Remove  another concerned (tiers, supplier...)
     * @remark type $g_user
     * @param type $p_fiche_id
     */
    function remove_linked_card($p_fiche_id)
    {
        global $g_user;
        if ($g_user->can_write_action($this->ag_id))
        {
            $this->db->exec_sql('delete from action_person where ag_id = $1 and f_id = $2', array($this->ag_id, $p_fiche_id));
            return true;
        }
        return false;
    }

//    /**
//     * Display the other concerned (tiers, supplier...) in a small cell
//     * @return string
//     */
//    function display_linked()
//    {
//        $a_linked=$this->db->get_array('select ap_id,f_id from action_person where ag_id=$1', array($this->ag_id));
//        if (count($a_linked)==0)
//            return "";
//        $dossier_id=Dossier::id();
//        for ($i=0; $i<count($a_linked); $i++)
//        {
//            $fiche=new Fiche($this->db, $a_linked[$i]['f_id']);
//            $qc=$fiche->get_quick_code();
//            $js_remove=sprintf("action_remove_concerned('%s','%s','%s')", dossier::id(), $a_linked[$i]['f_id'], 
//                    $this->ag_id);
//            echo '<span class="tagcell">';
//            echo HtmlInput::anchor($qc,"", sprintf("onclick=\"linked_card_option('%s','%s')\"",
//                    $a_linked[$i]['ap_id'],$dossier_id));
//            echo Icon_Action::trash(uniqid(), $js_remove);
//            echo '</span>';
//            echo '&nbsp;';
//            echo '&nbsp;';
//        }
//    }
    /**
     * Display the count of other concerned card (tiers, supplier...) in button which call 
     * @return string
     */
    function display_linked_count()
    {
        $a_linked=$this->db->get_array('select ap_id,f_id from action_person where ag_id=$1', array($this->ag_id));
        if (count($a_linked)==0)
            return "0";
        $dossier_id=Dossier::id();
        $javascript=<<<EOF
                    var obj={dossier:$dossier_id,ag_id:$this->ag_id};action_concerned_list(obj);
EOF;
        echo HtmlInput::anchor_action(count($a_linked), $javascript, null, "button");
    }
    
    /**
     *  Add another concerned (tiers, supplier...)
     * @remark type $g_user
     * @param type $p_fiche_id
     */
    function insert_linked_card($p_fiche_id)
    {
        global $g_user;
        if ($g_user->can_write_action($this->ag_id))
        {
            /**
             * insert into action_person
             */
            $count=$this->db->get_value('select count(*) from action_person where f_id=$1 and ag_id=$2', array($p_fiche_id, $this->ag_id));
            if ($count==0)
            {
                $this->db->exec_sql('insert into action_person (ag_id,f_id) values ($1,$2)', array($this->ag_id, $p_fiche_id));
            }
        }
    }
    /**
     * Return a HTML string with a button for adding other cards
     * @return type
     */
    function button_action_add_concerned_card()
    {
        $dossier=Dossier::id();
        $javascript=<<<EOF
                    var obj={dossier:$dossier,ag_id:$this->ag_id};action_concerned_search_card(obj);
EOF;
        $js=HtmlInput::button_action(_('Ajout autres'), $javascript, 'xx',
                        'smallbutton');
        return $js;
    }
    
    /**
     * Display one row for all the option of a card 
     * @param int $p_fid card if (fiche.f_id)
     * @param int $p_row index of the row used for alternate the color of the rows
     * @param array $aOther Column of the options
     */
    function display_row($p_fid,$p_row,$pa_Column){
        static $dossier_id;
        $dossier_id=Dossier::id();
        
        $action_person_id=$this->db->get_value("select ap_id from action_person where f_id=$1 and ag_id=$2",
                [$p_fid,$this->ag_id]);
        
        // Name , first & quick code from fiche
       $row= $this->db->get_row("select 
            (select ad_value from fiche_detail fd2 where fd2.f_id=ap.f_id and fd2.ad_id = 1) as xname,
            (select ad_value from fiche_detail fd2 where fd2.f_id=ap.f_id and fd2.ad_id = 32) as xfirst_name,
            (select ad_value from fiche_detail fd2 where fd2.f_id=ap.f_id and fd2.ad_id = 23) as xqcode
            from fiche ap
            where ap.f_id=$1
                ",[$p_fid]);
       
    
       // Detail for this one
       $detail = HtmlInput::anchor($row['xqcode'],"", sprintf("onclick=\"linked_card_option('%s','%s')\"",
                    $action_person_id,$dossier_id),'class="line"',_("Options"));
       // remove card
        $js_remove=sprintf("action_remove_concerned('%s','%s','%s')", dossier::id(), $p_fid, 
                    $this->ag_id);
        $remove =  Icon_Action::trash(uniqid(), $js_remove);
        $r=<<<EOF
    <tr id="other_{$action_person_id}">
       
        <td>
            {$detail}
        </td>
        <td>
            {$row['xname']}
        </td>
        <td>
            {$row['xfirst_name']}
        </td>
EOF;     
            $nb_other=count($pa_Column);
            for ($i=0;$i<$nb_other;$i++) {
                $value=$this->db->get_value("
                        select 	apo2.ap_value
                        from action_person_option apo2 
                        join action_person ap on (apo2.action_person_id=ap.ap_id) 
                        where 
                        apo2.contact_option_ref_id =$1
                        and ap.f_id=$2
                        ",array($pa_Column[$i]['cor_id'] , $p_fid));
                $r.= td($value);
            }
        $r.='<td>'.$remove.'</td>';
        $r.='</tr>'; 
        return $r;
    }
    /**
     * Display all the person with option in a html table
     */
    function display_table(){
    require_once NOALYSS_TEMPLATE."/follow_up_other_concerned_display_table.php";
    }
    /**
     * Get Available options
     */
    function get_option() 
    {
        $aColumn = $this->db->get_array("select cor_id,cor_label,jdoc.jdoc_enable 
    from contact_option_ref cor 
    join jnt_document_option_contact jdoc on (cor.cor_id=jdoc.contact_option_ref_id ) 
    join action_gestion ag on (ag.ag_type=jdoc.document_type_id ) 
    where ag_id=$1
    order by upper(cor_label)",[$this->ag_id]);
        return $aColumn;
    }
}