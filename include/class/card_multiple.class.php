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
 * @brief in follow-up , add multiple cards to an event, an action
 */
class Card_Multiple
{
    private $sql ; //!< SQL with the right column name
    function __construct()
    {
        $this->sql="select f_id,quick_code,vw_name,accounting,vw_first_name,vw_description,
            (select ad_value from fiche_detail fd where fd.f_id=vw_fiche_attr.f_id and fd.ad_id=25) as company
                  from vw_fiche_attr  ";
    }
    /**
     * 
     * @param type $sql_array
     * @return type
     */
    function build_sql($sql_array)
    {
        $cn=Dossier::connect();
        $filter="";
        if ( $sql_array["inactive_card"]==0) {
            $filter=" and ".$this->filter_enable_card();
        }
        $query=sql_string($sql_array['query']);
        if ( $sql_array['search_in'] == "-1")
        {
            $string_sql=$this->sql."
                  where 
                  (vw_name ilike '%$query%' 
                  or quick_code ilike '%$query%')
                     ".$filter."
                  order by vw_name 
                  limit 
                  ".MAX_CARD_SEARCH;
        } else {
            $string_sql=sprintf($this->sql." where f_id in (select f_id from fiche_detail where 
                    ad_id = '%s' and ad_value ilike '%%%s%%') and %s"
                    , sql_string($sql_array["search_in"]),$query,$filter);
        }
        return $string_sql;
    }
    /**
     * 
     * @param type $sql_array
     * @return type
     */
    function count_sql($sql_array)
    {
        $cn=Dossier::connect();
        $query=sql_string($sql_array['query']);
        $filter="";
        if ( $sql_array["inactive_card"]==0) {
            $filter="and ".$this->filter_enable_card();
        }

        $string_sql="select count(*) 
              from vw_fiche_attr  
              where 
              ( vw_name ilike '%$query%' 
              or quick_code ilike '%$query%')
                     ".$filter;


        return $cn->get_value($string_sql); 
    }
    /**
     * 
     * @return string
     */
    private function filter_enable_card()
    {
        $filter_enable_card="  f_id in (select f_id from fiche_detail where ad_value = '1' and ad_id=54) ";
        return $filter_enable_card;
    }
    /**
     * 
     * @global type $g_user
     * @param type $ap_id
     * @return type
     */
    function display_option($p_action_person_id)
    {
        global $g_user;
        $cn=Dossier::connect();
        // retrieve card id (fiche.f_id) and the ag_id (action_gestion.ag_id)
        $tmp=$cn->get_row("select f_id,ag_id from action_person where ap_id=$1 ", [$p_action_person_id]);
        if ($tmp==NULL)
        {
            record_log("CMDO.01nothing found ".var_export($_REQUEST, true));
            return;
        }
        $fiche_id=$tmp['f_id'];
        $ag_id=$tmp['ag_id'];
        if ( ! $g_user->can_read_action($ag_id)) {
            throw new Exception (_("CMCDO01"."Security"));
        }
        // insert new , synchronized
        $cn->exec_sql("insert into action_person_option (ap_value,contact_option_ref_id ,action_person_id ) 
            select null,cor_id,$1
            from contact_option_ref 
            where 
            cor_id not in (
                select contact_option_ref_id 
                from action_person_option 
                join action_person a on (a.ap_id=action_person_id) 
                where f_id=$2)",[$p_action_person_id,$fiche_id]);

        // delete disable
        $cn->exec_sql("delete 
                from action_person_option apo 
                where contact_option_ref_id  in 
               (select contact_option_ref_id from jnt_document_option_contact jdoc join action_gestion on                       (ag_type=document_type_id) 
                    where ag_id=$1 and jdoc_enable=0)",[$ag_id]);
        
        // First select the option
        $sql="select ap_id,cor_id,ap_value,cor_type ,cor_label,cor_value_select
              from contact_option_ref cor 
              join action_person_option apo on (cor.cor_id=apo.contact_option_ref_id) 
              where action_person_id=$1 order by cor_label  ";
        
        $a_option=$cn->get_array($sql,[$p_action_person_id]);
        require NOALYSS_TEMPLATE."/card_multiple_display_option.php";
        
    }

}
