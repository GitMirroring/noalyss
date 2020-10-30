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
// Copyright (2002-2019) Author Dany De Bontridder <danydb@noalyss.eu>

/* @file
 * @brief  display and allow to update , add , delete document_type for the follow up
 * 
 */
require_once NOALYSS_INCLUDE."/lib/manage_table_sql.class.php";
require_once NOALYSS_INCLUDE."/database/document_type_sql.class.php";

/**
 * @class 
 * @brief display , modify and add document_type for follow up
 * @see include/ajax/ajax_cfgaction.php
 * @see include/cfg_action.inc.php
 */
class Action_Document_Type_MTable extends Manage_Table_SQL
{

    private $other; //!< Other value 

    public function __construct(Document_type_SQL $doc_type)
    {
        parent::__construct($doc_type);
        $this->set_order(["dt_id", "dt_prefix", "dt_value",]);
        $this->set_property_updatable("dt_id", false);
        $this->set_property_visible("dt_id", false);
        $this->set_property_updatable("dt_value", true);
        $this->set_property_updatable("dt_prefix", true);
        $this->set_col_label("dt_value", _("Nom"));
        $this->set_col_label("dt_prefix", _("Préfixe document"));
        $this->other=array();
    }

    /**
     * 
      @code
      table	"public.document_type"
      ctl_id	"dtr"
      gDossier	"74"
      op	"cfgaction"
      p_id	"28"
      action	"save"
      ctl	"tbl5f785d403b123"
      dt_prefix	"ABO.HEB"
      dt_value	"Abonnements+PhpCompta"
      seq	"0"
      det_op	"1"
      det_contact_mul	"1"
      update	"OK"
      @endcode
     * 
     * 
     *       
     */
    public function from_request()
    {
        $http=new HttpInput();
        $this->table->dt_value=$http->request('dt_value');
        $this->table->dt_prefix=$http->request('dt_prefix');
        $this->other['detail_operation']=$http->request("detail_operation", "string", 0);
        $this->other['contact_multiple']=$http->request("det_contact_mul", "string", 0);
        $this->other['make_invoice']=$http->request("make_invoice", "string", 0);
        $this->other['followup_comment']=$http->request("followup_comment", "string", 0);
        $this->other['editable_description']=$http->request("editable_description", "string", 0);
        $this->other['enable_followup']=$http->request("enable_followup", "string", 0);
        $this->other['seq']=$http->request("seq", "string", 0);
        $this->other['select_option_operation']=$http->request("select_option_operation", "string", null);
        $this->other['select_comment']=$http->request("select_comment", "string", null);
        $this->other["cor_id"]=$http->request("cor_id","array",[]);
        $nb_corid=count($this->other["cor_id"]);
        $http->set_empty(0);
        for ($i=0;$i<$nb_corid;$i++) {
            $this->other["contact_option$i"]=$http->request("contact_option".$i,"number",0);
        }
                 
    }

    /**
     * 
     */
    public function check()
    {
        $table=$this->get_table();
        $error=0;
        if (empty(trim($table->dt_value)))
        {
            $this->set_error("dt_value", _("Nom ne peut être vide"));
            $error++;
        }
        if (empty(trim($table->dt_prefix)))
        {
            $this->set_error("dt_prefix", _("Préfixe ne peut être vide"));
            $error++;
        }
        // Check doublon
        if ($table->cn->get_value('select count(*) from document_type where upper(dt_value)=upper($1) and dt_id <> $2',
                        [$table->dt_value, $table->dt_id])>0)
        {
            $this->set_error("dt_value", _("Doublon, ce nom existe déjà "));
            $error++;
        }
        // Check doublon
        if ($table->cn->get_value('select count(*) from document_type where upper(dt_prefix)=upper($1) and dt_id <> $2',
                        [$table->dt_prefix, $table->dt_id])>0)
        {
            $this->set_error("dt_value", _("Doublon, ce préfixe existe déjà "));
            $error++;
        }
        if ($error>0)
            return false;
        return true;
    }

    /**
     * 
     * @throws Exception
     */
    function delete()
    {
        $table=$this->get_table();
        try
        {
            // remove the cat if no action
            $count_md=$table->cn->get_value('select count(*) from document_modele where md_type=$1',
                    array($table->dt_id));
            $count_a=$table->cn->get_value('select count(*) from action_gestion where ag_type=$1', array($table->dt_id));

            if ($count_md!=0||$count_a!=0)
            {
                throw new Exception(_('Des actions dépendent de cette catégorie'), 1300);
            }
            $table->delete();
        }
        catch (Exception $exc)
        {
            if ($exc->getCode()!=1300)
            {
                record_log("ADTM01".$exc->getTrace());
            }
            throw new Exception($exc->getMessage());
        }
    }

