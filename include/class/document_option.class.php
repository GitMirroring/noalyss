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
 * @brief 
 */

/**
 * @class
 * @brief
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

}
