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
 * @brief 
 */
require_once NOALYSS_INCLUDE."/database/parameter_extra_sql.class.php";
require_once NOALYSS_INCLUDE."/lib/manage_table_sql.class.php";

class Parameter_Extra_MTable extends Manage_Table_SQL
{
    function __construct(\Data_SQL $p_table)
    {
        parent::__construct($p_table);
    }
    static function build ($p_id=-1)
    {
        $cn=Dossier::connect();
        $object_sql=new Parameter_Extra_SQL($cn,$p_id);
        $object=new Parameter_Extra_MTable($object_sql);
        $object->set_button_add_top(false);
        $object->set_col_label("pe_code", _("Code"));
        $object->set_col_label("pe_label", _("Intitulé"));
        $object->set_col_label("pe_value", _("Valeur"));
        $object->set_order(['pe_code','pe_label','pe_value']);
        $object->set_search_table(false);
        $object->set_callback("ajax_misc.php");
        $object->add_json_param("op", "company");
        $object->setCssClass("inner_box w-20");
        
        return $object;
        
    }
    /**
     * Check before insert or update
     */
    function check() {
        $object=$this->get_table();
        $cn=$object->cn;
        $error=0;
        // duplicate
        $code=$cn->get_value("select comptaproc.transform_to_code($1)",[ $object->getp("pe_code")]);
        if ( $cn->get_value("select count(*) from parameter_extra where pe_code =$1 and id != $2",
                [$code,$object->getp("id")]) > 0 ) {
            $this->set_error("pe_code", _("Code déjà utilisé"));
            $error++;
        }
        // pe_code cannot be empyt
        if ( $code == "") {
            $this->set_error("pe_code", _("ne peut être vide"));
            $error++;
        }
                // pe_code cannot be empyt
        if ( preg_match('/^ATTR/', $code) == 1 ||
             preg_match('/^CUSTATTR/', $code) == 1 ||
             preg_match('/^BENEFATTR/', $code) == 1 )
        {
            $this->set_error("pe_code", _("ne peut être pas commencer par ATTR , BENEFATTR ou CUSTATTR"));
            $error++;
        }
        if ($error > 0) { return false;}
        
        return true;
    }
}