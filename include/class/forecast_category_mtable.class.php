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

/**
 * \file
 * \brief display, add, delete and modify forecast category
 *
 */
/**
 * \class Forecast_Category_MTable
 * \brief display, add, delete and modify forecast category
 *
 */
require_once NOALYSS_INCLUDE."/database/forecast_category_sql.class.php";

class Forecast_Category_MTable extends Manage_Table_SQL
{
    private $forecast_id;
    public function __construct(Data_SQL $p_table)
    {
        parent::__construct($p_table);
        $this->forecast_id=-1;
    }
    static function  build($p_id=-1)
    {
        $cn=Dossier::connect();
        $forecast_category_sql=new Forecast_Category_SQL($cn,$p_id);
        $forecast_category=new Forecast_Category_MTable($forecast_category_sql);
        $forecast_category->add_json_param("ac",'FORECAST');
        $forecast_category->add_json_param("op",'forecast_category');
        $forecast_category->set_callback("ajax_misc.php");
        $forecast_category->set_col_label("fc_desc", _("Nom"));
        $forecast_category->set_col_label("fc_order", _("Ordre"));
        $forecast_category->set_col_type("fc_order","numeric");
        $forecast_category->set_sort_column("fc_order");
        $forecast_category->set_col_type("fc_order","numeric");
        $forecast_category->set_button_add_top(false);
        $forecast_category->set_order(["fc_order","fc_desc"]);
        if ( $p_id != -1 ) {
            $forecast_id=$cn->get_value("select f_id 
                            from 
                                 forecast_category fc 
                            where 
                            fc_id=$1",array($p_id));
            $forecast_category->set_forecast_id($forecast_id);
        }
        return $forecast_category;
    }
    /**
     * @return int
     */
    public function get_forecast_id(): int
    {
        return $this->forecast_id;
    }

    /**
     * @param int $forecast_id
     */
    public function set_forecast_id($forecast_id)
    {
        $this->forecast_id = $forecast_id;
        $this->add_json_param("forecast_id", $forecast_id);
        $this->get_table()->setp("f_id",$forecast_id);
    }

    function input()
    {
        parent::input();
        $cn=$this->get_table()->cn;
        $nb_element=$cn->get_value("select count(*) from forecast_item where fc_id=$1",
                [$this->get_table()->getp("fc_id")]);
        printf(_("Utilisation %s"),$nb_element);
        echo '<p></p>';
    }

    function check()
    {
        $err=0;
         $cn=$this->get_table()->cn;
        $object=$this->get_table();
        if (trim ($object->getp("fc_desc") ) == "" ) {
            $this->set_error("fc_desc", _("Nom ne peut être vide"));
            $err++;
        }
        $dupl=$cn->get_value("select count(*) from forecast_category 
                 where 
                 upper(replace (fc_desc,' ',''))= upper(replace ($2,' ',''))  
                 and fc_id != $1 and f_id=$3",[$object->fc_id,$object->fc_desc,$this->forecast_id]);
        
        if ( $dupl > 0) {
            $this->set_error("fc_desc", _("Ce nom existe déjà"));
            $err++;
        }
        if ( trim($object->fc_order) =='') {
            $object->fc_order=$cn->get_value("select max(fc_order) from forecast_category where f_id=$1",
                    [$object->f_id])+10;
        }
        if ( $err > 0 ) { return false;}
        return true;
    }
}