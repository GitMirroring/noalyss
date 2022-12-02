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
 * @file
 * @brief  Manage the anticipation, prediction of sales, expense, bank...
 *
 */
/**
 * @class
 * @brief Manage the anticipation of expense, sales,...
 * @see Forecast Forecast_Cat Forecast_Item
 *
 */
require_once NOALYSS_INCLUDE . '/database/forecast_sql.class.php';

class Anticipation
{
    /* example private $variable=array("val1"=>1,"val2"=>"Seconde valeur","val3"=>0); */
    private $cn;
    var $cat; /*!< array of object categorie (forecast_category)*/
    var $item; /*< array of object item (forecast_item) */
    private $forecast_id; //!< forecast.f_id

    /**
     * @brief constructor
     * @param $p_init Database object
     */
    function __construct($p_init, $p_id = 0)
    {
        $this->cn = $p_init;
        $this->forecast_id = $p_id;
    }

    /**
     * @return mixed
     */
    public function getCat()
    {
        return $this->cat;
    }

    /**
     * @param mixed $cat
     */
    public function setCat($cat)
    {
        $this->cat = $cat;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return int|mixed
     */
    public function getForecastId()
    {
        return $this->forecast_id;
    }

    /**
     * @param int|mixed $forecast_id
     */
    public function setForecastId($forecast_id)
    {
        $this->forecast_id = $forecast_id;
    }

    public function input_title()
    {
        require_once NOALYSS_TEMPLATE . "/anticipation-input_title.php";
    }

    public function input_category()
    {
        $visible=uniqid();
        echo '<h2>';
        echo _("Catégorie");
        echo '<span style="margin-left:5em;font-style:normal;font-size:150%">';
        echo Icon_Action::less($visible, "");
        echo '</span>';
        echo '</h2>';
        
        echo '<div id="anticipation_input_category" >';
        $forecast_category = Forecast_Category_MTable::build();
        $forecast_category->set_forecast_id($this->forecast_id);
        $forecast_category->display_table(" where f_id = $1"
            . " order by fc_order",
            [$this->forecast_id]);
        echo $forecast_category->create_js_script();
        echo '</div>';
        echo <<<EOF
        <script>
        var visible=true;
        var icon=document.getElementById('{$visible}');
icon.onclick=function () {
    
   if ( visible ) { visible = false; $("anticipation_input_category").hide();icon.innerHTML="&#xe824;"}
    else { visible = true;$("anticipation_input_category").show();icon.innerHTML="&#xe827;"}
    }
</script>
EOF;

    }

    /**
     * @brief  display the detail for the forecast , for modifying item, category or name
     *
     */
    public function input_form()
    {
        $this->input_title();
            
        $this->input_category();
        $http=new HttpInput();
        $ac=$http->request("ac");
        echo h2(_("Eléments"));
        $forecast_item = Forecast_Item_MTable::build();
        $forecast_item->set_forecast_id($this->forecast_id);
        $forecast_item->display_table();
        echo '<hr>';
        $href="do.php?".http_build_query(array('ac'=>$ac, 'sa'=>'vw','gDossier'=>Dossier::id(),'f_id'=>$this->forecast_id));
        
        
        $form=dossier::hidden();
        $form.=HtmlInput::hidden('action', '');
        $form.=HtmlInput::hidden('f_id', $this->getForecastId());
        $form.=HtmlInput::hidden('ac', $ac);
        
        echo  '<form method="get" id="form_del" onsubmit="return confirm_box(this,content[\'47\'])" style="display:inline-block">';
        echo $form;
        echo HtmlInput::hidden("action","del");
        echo HtmlInput::submit('del_bt', _('Effacer'));
        echo '</form>';
        
        echo  '<form method="get" id="form_clone" onsubmit="return confirm_box(this,content[\'47\']) " style="display:inline-block" >';
        echo $form;
        echo HtmlInput::hidden("action","clone");
        echo HtmlInput::submit("clone_bt",_("Clone"));
        echo '</form>';
        
        echo HtmlInput::button_anchor(_("Retour"), $href);
                
    }

    /**
     * @brief Display the result of the forecast
     * @param $p_periode
     * @return HTML String with the code
     */
    public function display()
    {
        bcscale(4);
        $forecast = new Forecast_SQL($this->cn, $this->forecast_id);
        $forecast->load();
        $str_name = h($forecast->getp('f_name'));

        $start = $forecast->getp('f_start_date');
        $end = $forecast->getp('f_end_date');

        if ($start == '') throw new Exception (_('Période de début non valable'));
        if ($end == '') throw new Exception (_('Période de fin non valable'));

        $per = new Periode($this->cn, $start);
        $str_start = format_date($per->first_day());

        $per = new Periode($this->cn, $end);
        $str_end = format_date($per->last_day());


        $r = "";
        $aCat = $this->cn->get_array('select fc_id,fc_desc from forecast_category where f_id=$1 order by fc_order',
                array($this->forecast_id));
        $aItem = array();
        $aReal = array();
        $poste = new Acc_Account_Ledger($this->cn, 0);

        $aPeriode = $this->cn->get_array("select p_id,to_char(p_start,'MM.YYYY') as myear from parm_periode
	                                 where p_start >= (select p_start from parm_periode where p_id=$1)
                                         and p_end <= (select p_end from parm_periode where p_id=$2)
					 order by p_start",[$start,$end]);
        $error = array();
        for ($j = 0; $j < count($aCat); $j++) {
            
            // Item of the category,montly estimation
            $aItem[$j] = $this->cn->get_array('select fi_account,fi_text,fi_amount,
                fi_amount_initial
                   from forecast_item where fc_id=$1  and fi_pid=0 order by fi_order ', 
                    array($aCat[$j]['fc_id']));
            
            // Item of the category,  estimation for a specific month
            $aPerMonth[$j] = $this->cn->get_array('select fi_pid,fi_account,fi_text,fi_amount
                    from forecast_item where fc_id=$1 and fi_pid !=0 order by fi_order ', 
                    array($aCat[$j]['fc_id']));

            /* compute the real amount for periode */
            for ($k = 0; $k < count($aItem[$j]); $k++) {
                /* for each periode */
                for ($l = 0; $l < count($aPeriode); $l++) {
                    if ($aItem[$j][$k]['fi_account'] != '') {
                       
                        $poste->id = $aItem[$j][$k]['fi_account'];
                        $aresult = Impress::parse_formula($this->cn, "OK", $poste->id, $aPeriode[$l]['p_id'], $aPeriode[$l]['p_id']);
                        $tmp_label = $aresult['desc'];
                        $amount['solde'] = $aresult['montant'];

                        if ($tmp_label != 'OK') { 
                            $error[] = "<li> " . $aItem[$j][$k]['fi_text'] . $poste->id . '</li>';
                        }
                    }
                    $aReal[$j][$k][$l] = $amount['solde'];
                }
            }

        }
        if ( DEBUGNOALYSS < 2 ) { ob_start(); }
        require_once NOALYSS_TEMPLATE . '/anticipation-display.php';
        $r .= ob_get_contents();
        if ( DEBUGNOALYSS < 2 )  { ob_end_clean(); }
        return $r;
    }

    public static function div()
    {
        $r = '<div id="div_anti" style="display:none">';
        $r .= '</div>';
        return $r;
    }

    /**
     * @brief Clone completely Forecast and returns the new forecast_id
     * @return type
     */
    public function object_clone()
    {
        $this->cn->start();
        /* save into the table forecast */
        $sql = "insert into forecast(f_name,f_start_date,f_end_date) select 'clone '||f_name,f_start_date,f_end_date 
                from forecast 
                where f_id=$1 returning f_id";
        $new = $this->cn->get_value($sql, array($this->forecast_id));

        /* save into forecast_cat */
        $sql = "insert into forecast_category(fc_desc,f_id,fc_order) 
                select fc_desc,$1,fc_order 
                from forecast_category
                where f_id=$2 returning fc_id";
        $array = $this->cn->get_array($sql, array($new, $this->forecast_id));

        $old = $this->cn->get_array("select fc_id from forecast_category where f_id=$1", array($this->forecast_id));
        /* save into forecast_item */
        for ($i = 0; $i < count($array); $i++) {
            $this->cn->exec_sql("insert into forecast_item (fi_text,fi_account,fi_order,fc_id,
                           fi_amount,fi_pid) " .
                " select fi_text,fi_account,fi_order,$1,fi_amount,fi_pid " .
                " from forecast_item where fc_id=$2", array($array[$i]['fc_id'], $old[$i]['fc_id']));
        }
        $this->cn->commit();
        return $new;
    }

}

?>
