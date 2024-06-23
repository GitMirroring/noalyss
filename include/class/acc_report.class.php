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
 * \brief Create, view, modify and parse report
 */

require_once NOALYSS_INCLUDE.'/database/form_detail_sql.class.php';
require_once NOALYSS_INCLUDE.'/database/form_definition_sql.class.php';

/*!
 * \class Acc_Report
 * \brief Class rapport  Create, view, modify and parse report
 */

class Acc_Report
{

    private $form_definition; /*!< form_definition_sql */
    var $row;
    var $nb;
    var $id;
    var $name;
    /*!
    \brief  Constructor
 */
    function __construct($p_cn,$p_id=-1)
    {
        $this->form_definition=new Form_Definition_SQL($p_cn,$p_id);
    }
    public function get_form_definition()
    {
        return $this->form_definition;
    }

    public function set_form_definition($form_definition): void
    {
        $this->form_definition=$form_definition;
    }

        /*!\brief Return the report's name
     */
    function get_name()
    {
        return $this->form_definition->getp("fr_label");
    }
    /*!
     * \brief return all the row and parse formula
     *        from a report
     * \param $p_start start periode
     * \param $p_end end periode
     * \param $p_type_date type of the date : periode (or 0)  or calendar (or 1)
     */
    function get_row($p_start,$p_end,$p_type_date)
    {
        if (DEBUGNOALYSS > 1) {
            tracedebug("impress.debug.log",__FILE__."71.get_row({$p_start},{$p_end},{$p_type_date})");
        }
        $Res=$this->form_definition->cn->exec_sql("select fo_id ,
                                 fo_fr_id,
                                 fo_pos,
                                 fo_label,
                                 fo_formula,
                                 fr_label from form_detail
                                 inner join form_definition on fr_id=fo_fr_id
                                 where fr_id =$1
                                 order by fo_pos",array($this->form_definition->getp("fr_id")));
        $Max=Database::num_row($Res);
        if ($Max==0)
        {
            $this->row=0;
            return null;
        }
        $col=array();
        
        if ( $p_type_date == '0' || $p_type_date=="periode") {
            $type_date=0;
        } elseif ($p_type_date == '1' || $p_type_date=="calendar") {
            $type_date=1;
        } else {
            throw new Exception("ACR93:invalid type_date [ {$p_type_date} ] ");
        }


        for ($i=0;$i<$Max;$i++)
        {
            $l_line=Database::fetch_array($Res,$i);
            
            $col[]=Impress::parse_formula($this->form_definition->cn,
                                $l_line['fo_label'],
                                $l_line['fo_formula'],
                                $p_start,
                                $p_end,
                                true,
                                $type_date
                               );

        } //for ($i
        $this->row=$col;
        return $col;
    }
   
    /*!
    * \brief save into form and form_def
     */
    function save()
    {
        $this->form_definition->save();
    }
  
    /*!
     * \brief the fr_id MUST be set before calling
     */
    function load():void
    {
       $this->form_definition->load();

    }
    function delete()
    {
       $this->form_definition->delete();
    }
    /*!
     * \brief get a list from form_definition of all defined form
     *
     *\return array of object rapport
     *
     */
    function get_list()
    {
        $sql="select fr_id,fr_label from form_definition order by fr_label";
        $ret=$this->form_definition->cn->exec_sql($sql);
        if ( Database::num_row($ret) == 0 ) return array();
        $array=Database::fetch_all($ret);
        $obj=array();
        foreach ($array as $row)
        {
            $tmp=new Acc_Report($this->form_definition->cn);
            $tmp->id=$row['fr_id'];
            $tmp->name=$row['fr_label'];
            $obj[]=clone $tmp;
        }
        return $obj;
    }
    /*!\brief To make a SELECT button with the needed value, it is used
     *by the SELECT widget
     *\return string with html code
     */
    function make_array()
    {
        $sql=$this->form_definition->cn->make_array("select fr_id,fr_label from form_definition order by fr_label");
        return $sql;
    }


    /*!\brief write to a file the definition of a report
     * \param p_file is the file name (default php://output)
     */
    function export_csv($p_file)
    {
        $this->load();
        
        fputcsv($p_file,array($this->form_definition->getp("fr_label")));
        $array=$this->form_definition->get_cn()->get_array("select fo_label,fo_pos,fo_formula 
                        from form_detail
                        where fo_fr_id=$1
                        order by fo_pos"
                ,array($this->form_definition->getp("fr_id"))
                );
        
        foreach ($array as $row)
        {
            fputcsv($p_file,array($row["fo_label"],
                                  $row["fo_pos"],
                                  $row["fo_formula"])
                   );
        }

    }
    /*!\brief upload a definition of a report and insert it into the
     * database
     */
    function upload()
    {
        if ( empty ($_FILES) ) return;
        if ( noalyss_strlentrim($_FILES['report']['tmp_name'])== 0 )
        {
            alert("Nom de fichier est vide");
            return;
        }
        $file_report=tempnam('tmp','file_report');
        if (  move_uploaded_file($_FILES['report']['tmp_name'],$file_report))
        {
            // File is uploaded now we can try to parse it
            $file=fopen($file_report,'r');
            $data=fgetcsv($file);
            if ( empty($data) ) return;
            $array=array();
            $cn=$this->form_definition->cn;
            $id=$this->form_definition->getp("fr_id");
            while($data=fgetcsv($file))
            {
                $obj=new Form_Detail_SQL($cn);
                $obj->setp("fo_label",$data[0]);
                $obj->setp("fo_pos",$data[1]);
                $obj->set("fo_formula",$data[2]);
                $obj->setp("fo_fr_id",$id);
                $obj->insert();
            }
        }
    }
    /**
     *@brief check if a report exist
     *@param $p_id, optional, if given check the report with this fr_id
     *@return return true if the report exist otherwise false
     */
    function exist($p_id=0)
    {
        $c=$this->form_definition->getp("fr_id");
        if ( $p_id != 0 ) $c=$p_id;
        $ret=$this->form_definition->cn->exec_sql("select fr_label from form_definition where fr_id=$1",array($c));
        if (Database::num_row($ret) == 0) return false;
        return true;
    }
    /**
     * @brief display a form for creating a new report by importing it or set manually
     */
    function create()
    {
        require_once NOALYSS_INCLUDE."/template/acc_report-create.php";
    }
    /**
     * @brief 
     * @param IText $name
     */
    function input_name($name)
    {
        $name=new IText("fr_name",$name);
        $name->id="fr_name_inplace";
        
        $iName=new Inplace_Edit($name);
        $iName->set_callback("ajax_misc.php");
        $iName->add_json_param("op", "report_definition");
        $iName->add_json_param("sa", "change_name");
        $iName->add_json_param("gDossier", Dossier::id());
        $iName->add_json_param("p_id", $this->form_definition->getp("fr_id"));
        return $iName;
    }
}

?>