    /**
     * @brief display the box for adding, the name for p_id , are in the form 
     */
    function input()
    {
        parent::input();



        // Detail option contact
        $table=$this->get_table();
        $cn=$table->cn;
        try
        {
            $cn->start();
            if ( $table->dt_id == -1 ){
                $aOption=$cn->get_array("select cor_id,cor_label,cor_type,-1 document_type_id,0 jdoc_enable
                    from 
                    contact_option_ref cor  
                    order by cor_label");
                
            } else {
                // insert new contact options
                $cn->exec_sql("insert into   jnt_document_option_contact (jdoc_enable,document_type_id,
                                    contact_option_ref_id) 
                             select 0 , $1, cor_id 
                             from contact_option_ref 
                            where
                            cor_id not in (select cor_id from 
                                            jnt_document_option_contact a 
                                            where a.document_type_id=$1)", [$table->dt_id]);

                // Select all
                $aOption=$cn->get_array("select cor_id,cor_label,cor_type,document_type_id ,coalesce(jdoc_enable,0) jdoc_enable
                    from 
                    contact_option_ref cor  
                    left join jnt_document_option_contact jdoc on (cor_id=contact_option_ref_id) 
                    where 
                    document_type_id is null
                    or document_type_id = $1
                    order by cor_label", [$this->table->dt_id]);
                
                // delete unused option by the action_person_
                $cn->exec_sql("delete from action_person_option  where ap_id in (select apo.ap_id 
                    from action_person a 
                    left join action_person_option apo ON  (a.ap_id=apo.action_person_id)
                    left join contact_option_ref cor on (apo.contact_option_ref_id=cor.cor_id)
                    join action_gestion ag  on (a.ag_id=ag.ag_id)
                    where  
                    cor.cor_id not in (select jdoc2.contact_option_ref_id 
                                        from jnt_document_option_contact jdoc2 
                                        where jdoc2.document_type_id =$1 
                                        and jdoc2.jdoc_enable =1));
                    ",[$table->dt_id]);
            }

            // Detail option
            require NOALYSS_TEMPLATE."/action_document_type_mtable_input.php";
            $cn->commit();
        }
        catch (Exception $exc)
        {
            $cn->rollback();
                    
            echo $exc->getMessage();
            error_log($exc->getTraceAsString());
        }
        
    }

    /**
     * save
     */
    function save()
    {
        parent::save();
        $cn=Dossier::connect();
        $object_sql=$this->get_table();
        // restart sequence
        if ($this->other['seq']!=0)
        {
            $doc_type=new Document_type($cn, $object_sql->dt_id);
            $doc_type->set_number($this->other['seq']);
        }
        // Save detail operation
        $cn->exec_sql("insert into document_option (do_code,document_type_id,do_enable,do_option) values ($1,$2,$3,$4) 
            on conflict on constraint document_option_un
            do update set do_enable=$3,do_option=$4", 
                        [                   "detail_operation", 
                                            $object_sql->dt_id,
                                            $this->other['detail_operation'],
                                            $this->other['select_option_operation']
                    ]);

        // Save contact_multiple
        $cn->exec_sql("insert into document_option (do_code,document_type_id,do_enable) values ($1,$2,$3) 
            on conflict on constraint document_option_un
            do update set do_enable=$3 ", ["contact_multiple", $object_sql->dt_id, $this->other['contact_multiple']]);
        
         // Save editable description
        $cn->exec_sql("insert into document_option (do_code,document_type_id,do_enable) values ($1,$2,$3) 
            on conflict on constraint document_option_un
            do update set do_enable=$3 ", ["editable_description", $object_sql->dt_id
                , $this->other['editable_description']]);
        
        // Save make invoice
        $cn->exec_sql("insert into document_option (do_code,document_type_id,do_enable) values ($1,$2,$3) 
            on conflict on constraint document_option_un
            do update set do_enable=$3 ", ["make_invoice", $object_sql->dt_id, $this->other['make_invoice']]);
        // Option for comments
            $cn->exec_sql("insert into document_option (do_code,document_type_id,do_enable,do_option) values ($1,$2,$3,$4) 
                on conflict on constraint document_option_un
                do update set do_enable=$3,do_option=$4 ", 
                    [   "followup_comment", 
                        $object_sql->dt_id, 
                        $this->other['followup_comment'],
                        $this->other['select_comment']
                    ]);
        
        // Option contact to save
        $cn->exec_sql("delete from jnt_document_option_contact where document_type_id=$1",[$object_sql->dt_id]);
        $nb_contact_option=count($this->other["cor_id"]);
        $aOption=$this->other["cor_id"];
        for ( $e=0;$e<$nb_contact_option;$e++) {
            $option_id=$aOption[$e];
            if ( isset($this->other["contact_option".$e])  ) {
                $cn->exec_sql("insert into jnt_document_option_contact
                    (jdoc_enable,document_type_id,contact_option_ref_id)
                    values ($1,$2,$3)",[$this->other["contact_option".$e],$object_sql->dt_id,$option_id]);
            } 
        }
        
        
    }

}
