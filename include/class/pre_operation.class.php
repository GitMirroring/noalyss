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

/*!\file
 * \brief definition of Pre_operation
 */

/*! \brief manage the predefined operation, link to the table op_def
 * and op_def_detail
 *
 */
require_once NOALYSS_INCLUDE.'/lib/iselect.class.php';
require_once NOALYSS_INCLUDE.'/lib/ihidden.class.php';
require_once NOALYSS_INCLUDE.'/class/pre_op_ach.class.php';
require_once NOALYSS_INCLUDE.'/class/pre_op_ven.class.php';
require_once NOALYSS_INCLUDE.'/class/pre_op_advanced.class.php';
class Pre_operation
{
    private $db;						/*!< $db database connection */
    private  $nb_item;					/*!< $nb_item nb of item */
    private  $p_jrn;					/*!< $p_jrn jrn_def_id */
    private  $jrn_type;					/*!< $jrn_type */
    private  $name;						/*!< $name name of the predef. operation */
    private  $detail;                /*!< Pre_operation_detail object */
    private  $od_direct ;    /*!< Compatibility for ACH in direct mode, only for ODS */
    private  $od_id;         /*!< id of the Predefined Operation */
    private $isloaded;
    private $description; /*!< description of the predefined operation */
    function __construct($cn,$p_id=0)
    {
        $this->db=$cn;
        $this->od_direct='f';
        $this->od_id=$p_id;
        $this->p_jrn=0;
        $this->jrn_type='x';
        $this->name='';
        $this->isloaded=false;
        $this->description="";

    }

    /**
     * @brief Propose to save the operation into a predefined operation
     * @return HTML  string
     */
    static function save_propose() {
        $r="";
        $r.= '<p class="decale">';
        $r.= _("Donnez un nom pour sauver cette opération comme modèle")." <br>";
        $opd_name = new IText('opd_name');
        $r.=_( "Nom du modèle " ) . $opd_name->input();
        $opd_description=new ITextarea('od_description');
        $opd_description->style=' class="itextarea" style="width:30em;height:4em;vertical-align:top"';
        $r.='</p>';
        $r.= '<p class="decale">';
        $r.= _('Description (max 50 car.)');   
        $r.='<br>';
        $r.=$opd_description->input();
        $r.='</p>';
        return $r;
    }
    /**
     * @return string
     */
    public function get_description()
    {
        return $this->description;
    }/**
     * @param string $description
     */
    public function set_description($description)
    {
        $this->description = $description;
        return $this;
    }


    /*!\brief fill the object with the $_POST variable */
    function get_post()
    {
        $http=new HttpInput();
        $this->nb_item=$http->post('nb_item',"number");
        $this->p_jrn=$http->request('p_jrn',"number");
        $this->jrn_type=$http->post('jrn_type');
	    $this->name=$http->post('opd_name');

        $this->description= $http->post('od_description');
        if ( $this->name=="")
        {
            $n=$this->db->get_next_seq('op_def_op_seq');
            $this->name=$this->jrn_type.$n;

        }

        // get also info for the details
        // common value
        $this->detail=Pre_operation_detail::build_detail($this->jrn_type,$this->db);
        $this->detail->get_post();
    }

