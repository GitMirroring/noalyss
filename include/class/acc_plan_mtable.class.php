<?php

/*
 * Copyright (C) 2017 Dany De Bontridder <dany@alchimerys.be>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


/***
 * @file 
 * @brief class Acc_Plan_MTabme
 * @see Acc_Plan_MTabme
 *
 */
require_once NOALYSS_INCLUDE.'/database/acc_plan_sql.class.php';
/**
 * @brief this instance extends Manage_Table_SQL and aims to manage 
 * the Table tmp_pcmn thanks a web interface (add , delete, display...)
 * 
 * @see Acc_Plan_SQL
 */
class Acc_Plan_MTable extends Manage_Table_SQL
{
    function __construct(Acc_Plan_SQL $p_table)
    {
        $this->table = $p_table;
        parent::__construct($p_table);
        //--------------------------------------------------------------
        //Set the table header 
        //--------------------------------------------------------------
        $this->set_col_label("pcm_val", _("Poste Comptable"));
        $this->set_col_label("pcm_type", _("Type"));
        $this->set_col_label("pcm_lib", _("Libellé"));
        $this->set_col_label("parent_accounting", _("Dépend"));
        $this->set_col_label("fiche_qcode", _("Fiche"));
        $this->set_col_label("pcm_direct_use", _("Utilisation directe"));
        //--------------------------------------------------------------
        $this->set_property_visible("id", FALSE);
        $this->set_property_updatable("fiche_qcode", FALSE);
        $this->set_col_type("pcm_type", "select", [
            ["label"=>_("Actif"),"value"=>"ACT"],
            ["label"=>_("Actif inversé"),"value"=>"ACTINV"],
            ["label"=>_("Passif"),"value"=>"PAS"],
            ["label"=>_("Passif Inversé"),"value"=>"PASINV"],
            ["label"=>_("Charge"),"value"=>"CHA"],
            ["label"=>_("Charge inversé"),"value"=>"CHAINV"],
            ["label"=>_("Produit"),"value"=>"PRO"],
            ["label"=>_("Produit inversé"),"value"=>"PROINV"],
            ["label"=>_("Contexte"),"value"=>"CON"]
        ]);
        $this->set_col_type("pcm_direct_use", "select",
            array(["label" => _("Oui"), "value" => "Y"], ["label" => "Non", "value" => "N"]));
        $this->set_col_type("pcm_val", "custom");
        $this->a_order = ["pcm_val", "pcm_lib", "parent_accounting", "pcm_direct_use", "pcm_type", "fiche_qcode"];
        $this->set_icon_mod("first");
        $this->set_dialogbox_style(["width"=>"auto"]);
    }

    /**
     * Display a row
     * @param type $p_row array of value key column=>value
     */
    function display_row($p_row)
    {
         printf('<tr  id="%s_%s">',
                 $this->object_name,
                $p_row[$this->table->primary_key])
        ;

        $dossier_id=Dossier::id();
        $nb_order=count($this->a_order);
        for ($i=0; $i<$nb_order; $i++)
        {
            $v=$this->a_order[$i];
            $nb=0;
            $cn=Dossier::connect();
            $nb_used=$cn->get_value("select count(*) from jrnx where j_poste=$1",[$p_row['pcm_val']]);
            $nb_plan=$cn->get_value("select count(*) from tmp_pcmn where pcm_val_parent=$1",[$p_row['pcm_val']]);
            $nb=$nb_used+$nb_plan;
            if ($v=="pcm_val")
            {
                $js=sprintf("onclick=\"%s.input('%s','%s');\"", $this->object_name,
                        $p_row[$this->table->primary_key], $this->object_name);
                echo sprintf('<td sort_type="text" sort_value="X%s">%s',
                        htmlspecialchars($p_row[$v]),
                        HtmlInput::anchor($p_row[$v], "", $js)).
                        '</td>';
            }
            elseif ($v == "fiche_qcode") {
                $count=$this->table->cn->get_value("select count(*) from fiche_detail where ad_id=5 and ad_value=$1",array($p_row['pcm_val']));
               if ($count ==  0) echo td("");
               elseif ($count == 1 ) {
                   echo '<td>';
                   echo HtmlInput::card_detail($p_row[$v]) ;
                   echo '</td>';

               }
               elseif ($count > 1) {
                   echo '<td>';
                   $a_code=explode(",",$p_row[$v]);
                   $nb_code=count($a_code);
                   for ($xx = 0;$xx < $nb_code;$xx++)
                   {
                       echo HtmlInput::card_detail($a_code[$xx])."," ;
                   }
                   echo  " ($count) ";
                   echo Icon_Action::more(uniqid(), sprintf("display_all_card('%s','%s')",$dossier_id,$p_row["pcm_val"]));
                   echo '</td>';

               }
            }
            elseif ($v=="pcm_lib")
            {

                if ( $nb >0){
                    echo "<td>";
                    if ($nb_used > 0) {
                        $used=sprintf (' (%s)',$nb_used);
                        echo HtmlInput::history_account($p_row['pcm_val'],h($p_row["pcm_lib"].$used));
                    } else {
                        echo h($p_row["pcm_lib"]);
                    }
                    echo "</td>";
                } else {
                    echo td($p_row[$v]);
                }

            }
            else
            {
                if ( ! $this->get_property_visible($v)) continue;
                echo td($p_row[$v]);
            }
        }
        if ( $nb == 0 ) $this->display_icon_del($p_row);
        else echo td("&nbsp;");


        echo '</tr>';
    }

