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
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

/**
 * @file
 * @brief Manage the Payment method
 * @see Payment_Method_SQL
 * @see ajax_payment_method.php
 */
require_once NOALYSS_INCLUDE."/database/jrn_def_sql.class.php";
/**
 * @class Payment_Method_MTable
 * @brief Manage the Payment method
 * @see Payment_Method_SQL
 * @see ajax_payment_method.php
 */
class Payment_Method_MTable extends Manage_Table_SQL
{

    function __construct(\Data_SQL $p_table)
    {
        parent::__construct($p_table);

        // Column header
        $this->set_col_label("mp_lib", _("Libellé"));
        $this->set_col_label("mp_jrn_def_id", _("Enregistrement dans"));
        $this->set_col_label("mp_fd_id", _("Type de fiche"));
        $this->set_col_label("mp_qcode", _("Fiche"));
        $this->set_col_label("jrn_def_id", _("Utilisation dans"));
        // Hide id
        $this->set_property_visible("mp_id", FALSE);
        $this->icon_mod="right";

        // Set value for col
        $this->set_col_type("mp_qcode","custom");
        $this->set_col_type("mp_jrn_def_id", "select",
        $p_table->cn->make_array("select jrn_def_id,jrn_def_name from jrn_def  where jrn_def_type in  ('FIN','ODS') and jrn_enable=1 order by 2",1));
        $this->set_col_type("mp_fd_id", "select",
                $p_table->cn->make_array("select fd_id,fd_label from fiche_def order by 2",1));
        $this->set_col_type("jrn_def_id", "select",
                $p_table->cn->make_array("select jrn_def_id,jrn_def_name from jrn_def  where jrn_def_type in ('ACH','VEN') and jrn_enable=1 order by 2",1));
        $this->set_col_tips("mp_fd_id", 71);
        $this->set_col_tips("mp_qcode", 72);
        $this->set_order(["mp_lib","jrn_def_id","mp_jrn_def_id","mp_fd_id","mp_qcode"]);
        
    }

    function check()
    {
        $table=$this->get_table();
        $cn=$table->cn;
        $has_error=0;

        if ( trim($table->mp_lib) == "") {
            $this->set_error("mp_lib", _("Un libellé est obligatoire"));
            $has_error++;
        }
        $count_ledger_target=0;
        if ( trim($table->mp_jrn_def_id) !="") {
            $count_ledger_target=$cn->get_value("select count(*) from jrn_def where jrn_def_id=$1",[$table->mp_jrn_def_id]);
          
        } 
        if ($count_ledger_target == 0 ) {
            $this->set_error("mp_jrn_def_id", _("Choisissez un journal"));
                $has_error++;  
        }
        
        $count_ledger_used=0;
        if ( trim($table->jrn_def_id) != "") {
            $count_ledger_used=$cn->get_value("select count(*) from jrn_def where jrn_def_id=$1",[$table->jrn_def_id]);
        }
        
        if ($count_ledger_used == 0) {
            $this->set_error("jrn_def_id", _("Choisissez un journal"));
            $has_error++;
        }
        // if the ledger is financial, the qcode MUST be the default one
        if ( trim($table->mp_lib) == "") {
            $this->set_error("mp_lib", _("Un libellé est obligatoire"));
            $has_error++;
        }
        
        if (trim ($table->mp_qcode) != "") {
            $ledger=new Jrn_def_SQL($cn , $table->jrn_def_id);
            if ( $ledger->get('jrn_def_type') == 'FIN'){
                $fiche=new Fiche($cn);
                if ( $fiche->get_by_qcode($table->mp_qcode,FALSE) == 1) {
                    $this->set_error("mp_qcode",_("Fiche inexistante"));
                    $has_error++;
                }
            }
        }
        // get the type of the ledger
        $a_row= $cn->get_row("select jrn_def_type,jrn_def_bank from jrn_def where jrn_def_id = $1",[$table->mp_jrn_def_id]);
        
        // if ledger FIN then set the mp_qcode to the right card and set mp_fd_id to the same than mp_qcode
        if ( $a_row['jrn_def_type'] == 'FIN') {
                $fiche=new Fiche($cn,$a_row['jrn_def_bank']);
                $table->mp_qcode=$fiche->get_quick_code();
                $table->mp_fd_id=$cn->get_value("select fd_id from fiche where f_id=$1",[$a_row['jrn_def_bank']]);
        } 
        
        
        if (  $a_row['jrn_def_type'] != 'FIN' && $table->mp_fd_id == -1)
        {
            $this->set_error("mp_fd_id",_("Choisissez un type de fiche"));
            $has_error++;
        }
        if ( $has_error > 0)            return FALSE;
        return TRUE;
    }
    /**
     * For the quickcode
     * @param type $p_key
     * @param type $p_value
     */
    function input_custom($p_key,$p_value)
    {
        $cn=$this->get_table()->cn;
        switch ($p_key)
        {
            case "mp_qcode":
                $w=new ICard("mp_qcode",$p_value);
                $w->set_attribute("typecard", "all");
                $w->autocomplete=0;
                
                echo $w->input();
                echo $w->search();
                break;
            default:
                printf (_("Erreur key %s value %s"),$p_key,$p_value);
                break;
        }
    }
}
