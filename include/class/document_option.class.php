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
 * @brief in follow-up , you have action which can be set with different options depending of the type of documents like
 * invoicing, meeting, ...
 */

/**
 * @class Document_Option
 * @brief in follow-up , you have action which can be set with different options depending of the type of documents like
 * invoicing, meeting, ...
 */
class Document_Option
{

    /**
     * returns true if the operation_detail is enable, otherwise false
     * 
     * @param int $p_document_type Document_Type.dt_id
     * @return boolean
     */
    static function is_enable_operation_detail($p_document_type)
    {
        $display_operation=false;
        $cn=Dossier::connect();
        if ($cn->get_value("select do_enable from document_option where document_type_id=$1 and do_code = $2",
                        [$p_document_type, 'detail_operation'])=='1')
        {
            $display_operation=true;
        }
        return $display_operation;
    }
    /**
     * returns true if comment are available otherwise false
     * 
     * @param int $p_document_type Document_Type.dt_id
     * @return boolean
     */
    static function is_enable_comment($p_document_type)
    {
        $display_operation=false;
        $cn=Dossier::connect();
        if ($cn->get_value("select do_enable from document_option where document_type_id=$1 and do_code = $2",
                        [$p_document_type, 'followup_comment'])=='1')
        {
            $display_operation=true;
        }
        return $display_operation;
    }
    /**
     * returns option from  the operation_detail 
     * 
     * @param int $p_document_type Document_Type.dt_id
     * @return string
     */
    static function option_operation_detail($p_document_type)
    {
        $cn=Dossier::connect();
        $option_operation = $cn->get_value("select do_option from document_option where document_type_id=$1 "
                . " and do_code = $2",
                        [$p_document_type, 'detail_operation']);
        return $option_operation;
    }

    /**
     * returns true if the operation_detail is enable, otherwise false
     * 
     * @param int $p_document_type Document_Type.dt_id
     * @return boolean
     */
    static function is_enable_contact_multiple($p_document_type)
    {
        $return=false;
        $cn=Dossier::connect();
        if ($cn->get_value("select do_enable from document_option where document_type_id=$1 and do_code = $2",
                        [$p_document_type, 'contact_multiple'])=='1')
        {
            $return=true;
        }
        return $return;
    }

    /**
     * returns true if the operation_detail is enable, otherwise false
     * 
     * @param int $p_document_type Document_Type.dt_id
     * @return boolean
     */
    static function is_enable_make_invoice($p_document_type)
    {
        $return=false;
        $cn=Dossier::connect();
        if ($cn->get_value("select do_enable from document_option where document_type_id=$1 and do_code = $2",
                        [$p_document_type, 'make_invoice'])=='1')
        {
            $return=true;
        }
        return $return;
    }
    /**
     * returns true if the operation_detail is enable, otherwise false
     *
     * @param int $p_document_type Document_Type.dt_id
     * @return boolean
     */
    static function is_enable_make_feenote($p_document_type)
    {
        $return=false;
        $cn=Dossier::connect();
        if ($cn->get_value("select do_enable from document_option where document_type_id=$1 and do_code = $2",
                [$p_document_type, 'make_feenote'])=='1')
        {
            $return=true;
        }
        return $return;
    }
    /**
     * Returns true if we can add a comment , or false if it is not possible
     * @param integer $p_id is the action_gestion.ag_id
     */
    static  function can_add_comment($p_id) {
        $cn=Dossier::connect();
        $document_type=$cn->get_value("select ag_type from action_gestion where ag_id=$1",[$p_id]);
        if (Document_Option::is_enable_comment($document_type)) return true;
        // If comment are disable, only the first one is authorized as event description
        $cnt=$cn->get_value("select count(*) from action_gestion_comment where ag_id=$1",[$p_id]);
        if ($cnt == 0 ) return true;
        return false;
        
    }
    
    /**
     * returns option from  the operation_detail 
     * 
     * @param int $p_document_type Document_Type.dt_id
     * @return string ONE_EDIT or SOME_FIXED
     */
    static function option_comment($p_document_type)
    {
        $cn=Dossier::connect();
        $option_operation = $cn->get_value("select do_option from document_option where document_type_id=$1 "
                . " and do_code = $2",
                        [$p_document_type, 'followup_comment']);
        return $option_operation;
    }
    
    /**
     * returns true if it is possible to edit the description , otherwise false
     * 
     * @param int $p_document_type Document_Type.dt_id
     * @return boolean
     */
    static function is_enable_editable_description($p_document_type)
    {
        $return=false;
        $cn=Dossier::connect();
        if ($cn->get_value("select do_enable from document_option where document_type_id=$1 and do_code = $2",
                        [$p_document_type, 'editable_description'])=='1')
        {
            $return=true;
        }
        return $return;
    }
    
     /**
     * returns true there is a videoconf enable
     * 
     * @param int $p_document_type Document_Type.dt_id
     * @return boolean
     */
    static function is_enable_video_conf ($p_document_type)
    {
         $return=false;
        $cn=Dossier::connect();
        if ($cn->get_value("select do_enable from document_option where document_type_id=$1 and do_code = $2",
                        [$p_document_type, 'videoconf_server'])=='1')
        {
            $return=true;
        }
        return $return;
    }
    
     /**
     * returns the videoconf server
     * 
     * @param int $p_document_type Document_Type.dt_id
     * @return string with url to videoconf server
     */
    static function option_video_conf($p_document_type)
    {
        $cn=Dossier::connect();
        $option_operation = $cn->get_value("select do_option from document_option where document_type_id=$1 "
                . " and do_code = $2",
                        [$p_document_type, 'videoconf_server']);
        return $option_operation;
    }

}