    /**
     * Check that the entered data are valid before recording them into
     * tmp_pcmn, the errors are stored into this->a_error and if someting wrong
     * is found it returns false, if the data can be saved it returns true
     * @return return false if an error is found,
     */
    function check()
    {
        $cn=Dossier::connect();
        $count=$cn->get_value("select count(*) from tmp_pcmn where pcm_val = $1 and id <> $2",
                    array($this->table->pcm_val,$this->table->id));
        if ($count > 0 ) {
            $this->set_error("pcm_val", _("Poste comptable est unique"));
        }
        if ( trim($this->table->pcm_val) == "") {
            $this->set_error("pcm_val", _("Poste comptable ne peut être vide"));
        }
        // Check size
         if ( strlen(trim($this->table->pcm_val)) > 40) {
            $this->set_error("pcm_val", _("Poste comptable trop long"));
        }
        if ( trim($this->table->parent_accounting) == "") {
            $this->set_error("parent_accounting", _("Poste comptable dépendant ne peut pas être vide"));
        }
        /**
         * Check that the parent accounting does exist
         */
        $exist_parent=$cn->get_value("select count(*) from tmp_pcmn where pcm_val = $1 ",
                    array($this->table->parent_accounting));
        if ($exist_parent == 0) {
            $this->set_error("parent_accounting", _("Compte parent n'existe pas"));
        }
        /**
         * check that accounting is not already used
         */
        $old_accounting = $cn->get_value("select pcm_val from tmp_pcmn where id = $1",
            array($this->table->id));
        // it is not a new accounting and is different
        if ($old_accounting != "" && $old_accounting != $this->table->pcm_val) {
            // count it is used
            if ($cn->get_value("select count(*) from jrnx where j_poste=$1",
                    [$old_accounting]) > 0) {
                $this->set_error("pcm_val", _("Poste utilisé"));
            }
        }
        if (count($this->aerror) > 0) return false;
        return true;
    }

    /**
     * @brief display into a dialog box the datarow in order
     * to be appended or modified. Can be override if you need
     * a more complex form
     */
    function input()
    {
        parent::input();
        $dossier_id=Dossier::id();
        echo HtmlInput::button_action(_("Toutes les fiches") , sprintf("display_all_card('%s','%s')",$dossier_id,$this->table->pcm_val));
    }

    /**
     * if pcm_val already used then it cannot be modified
     *
     * @param $p_key always pcm_val
     * @param $p_value current value of pcm_val
     * @return nothing|void
     */
    function input_custom($p_key, $p_value)
    {
        $readonly=true;
        $cn=Dossier::connect();
        if (
             $p_value == "" ||
            ($p_value !="" && $cn->get_value("select count(*) from jrnx where j_poste=$1",[$p_value]) == 0  )
            )
        {
            $readonly=false;
        }
        $text=new IText($p_key);
        $text->setReadOnly($readonly);
        $text->value=$p_value;
        $min_size=(strlen($p_value)<30)?30:strlen($p_value)+5;
        $text->size=$min_size;
        echo $text->input();
    }

}