    /**
     * delete a template operation and children
     */
    function delete ()
    {
        $sql="delete from op_predef where od_id=$1";
        $this->db->exec_sql($sql,array($this->od_id));
    }
    function save()
    {
        if ($this->od_id  < 1) {
            $this->save_insert();
        } else {
            $this->save_update();
        }

    }
    function save_update()
    {
        $sql = "update op_predef set jrn_def_id = $1 , od_name = $2 , 
                        od_item =$3, od_description = $4 where od_id=$5";
        $this->db->exec_sql($sql,array($this->p_jrn,$this->name,$this->nb_item,$this->description,$this->od_id));
        // delete detail&
        $this->db->exec_sql("delete from op_predef_detail where od_id = $1",array($this->od_id));
        $this->detail->save($this->od_id,$this->nb_item);
    }
    /*!\brief save the predef check first is the name is unique
     * \return true op.success otherwise false
     */
    function save_insert()
    {

        if (	$this->db->count_sql("select * from op_predef ".
                                  "where upper(od_name)=upper('".Database::escape_string($this->name)."')".
                                  "and jrn_def_id=".$this->p_jrn." and od_id <> ".$this->od_id)
                != 0 )
        {
            $this->name="copy_".$this->name."_".microtime(true);
        }
        if ( $this->count()  > MAX_PREDEFINED_OPERATION )
        {
            echo '<span class="notice">'.("Vous avez atteint le max. d'opération prédéfinie, désolé").'</span>';
            return false;
        }
        try {
            $this->db->start();
            $sql='insert into op_predef (jrn_def_id,od_name,od_item,od_jrn_type,od_direct,od_description)'.
                'values'.
                "($1,$2,$3,$4,$5  ,$6               )";
            $this->db->exec_sql($sql,array($this->p_jrn,
                $this->name,
                $this->nb_item,
                $this->jrn_type,
                $this->od_direct,
                $this->description,
            ));
            $this->od_id=$this->db->get_current_seq('op_def_op_seq');


           // $this->detail=Pre_operation_detail::build_detail($this->jrn_type,$this->db);
            $this->detail->save($this->od_id,$this->nb_item);
            $this->db->commit();
        } catch (Exception $e) {
            record_log("PROP139.Failed save predefined operation ");
            $this->db->rollback();
        }

        return true;
    }
    /*!\brief load the data from the database and return an array
     * \return an double array containing all the data from database
     */
    function load()
    {
        $this->isloaded=true;
        //------------------------------------------
        // if new , then od_id == 0 and we need to use blank()
        //------------------------------------------
        if ($this->od_id == -1 ) {
            $array=$this->blank($this->p_jrn);
            return  $array;
        }
        $sql="select od_id,jrn_def_id,od_name,od_item,od_jrn_type,od_description".
             " from op_predef where od_id=$1 ".
             " order by od_name";
        $res=$this->db->exec_sql($sql,[$this->od_id]);
        $array=Database::fetch_all($res);
        foreach (array('jrn_def_id','od_name','od_item','od_jrn_type','od_description') as $field) {
            $this->$field=$array[0][$field];
        }
        $this->detail = Pre_operation_detail::build_detail($this->od_jrn_type, $this->db);
        $array+=$this->detail->load($this->od_id);
        return $array;
    }
    /**
     * create a blank object to insert it later
     * @param $p_ledger_id
     * @throws Exception
     */
    function blank($p_ledger_id) {
        $array["od_id"]=-1;
        $array['jrn_def_id']=$p_ledger_id;
        $array['od_name']="";
        $array['od_item']=2;
        $array['od_jrn_type']=$this->db->get_value("select jrn_def_type from jrn_def where jrn_def_id=$1",[$p_ledger_id]);
        $array['od_description']="";
        foreach (array('jrn_def_id','od_name','od_item','od_jrn_type','od_description') as $field) {
            $this->$field=$array[$field];
        }
        $this->od_jrn_type=$array['od_jrn_type'];

        $this->detail = Pre_operation_detail::build_detail($this->od_jrn_type, $this->db);
        $this->detail->set_od_id(0);
        $this->detail->set_jrn($p_ledger_id);

        return $array;
    }

    function compute_array()
    {
        if ($this->od_id > 0) {
            $p_array = $this->load();
        } else {
            $p_array=$this->blank($this->p_jrn);
        }
        $array=array(
                   "e_comm"=>$p_array[0]["od_name"],
                   "nb_item"=>(($p_array[0]["od_item"]<10)?10:$p_array[0]["od_item"])   ,
                   "p_jrn"=>$p_array[0]["jrn_def_id"],
                   "jrn_type"=>$p_array[0]["od_jrn_type"],
                   "od_description"=>$p_array['0']['od_description']
               );
        $this->detail = Pre_operation_detail::build_detail($this->od_jrn_type, $this->db);
        $array += $this->detail->compute_array($this->od_id);
        return $array;

    }

    /*!\brief show the button for selecting a predefined operation */
    function show_button()
    {

        $select=new ISelect();
        $value=$this->db->make_array("select od_id,od_name from op_predef ".
                                     " where jrn_def_id=".$this->p_jrn.
                                     " and od_direct ='".$this->od_direct."'".
                                     " order by od_name");

        if ( empty($value)==true) return "";
        $select->value=$value;
        $r=$select->input("pre_def");

        return $r;
    }
    /*!\brief count the number of pred operation for a ledger */
    function count()
    {
        $a=$this->db->count_sql("select od_id,od_name from op_predef ".
                                " where jrn_def_id= $1 ".
                                " and od_direct = $2 ".
                                " order by od_name",array($this->p_jrn,$this->od_direct));
        return $a;
    }
    /*!\brief get the list of the predef. operation of a ledger
     * \return string
     */
    function get_list_ledger()
    {
        $sql="select od_id,od_name,od_description from op_predef ".
             " where jrn_def_id= $1 ".
             " and od_direct = $2 ".
             " order by od_name";
        $res=$this->db->exec_sql($sql,array($this->p_jrn,$this->od_direct));
        $all=Database::fetch_all($res);
        return $all;
    }

    /**
     * 
     * @brief display the detail of predefined operation, normally everything 
     * is loaded
     */
    function display() 
    {
        $array=$this->compute_array();
        require NOALYSS_TEMPLATE."/pre_operation_display.php";
        echo $this->detail->display($array);
    }
    /*!\brief show a form to use pre_op
  */
    function form_get ($p_url)
    {
        $r=HtmlInput::button_action(_("Modèle d'opérations"),
            ' $(\'modele_op_div\').style.display=\'block\';if ( $(\'lk_modele_op_tab\')) { $(\'lk_modele_op_tab\').focus();}');
        $r.='<div id="modele_op_div" class="noprint">';
        $r.=HtmlInput::title_box(_("Modèle d'opérations"), 'modele_op_div', 'hide',"","n");
        $hid=new IHidden();
        $r.=$hid->input("action","use_opd");
        $r.=$hid->input("jrn_type",$this->jrn_type);
        $r.= $this->display_list_operation($p_url);
        $r.=' <p style="text-align: center">'.
            HtmlInput::button_hide('modele_op_div').
            '</p>';
        $r.='</div>';
        return $r;

    }

