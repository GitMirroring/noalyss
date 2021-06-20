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

// Copyright Author Dany De Bontridder danydb@noalyss.eu

/**
 * @file
 * @brief display the item for forecast
 */


/**
 * @file
 * @brief display the item for forecast
 */

require_once NOALYSS_INCLUDE . "/database/forecast_item_sql.class.php";
require_once NOALYSS_INCLUDE . "/database/forecast_sql.class.php";

class Forecast_Item_MTable extends Manage_Table_SQL
{
    private $forecast_id;

    public function __construct(Data_SQL $p_table)
    {
        parent::__construct($p_table);
        $this->forecast_id = -1;
    }

    static function build($p_id = -1)
    {
        $cn = Dossier::connect();
        $forecast_sql = new Forecast_Item_SQL($cn, $p_id);
        $forecast_item = new Forecast_Item_MTable($forecast_sql);
        $forecast_item->add_json_param('ac', 'FORECAST');
        $forecast_item->add_json_param("op", "forecast_item");
        $forecast_item->set_callback("ajax_misc.php");
        if ( $p_id != -1 ) {
            $forecast_id=$cn->get_value("select f_id 
                            from forecast_item fi
                                join forecast_category fc on (fi.fc_id=fc.fc_id) 
                            where 
                            fi_id=$1",array($p_id));
            $forecast_item->set_forecast_id($forecast_id);
        }
        return $forecast_item;

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
    public function set_forecast_id(int $forecast_id)
    {
        $this->forecast_id = $forecast_id;
        $this->add_json_param("forecast_id", $forecast_id);
    }
    function count_category()
    {
        $object=$this->get_table();
        $nCategory = $object->cn->get_value("select count(*) from forecast_category where f_id=$1",[$this->forecast_id]);
        return $nCategory;
        
    }
    /**
     * Display list of items, if there is no category the list is empty and you cannot add new ones
     * @param type $p_string
     * @param type $p_array
     */
    public function display_table($p_string = "", $p_array = NULL)
    {
        
        $sql = "select fi_id,fi_text,fi_account,
                fi_account str_account_card,
                fc_desc,
                fi.fi_amount ,
                (select p_start from parm_periode pp where pp.p_id=fi.fi_pid) as str_periode
                from forecast_item fi join forecast_category fc on (fi.fc_id=fc.fc_id)
                where fc.f_id =$1
                order by fc_desc,fi_text ";
        $a_row = $this->get_table()->cn->get_array($sql, array($this->forecast_id));
        require_once NOALYSS_TEMPLATE . "/forecast_item_mtable-display_table.php";
    }

    public function display_row($p_row)
    {
        if ( ! isset($p_row['fc_desc'])) {
            $cn=$this->get_table()->cn;
         $sql=   "select fi_id,fi_text,fi_account,
                fi_account                 str_account_card,
                fc_desc,
                fi.fi_amount ,
                (select p_start from parm_periode pp where pp.p_id=fi.fi_pid) as str_periode
                from forecast_item fi join forecast_category fc on (fi.fc_id=fc.fc_id)
                where fi_id=$1";
            $p_row=$cn->get_row($sql,[$p_row['fi_id']]);

        }
        if ( ! isset ($p_row['str_account_card'])) {
            $p_row['str_account_card']="";
        }
        if ( !isset ($p_row['str_periode'])){
            $p_row['str_periode']="";
        }
        printf('<tr  id="%s_%s">', $this->object_name, $p_row['fi_id']);
        print td($p_row['fc_desc']);
        print td($p_row['fi_text']);
        print td($p_row['str_account_card']);
        print td(nbm($p_row['fi_amount']));
        print td($p_row['str_periode']);


        $jsdel = sprintf("%s.remove('%s','%s');",
            $this->object_name,
            $p_row['fi_id'],
            $this->object_name
        );
        $jsupd = sprintf("%s.input('%s','%s');",
            $this->object_name,
            $p_row['fi_id'],
            $this->object_name
        );
        echo '<td>' . Icon_Action::modify(uniqid(), $jsupd);
        echo '</td>';
        echo '<td>';
        echo Icon_Action::trash(uniqid(), $jsdel);
        echo '</td>';
        echo '</tr>';

    }

    public function input()
    {
        $object=$this->get_table();
        if ( $this->count_category() == 0 ) {
            echo span(_("Sans catégorie il n'est pas possible d'ajouter de nouveaux éléments"),'class="notice"');
        return false;
        }

        require_once NOALYSS_TEMPLATE."/forecast_item_mtable-input.php";
        return true;
    }

    /**
     * forecast_id: 14
    p_id: 143
    action: save
    ctl: tbl6058da883ded6
    fc_id: 88
    fi_text: Gérant
    fi_account: [4160%]-[4890%]
    fi_amount: 3000.0000
    fi_pid: 0
     */
    public function from_request()
    {
        $http=new HttpInput();
        $this->set_forecast_id($http->post("forecast_id","number"));
        $object_sql=$this->get_table();
        $object_sql->setp("fi_text",$http->post("fi_text"));
        $object_sql->setp("fc_id",$http->post('fc_id',"number"));
        $object_sql->setp("fi_account",$http->post('fi_account'));
        $object_sql->setp("fi_amount",$http->post('fi_amount',"number"));
        $object_sql->setp("fi_pid",$http->post('fi_pid',"number"));
        $object_sql->setp("fi_amount_initial",$http->post("fi_amount_initial","number"));
        
        

    }
    function check()
    {
        $object=$this->get_table();
        if ( trim($object->getp("fi_text") ) == "") {
            $this->set_error("fi_text", _("Intitulé est vide"));
        }
        if ( trim ($object->getp("fi_account"))=="") {
            $this->set_error("fi_account", _("Formule est vide"));
        }
        if ( trim ($object->getp("fi_account"))=="") {
            $this->set_error("fi_account", _("Formule est vide"));
        }
         if ( ! Impress::check_formula($object->getp("fi_account"))) {
            $this->set_error("fi_account", _("Formule invalide"));
        }
        if ( $this->count_error()==0) return true;
        return false;
    }
}