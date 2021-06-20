<?php

//This file is part of NOALYSS and is under GPL 
//see licence.txt
/**
 * @brief Manage the table attr_def
 *
 *
 */
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
require_once NOALYSS_INCLUDE."/database/attr_def_sql.class.php";

class Fiche_Attr extends Attr_def_SQL
{
    /* example private $variable=array("easy_name"=>column_name,"email"=>"column_name_email","val3"=>0); */

    function __construct($p_cn, $p_id=0)
    {
        parent::__construct($p_cn, $p_id);
    }

    public function get_parameter($p_string)
    {
        return $this->getp($p_string);
    }

    public function set_parameter($p_string, $p_value)
    {
        $this->setp($p_string, $p_value);
    }

    public function verify()
    {
        // Verify that the elt we want to add is correct
        /* verify only the datatype */
        if (strlen(trim($this->ad_text))==0)
            throw new Exception('La description ne peut pas être vide', 1);
        if (strlen(trim($this->ad_type))==0)
            throw new Exception('Le type ne peut pas être vide', 1);
        $this->ad_type=strtolower($this->ad_type);
        if (in_array($this->ad_type, array('date', 'text', 'numeric', 'zone', 'poste', 'card', 'select','check'))==false)
            throw new Exception('Le type doit être text, numeric,poste, card, select ou date', 1);
        if (trim($this->ad_size)==''||isNumber($this->ad_size)==0||$this->ad_size>22)
        {
            switch ($this->ad_type)
            {
                case 'text':
                    $this->ad_size=22;
                    break;
                case 'numeric':
                    $this->ad_size=9;
                    break;
                case 'date':
                    $this->ad_size=8;
                    break;
                case 'zone':
                    $this->ad_size=22;
                    break;

                default:
                    $this->ad_size=22;
            }
        }
        if ($this->ad_type=='numeric')
        {
            $this->ad_extra=(trim($this->ad_extra)=='')?'2':$this->ad_extra;
            if (isNumber($this->ad_extra)==0)
                throw new Exception("La précision doit être un chiffre");
        }
        if ($this->ad_type=='select')
        {
            if (trim($this->ad_extra)=="")
                throw new Exception("La requête SQL est vide ");
            if (preg_match('/^\h*select/i', $this->ad_extra)==0)
                throw new Exception("La requête SQL doit commencer par SELECT ");
            try
            {

                $this->cn->exec_sql($this->ad_extra);
            }
            catch (Exception $e)
            {
                record_log($e);
                throw new Exception("La requête SQL ".h($this->ad_extra)." est invalide ");
            }
        }
    }


    public function update()
    {
        try
        {
            $this->verify();
            if ($this->ad_id<9000)
                return;
            /*   please adapt */
            parent::update();
        }
        catch (Exception $e)
        {
            record_log($e);
            throw $e;
        }
    }

    public function delete()
    {
        if ($this->ad_id<9000)
            return;
        $sql=$this->cn->exec_sql("delete from fiche_detail  where ad_id=$1 ", array($this->ad_id));

        $sql="delete from jnt_fic_attr where ad_id=$1";
        $res=$this->cn->exec_sql($sql, array($this->ad_id));

        $sql="delete from attr_def where ad_id=$1";
        $res=$this->cn->exec_sql($sql, array($this->ad_id));
    }

    /* !
     * @brief used with a usort function, to sort an array of Attribut on the attribut_id (ad_id)
     */

    static function sort_by_id($o1, $o2)
    {
        if ($o1->ad_id>$o2->ad_id)
            return 1;
        if ($o1->ad_id==$o2->ad_id)
            return 0;
        return -1;
    }

    public function save()
    {

        /* please adapt */
        if ($this->ad_id < 1)
        {
            $this->ad_id=-1;
            $this->insert();
        }
        else
            $this->update();
    }
    public function to_array($prefix="")
    {
        
        $array=array();
        foreach ($this->name as $key=> $value)
        {
            $nkey=$prefix.$value;
            $array[$nkey]=$this->$value;
//            $nkey=$prefix.$key;
//            $array[$nkey]=$this->$value;
//            
        }
        tracedebug("card_attribute.log",$array);
        return $array;
    }
}

//Fiche_Attr::test_me();