    /*!\brief show the button for selecting a predefined operation */
    function display_list_operation($p_url)
    {


        $value=$this->db->get_array("select od_id,od_name,od_description from op_predef ".
            " where jrn_def_id=$1".
            " and od_direct =$2".
            " order by od_name",
            array($this->p_jrn,$this->od_direct ));

        if ( $this->p_jrn=='') $value=array();

        $r="";
        if (count($value)==0) {
            $r.=_("Vous n'avez encore sauvé aucun modèle");
            return $r;
        }
        $r.=_('Cherche').' '.HtmlInput::filter_table('modele_op_tab', '0,1', '0');
        $r.='<table style="width:100%" id="modele_op_tab">';
        for ($i=0;$i<count($value);$i++) {
            $r.='<tr class="'.(($i%2==0)?"even":"odd").'">';
            $r.='<td style="font-weight:bold;vertical-align:top;text-decoration:underline">';
            $r.=sprintf('<a href="%s&pre_def=%s" onclick="waiting_box()">%s</a> ',
                $p_url,$value[$i]['od_id'],$value[$i]['od_name']);
            $r.='</td>';
            $r.='<td>'.h($value[$i]['od_description']).'</td>';
            $r.='</tr>';
        }
        $r.='</table>';
        return $r;
    }
    public function   get_operation()
    {
        if ( $this->jrn_def_id=='') return array();
        $value=$this->db->make_array("select od_id,od_name from op_predef ".
            " where jrn_def_id=".sql_string($this->jrn_def_id).
            " and od_direct ='".sql_string($this->od_direct)."'".
            " order by od_name",1);
        return $value;
    }

    /**
     * @return mixed
     */
    public function get_db()
    {
        return $this->db;
        return $this;
    }

    /**
     * @param mixed $db
     */
    public function set_db($db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_nb_item()
    {
        return $this->nb_item;
        return $this;
    }

    /**
     * @param mixed $nb_item
     */
    public function set_nb_item($nb_item)
    {
        $this->nb_item = $nb_item;
        return $this;
    }

    /**
     * @return string
     */
    public function get_jrn_type()
    {
        return $this->jrn_type;
        return $this;
    }

    /**
     * @param string $jrn_type
     */
    public function set_jrn_type($jrn_type)
    {
        if ( ! in_array ($jrn_type,['ACH','FIN','VEN','ODS'] )) throw new Exception('prop03.invalid ledger type');
        $this->jrn_type = $jrn_type;
        return $this;
    }

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->name;
        return $this;
    }

    /**
     * @param string $name
     */
    public function set_name($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array
     */
    public function get_detail()
    {
        return $this->detail;
        return $this;
    }

    /**
     * @param array $detail
     */
    public function set_detail(Pre_operation_detail $detail)
    {
        $this->detail = $detail;
        return $this;
    }

    /**
     * @return string
     */
    public function get_od_direct()
    {
        return $this->od_direct;
        return $this;
    }

    /**
     * @param string $od_direct
     */
    public function set_od_direct($od_direct)
    {
        if ( !   in_array($od_direct,['f','t']))  throw new Exception('prop02.invalid od_direct');
        $this->od_direct = $od_direct;
        return $this;
    }

    /**
     * @return int|mixed
     */
    public function get_od_id()
    {
        return $this->od_id;
    }

    /**
     * @param int|mixed $od_id
     */
    public function set_od_id($od_id)
    {
        $this->od_id = $od_id;
        return $this;
    }


    /*!\brief set the ledger
     * \param $p_jrn is the ledger (jrn_id)
     */
    function set_p_jrn($p_jrn)
    {
        $this->p_jrn=$p_jrn;
        $this->jrn_type=$this->db->get_value("select jrn_def_type from jrn_def where jrn_def_id=$1",[$p_jrn]);
        return $this;
    }
}

/*!\brief mother of the pre_op_XXX, it contains only one data : an
 * object Pre_Operation. The child class contains an array of
 * Pre_Operation object
 */
class Pre_operation_detail
{
    function __construct($p_cn)
    {
        $this->db=$p_cn;

    }

    static function build_detail($p_jrn_type,Database $database)
    {
        switch ($p_jrn_type) {
            case 'ACH':
                $detail=new Pre_op_ach($database);
                break;
            case 'VEN':
                $detail=new Pre_Op_ven($database);
                break;
            case 'ODS':
                $detail=new Pre_op_advanced($database);
                break;
            default:
                throw new Exception(sprintf(_('Echec PreOperatoin chargement %s'),$p_jrn_type));
        }
        return $detail;
    }

}
