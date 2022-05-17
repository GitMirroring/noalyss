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
 *  Copyright (2002-2022) Author Dany De Bontridder <danydb@noalyss.eu>
 */
class Acc_Other_Tax_MTable extends Manage_Table_SQL
{
    /**
     * @brief Build and returns an object
     * @param int $p_id
     */
    static public function  build($p_id=-1)
    {
        $cn=Dossier::connect();
        $object_sql=new Acc_Other_Tax_SQL($cn,$p_id);

        $object=new Acc_Other_Tax_MTable($object_sql);
        $object->set_object_name("other_tax_ctl");
        $object->set_callback("ajax_misc.php");
        $object->add_json_param("op","other_tax");
        $object->set_order(["ac_label","ac_rate","ac_accounting","ajrn_def_id"]);

        $object->set_col_label("ac_label",_("Nom"));
        $object->set_col_label("ajrn_def_id",_("Journaux"));
        $object->set_col_label("ac_rate",_("Taux"));
        $object->set_col_tips("ac_rate",82);
        $object->set_col_label("ac_accounting",_("Poste comptable"));
        $object->set_col_tips("ac_accounting",81);
        $object->set_col_type("ac_accounting","custom");
        $object->set_col_type("ajrn_def_id","custom");
        $object->set_property_visible("ajrn_def_id",false);
        return $object;
    }
    function input_custom($p_key,$p_value) {
        switch ($p_key) {
            case "ac_accounting":
                $accounting=new IPoste("ac_accounting",$p_value);
                $accounting->set_attribute('gDossier',Dossier::id());
                $accounting->set_attribute('jrn',0);
                $accounting->set_attribute('account','ac_accounting');
                echo  $accounting->input();
                break;
            case "ajrn_def_id":
                $cn=Dossier::connect();
                $a_ledger=$cn->get_array("select 
                        jrn_def_id,jrn_def_name ,
                       coalesce ( (select array_position(ajrn_def_id,jrn_def_id) from acc_other_tax
                           where ac_id=$1
                           ),0) as in_array
                        from jrn_def
                        where 
                            jrn_enable=1
                          and jrn_def_type in ('ACH','VEN') 
                          order by jrn_def_name",[$this->get_table()->get("ac_id")]);
                if (empty($a_ledger) ) {
                    echo _("Aucun journal disponible");
                    return;
                }
                $nb_ledger=count($a_ledger);
                echo '<ul class="tab_row">';
                for ($i=0;$i<$nb_ledger;$i++) {
                    $icheckbox=new ICheckBox("check[]",$a_ledger[$i]['jrn_def_id']);
                    if ( $a_ledger[$i]['in_array']!=0) {
                        $icheckbox->set_check($a_ledger[$i]['jrn_def_id']);
                    }
                    echo '<li>',
                        $icheckbox->input(),
                        h($a_ledger[$i]['jrn_def_name']),
                        '</li>';

                }
                echo '</ul>';
                break;
        }
    }
    function display_row_custom($p_key, $p_value, $p_id = 0)
    {
        $cn=Dossier::connect();
        switch ($p_key)
        {
            case 'ac_accounting':
                $label=$cn->get_value("select pcm_lib from tmp_pcmn where pcm_val=$1",
                [$p_value]);
                echo '<td>',
                    h($p_value),
                    " ",
                    h($label),
                    '</td>';
                break;
        }

    }

    function check()
    {
        $row=$this->get_table();
        $cn=$row->get_cn();

        if ( trim($row->getp("ac_rate")) == "") {
            $row->setp("ac_rate",0);
        }

        if ( $row->getp("ac_rate")>100 || $row->getp("ac_rate")< 0  ) {
            $this->set_error("ac_rate",_("Valeur invalide"));
        }

        $accounting=$row->getp("ac_accounting");
        $nb_accounting=$cn->get_value("select count(*) from tmp_pcmn where pcm_val =format_account($1)",
            [$accounting]);
        if (empty($accounting)||$nb_accounting == 0) {
            $this->set_error("ac_accounting",_("Poste comptable inexistant"));
        }

        $ledger=$row->get("ajrn_def_id");
        if ( $ledger != "{}" && ! empty ($ledger))
        {
            $ledger=trim($ledger,'{');
            $ledger=trim($ledger,'}');
            $a_ledger=explode(",",$ledger);

            $nb_ledger=count($a_ledger);
            $pk=$row->get("ac_id");
            for ($i=0;$i<$nb_ledger;$i++) {
                if ($cn->get_value("select count(*) from acc_other_tax 
                where 
                array_position(ajrn_def_id,$1) is not null
                and ac_id != $2
                 ",[$a_ledger[$i],$pk]) > 0)
                {
                    $this->set_error("ajrn_def_id",_("Journal déjà utilisé dans autre taxe"));
                }
            }
        }


        if ($this->count_error()>0) {
            return false;
        }
        return true;
    }
    function input()
    {
        $this->set_property_visible("ajrn_def_id",true);
        return parent::input();
    }

    function from_request()
    {
        parent::from_request(); // TODO: Change the autogenerated stub
        $http=new HttpInput();
        $this->table->set("ajrn_def_id","{".join(",",$http->post("check","array",array()))."}");
    }
}
