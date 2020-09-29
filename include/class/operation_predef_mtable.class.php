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
/*! \file
 * \brief Class to manage predefined operation thanks the class Manage Table
 */
/*!
 * \brief Display a table and allow to change the predefined operation, insert or delete. Used the
 * class Manage_Table_SQL and the SQL Object for the tables op_predef and op_predef_detail.
 */
require_once NOALYSS_INCLUDE."/lib/manage_table_sql.class.php";
require_once NOALYSS_INCLUDE."/database/op_predef_sql.class.php";
require_once NOALYSS_INCLUDE."/class/pre_operation.class.php";
require_once NOALYSS_INCLUDE."/lib/select_box.class.php";


class Operation_Predef_MTable extends Manage_Table_SQL
{
    private $pre_operation;
    private $force_ledger_type; //!< When adding , the ledger must be set
    function __construct(Op_predef_SQL $p_table)
    {
        parent::__construct($p_table);

        $this->set_property_visible("od_id",false);
        $this->set_property_visible("od_direct",false);
        $this->set_property_visible("od_item",false);
        $this->set_col_label("jrn_def_id",_("Journal"));
        $this->set_col_label("od_name",_("Nom"));
        $this->set_col_label("od_jrn_type",_("Type de journal"));
        $this->set_col_label("od_description",_("Description"));

        $aLedger=$p_table->cn->make_array("select jrn_def_id,jrn_def_name from jrn_def order by jrn_def_name asc");

        $this->set_sort_column("od_name");
        $this->set_col_sort(1);
        $this->set_order(array("jrn_def_id","od_name","od_description","od_jrn_type"));
        $this->set_col_type("jrn_def_id","select",$aLedger);

        $this->set_callback("ajax_misc.php");
        $this->set_property_updatable("jrn_def_id",false);
        $this->set_property_updatable("od_jrn_type",false);
        $this->pre_operation=null;
        // create our own "Append button"
        $this->set_append_row(false);
        $this->set_dialog_box("prdfop");
        $this->set_dialogbox_style(["position"=>"absolute","top"=>"5%","width:auto","min-width"=>"80%"]);
    }

    /**
     * @return mixed
     */
    public function get_force_ledger_type()
    {
        return $this->force_ledger_type;
    }

    /**
     * When adding the ledger_type must be set
     *  if p_id == -2 then type ACH , 3 for VEN and 4 for ODS
     * @param mixed $force_ledger_type
     */
    public function set_force_ledger_type($force_ledger_type)
    {
        $aLedger=array(-2=>'ACH',-3=>'VEN',-4=>'ODS');
        $this->force_ledger_type = $aLedger[$force_ledger_type];
        return $this;
    }

    /**
     * Display the form
     * @throws Exception
     */
    function input()
    {
        $obj=$this->get_table();
        $od_id=$obj->get("od_id");
        if ($od_id > 0 ) {
            $this->pre_operation = new Pre_operation($obj->cn);
            $this->pre_operation->set_od_id($obj->get("od_id"));
            $this->pre_operation->display();
        } else  {
            // display blanck operation type ledger = ACH
            $this->pre_operation = new Pre_operation($obj->cn);
            // new operation od_id = -1
            $this->pre_operation->set_od_id(-1);
            $this->pre_operation->set_jrn_type($this->get_force_ledger_type());
            $this->pre_operation->display();

        }
    }

    /**
     * Get values from the request
     *  - table is the sql table
     *  - ctl_id is the dialog box id
     *  - p_id = predef_op.od_id
     *
     * @code
     * json = {"table":"public.op_predef",
                "ctl_id":"dtr",
                "ac":"COMPTA/ADV/PREDOP",
                "op":"save_predf",
                "gDossier":["28", "28"],
                "p_id":"15",
                "action":"save",
                "ctl":"tbl5f6a1d09035f2",
                "nb_item":"10",
                "e_client":"",
                "p_jrn":"2",
                "e_march0":"MARCHA",
                "e_march0_price":"102.0000",
                "e_quant0":"1.0000",
                "htva_march0":"0",
                "e_march0_tva_id":"4",
                "bt_e_march0_tva_id":"+TVA+",
                "e_march0_tva_amount":"0.0000",
                "tva_march0":"0","tvac_march0":"0",
                "jrn_type":"VEN",
                "5f6a1d0b23242_ledger":"O","
                5f6a1d0b23242":"1",
                "update":"OK",
                "5f6a1d5b70997_ledger":"O",
                "5f6a1d5b70997":"1"}
     * @endcode
     */
    function from_request()
    {
        $http=new HttpInput();
        $obj=$this->get_table();
        $this->pre_operation=new Pre_operation($obj->cn);
        $this->pre_operation->get_post();
        $this->pre_operation->set_od_id($http->post("p_id","number"));
    }

    function save()
    {
       $this->pre_operation->save();
       $this->set_pk($this->pre_operation->get_od_id());
    }

    /**
     * Display a button to choose a type of operation and call a dialog box for adding
     */
    function display_button_add()
    {
        $select=new Select_Box(uniqid(),_("Ajout"));
        $select->set_position('in-absolute');
        $select->add_javascript(_("Achat"),sprintf("%s.input('-2','%s')",
            $this->get_object_name(),
            $this->get_object_name(), "xx", "smallbutton", BUTTONADD));

        $select->add_javascript(_("Vente"),sprintf("%s.input('-3','%s')",
            $this->get_object_name(),
            $this->get_object_name(), "xx", "smallbutton", BUTTONADD));

          $select->add_javascript(_("Opérations Diverses"),sprintf("%s.input('-4','%s')",
              $this->get_object_name(),
              $this->get_object_name(), "xx", "smallbutton", BUTTONADD));
        echo $select->input();
    }
}