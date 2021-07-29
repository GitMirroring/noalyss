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
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief Manage the table profile_mobile
 */

/**
 * @class Mobile_Device_MTable
 * @brief Manage the table profile_mobile
 */
class Mobile_Device_MTable extends Manage_Table_SQL
{
    private $profile_id; //!< profile_mobile.p_id profile 
    
    function __construct(Profile_Mobile_SQL $p_table)
    {
        parent::__construct($p_table);
        $this->set_append_row(true);
        $this->set_delete_row(true);
        $this->set_col_label("me_code", _("Code Menu"));
        $this->set_col_label("pmo_order", _("Ordre apparition"));
        $this->set_col_label("pmo_default", _("Entêtes standards"));
        $this->set_col_tips("pmo_default",80 );
        $this->set_property_visible("pmo_id", false);
        $this->set_property_visible("p_id", false);
        $this->set_col_type("pmo_default", "select",array(
                                                    ["value"=>1,"label"=>_("Oui")],
                                                    ["value"=>0,"label"=>_("Non")]
                                                    ));
        $this->set_col_type("me_code","custom");
        $this->set_col_type("pmo_order","numeric");
        $this->set_header_option("pmo_order",'style="text-align:right;"');
        $this->set_callback("ajax_misc.php");
        $this->add_json_param("op","mobile_device_menu");
        $this->set_sort_column("pmo_order");
        $this->set_col_sort(1);
        $this->set_order(["me_code","pmo_order","pmo_default"]);
        
        
        $this->set_object_name("profile_menu_mtable");
    }
    public function get_profile_id()
    {
        return $this->profile_id;
    }

    public function set_profile_id($profile_id)
    {
        $this->profile_id=$profile_id;
        $this->get_table()->setp('p_id',$profile_id);
        $this->add_json_param("profile_id",$profile_id);
        return $this;
    }

    /**
     * @brief 
     * @param number $p_id profile_mobile.pmo_id
     * @param number $profile_id  profile_mobile.p_id profile 
     * @return \Mobile_Device_MTable
     */
    static function build($p_id,$profile_id)
    {
        $cn=Dossier::connect();
        $profile_mobile=new Profile_Mobile_SQL($cn,$p_id);
        if ( $p_id== -1) { 
            $profile_mobile->setp("pmo_order",5); 
            $profile_mobile->setp("pmo_default",1);
        }
        
        $mobile_device_table=new Mobile_Device_MTable($profile_mobile);
      /*  $mobile_device_table->set_profile_id($profile_id);
        $mobile_device_table->get_table()->setp('p_id',$profile_id);*/
        return $mobile_device_table;
    }
    function input_custom($p_key,$p_value)
    {
        
        if ( $p_key == "me_code") {
            $select = new ISelect("me_code");
            $cn=$this->get_table()->get_cn();
            $select->value=$cn->make_array("select me_code , me_code ||' '||coalesce(me_description,'') from menu_ref 
                    where
                        me_type in ('PL','ME') and trim(me_code) != 'new_line'
                    order by me_code");
            $select->rowsize=17;
            $select->selected=$p_value;
            echo td($select->input());
            return;
        }
    }
    function display_row_custom($p_key, $p_value, $p_id=0)
    {
        if ( $p_key == 'me_code') {
            echo td($p_value);
            return;
        }
    }
    /**
     * @brief before inserting or updating, check that the data are correct ,
     * 
     */
    function check()
    {
        // DB connection
        $cn=$this->get_table()->cn;
        // object to insert
        $profile_mobile_sql=$this->get_table();
        $profile_mobile_sql->me_code=strtoupper($profile_mobile_sql->getp('me_code'));
        $me_code=$profile_mobile_sql->me_code;
        
        $profile_id=$profile_mobile_sql->getp("p_id");
        $pmo_id=$profile_mobile_sql->getp("pmo_id");
        
        // check for duplicate
        if ( $cn->get_value("select count(*) from profile_mobile where p_id = $1 and me_code=$2 and pmo_id <> $3",
                        array($profile_id,$me_code,$pmo_id )) > 0
            ) {
            $this->set_error("me_code", _("Doublon"));
        }
        
        if (isNumber($profile_mobile_sql->getp("pmo_order")) != 1 ) {
            $this->set_error("pmo_order", _("doit être un nombre"));
        }
        
        if ( $cn->get_value("select count(*) from menu_ref where me_code=$1",[$me_code]) == 0) {
            $this->set_error ("me_code",_('Menu code invalide'));
        }
        if ($this->count_error() > 0) {
            return false;
        }
        return true;
    }
}