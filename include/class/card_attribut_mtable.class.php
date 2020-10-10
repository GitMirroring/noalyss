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
 * @brief manage the table attr_def 
 * @see card_attr.inc.php
 */
require_once NOALYSS_INCLUDE."/class/fiche_attr.class.php";
require_once NOALYSS_INCLUDE."/lib/manage_table_sql.class.php";
require_once NOALYSS_INCLUDE."/lib/inplace_switch.class.php";

class Card_Attribut_MTable extends Manage_Table_SQL
{

    function __construct(\Data_SQL $p_table)
    {
        parent::__construct($p_table);
        $this->set_property_visible("ad_id", FALSE);
        $this->set_property_updatable("ad_id", FALSE);
        $this->set_col_label("ad_text", _("Nom"));
        $this->set_col_label("ad_type", _("Type"));
        $this->set_col_label('ad_size', _("Taille"));
        $this->set_col_label("ad_extra", _("Option sup."));
        $this->set_col_label("ad_search_followup", _("Recherche suivi"));

        $this->set_col_type("ad_search_followup", "custom");
        $this->set_col_type("ad_text", "custom");
        $this->set_col_type("ad_size", "numeric");
        $this->set_col_type("ad_extra", "text");
        $this->set_col_type("ad_type", "select",
                array(
                    ["value"=>"text", "label"=>_("Texte")],
                    ["value"=>"date", "label"=>_("Date")],
                    ["value"=>"numeric", "label"=>_("Nombre")],
                    ["value"=>"select", "label"=>_("Choix")],
                    ["value"=>"card", "label"=>_("Fiche")],
                    ["value"=>"zone", "label"=>_("Zone de texte")],
                    ["value"=>"poste", "label"=>_("Poste comptable")]
        ));
        $this->set_col_tips("ad_search_followup", 77);
        // to prevent a call to this function for each row
        $this->dossier_id=Dossier::id();
    }

    /**
     * Display row of table attr_def
     * @param array $row 
     */
    function display_row($row)
    {
        tracedebug("card_attribute.log", $row);
        if ($row['ad_id']>=9000)
        {
            $this->set_delete_row(TRUE);
            $this->set_update_row(TRUE);
        }
        else
        {
            $this->set_delete_row(FALSE);
            $this->set_update_row(FALSE);
        }

        parent::display_row($row);
    }

    /**
     * For the type custom , we can call a function to display properly the value
     * @param $p_key string key name
     * @param $p_value string value
     * @see input_custom
     * @see set_type
     * @note must return a string which will be in surrounded by td in the function display_row
     * @return string
     */
    function display_row_custom($p_key, $p_value, $p_id=0)
    {
        if ($p_key=="ad_search_followup")
        {
            $ic=new Inplace_Switch(uniqid("search_follow_up"), $p_value);
            $ic->set_callback("ajax_misc.php");
            $ic->add_json_param("op", "card");
            $ic->add_json_param("gDossier", $this->dossier_id);
            $ic->add_json_param("op2", "attribute");
            $ic->add_json_param("action", "enable_search");
            $ic->add_json_param("ad_id", $p_id);
            $ic->add_json_param("ctl", $p_id);
            $ret=$ic->input();

            return $ret;
        }
        if ($p_key == "ad_text"){
            return $p_value;
        }
        return;
    }

    /**
     * For the type custom , we can call a function to display properly the value
     * @param $p_key string key name
     * @param $p_value string value
     * @see input_custom
     * @see set_type
     * @note must return a string which will be in surrounded by td in the function display_row
     * @return string
     */
    function input_custom($p_key, $p_value)
    {
        if ($p_key=="ad_search_followup")
        {
            $p_id=$this->get_table()->get("ad_id");
            $ic=new ISelect("ad_search_followup", $p_value);
            $ic->value=array(["value"=>0, "label"=>_("Non")],
                ["value"=>0, "label"=>_("Oui")]
            );
            $ic->set_value($p_value);
            echo $ic->input();
        }
        /**
         * Bug in firefox if id=ad_text , the widget is not displaid 
         */
        if ( $p_key == "ad_text") 
        {
            $text=new IText("ad_text",$p_value);
            $text->id=uniqid();
            echo $text->input();
        }
        echo "";
        return;
    }
    
    function check()
    {
        $nb_error=0;
        $object_sql=$this->get_table();

        if ($object_sql->get("ad_id")<9000 && $object_sql->get("ad_id")!=-1)
        {
            $this->set_error("ad_id",_("Bloqué"));
            return false;
        }
        
        // Duplicate
        $count="select count(*) 
                from attr_def as ad1 
                where 
                upper(trim(ad1.ad_text)) = upper(trim($1))
                and ad1.ad_id != $2
                ";
        $count_duplicate=$object_sql->cn->get_value($count, [$object_sql->ad_text, $object_sql->ad_id]);
        if ($count_duplicate>0)
        {
            $this->set_error("ad_text", _("Doublon"));
            $nb_error++;
        }

        // Name empty
        if (trim($object_sql->ad_text)=="")
        {
            $this->set_error("ad_text", _("Description ne peut pas être vide"));
            $nb_error++;
        }
        // select protect 


        if ($nb_error>0)
            return false;
        return true;
    }

    function delete() {
         $object_sql=$this->get_table();
         $db=$object_sql->cn;
         try {
             $db->start();
              $object_sql=$this->get_table()->delete();
             $db->commit();
         } catch (Exception $ex) {
             $db->rollback();
            throw new Exception (_("Effacement bloqué : attribut utilisé"));
         }
    }
}
