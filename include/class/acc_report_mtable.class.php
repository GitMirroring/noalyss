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

require_once NOALYSS_INCLUDE.'/database/form_detail_sql.class.php';
/**
 * @file
 * @brief  manage simple report 
 */
class Acc_Report_MTable extends Manage_Table_SQL
{

    private $form_definition_id;

    function __construct(\Data_SQL $p_table)
    {
        parent::__construct($p_table);
    }

    static function build($p_id, $p_form_def_id)
    {
        $cn=Dossier::connect();
        $form_detail=new Form_Detail_SQL($cn, $p_id);
        if ( $p_id == -1) {
            $max=$cn->get_value("select coalesce(max(fo_pos),0) +10 from form_detail where fo_fr_id=$1",
                     [$p_form_def_id]);
            $form_detail->setp("fo_pos",$max);
        }
        $acc_report_mtable=new Acc_Report_MTable($form_detail);
        $acc_report_mtable->set_order(['fo_pos', 'fo_label', 'fo_formula']);

        $acc_report_mtable->set_col_label("fo_pos", _("Position"));
        $acc_report_mtable->set_col_label("fo_label", _("Libellé"));
        $acc_report_mtable->set_col_label("fo_formula", _("Formula"));
        $acc_report_mtable->set_form_definition_id($p_form_def_id);

        $acc_report_mtable->set_sort_column("fo_pos");

        $acc_report_mtable->set_col_type("fo_pos", "numeric");
        $acc_report_mtable->set_col_type("fo_formula", "custom");
        $acc_report_mtable->set_col_tips("fo_formula", 79);

        $acc_report_mtable->add_json_param("form_def", $p_form_def_id);
        $acc_report_mtable->add_json_param("op", "report_definition");
        $acc_report_mtable->set_callback("ajax_misc.php");

        return $acc_report_mtable;
    }

    public function get_form_definition_id()
    {
        return $this->form_definition_id;
    }

    public function set_form_definition_id($form_definition_id): void
    {
        $this->form_definition_id=$form_definition_id;
    }

    function input_custom($p_key, $p_value)
    {
        if ($p_key=='fo_formula')
        {
            $formula=new ITextarea('fo_formula');
            $formula->id='fo_formula';
            $formula->value=$p_value;
            echo $formula->input();
            
            echo HtmlInput::button_action(_("Cherche poste, fiche , analytique"), 
                                sprintf('search_account_card({gDossier:%s,target:\'%s\'})',Dossier::id(),
                                        "fo_formula")
                    );
            echo HtmlInput::hidden("form_def",$this->form_definition_id);
        }
    }

    function display_row_custom($p_key, $p_value, $p_id=0)
    {
        if ($p_key=='fo_formula')
        {

            return td($p_value);
        }
    }

    function display_table($p_order="", $p_array=NULL)
    {

        return parent::display_table(" where fo_fr_id = $1 order by fo_pos", [$this->get_form_definition_id()]);
    }
    function check()
    {
        $obj=$this->get_table();
        $obj->setp("fo_fr_id",$this->form_definition_id);
        $n=0;
        if ( ! Impress::check_formula($obj->getp("fo_formula"))) {
            $this->set_error("fo_formula", _("Formule invalide"));
            $n++;
            
        }
        if ( trim($obj->getp("fo_label")) == "") {
            $n++;
            $this->set_error("fo_label",_("Nom est vide"));
        }
               
        if ($n == 0 ) { return true; }
        return false;
    }

}
