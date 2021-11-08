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
 * \brief define Class fiche, this class are using
 *        class attribut
 */
/*!
 * \brief define Class fiche and fiche def, those class are using
 *        class attribut. When adding or modifing new card in a IPOPUP
 *        the ipopup for the accounting item is ipop_account
 */

//-----------------------------------------------------
// class fiche
//-----------------------------------------------------
class Fiche
{
    var $cn;           /*! < $cn database connection */
    var $id;           /*! < $id fiche.f_id */
    var $fiche_def;    /*! < $fiche_def fd_id */
    var $attribut;     /*! < $attribut array of attribut object */
    var $fiche_def_ref; /*!< $fiche_def_ref Type */
    var $row;           /*! < All the row from the ledgers */
    var $quick_code;		/*!< quick_code of the card */
    private $f_enable;  /*!< if card is enable (fiche.f_enable) */
    function __construct($p_cn,$p_id=0)
    {
        $this->cn=$p_cn;
        $this->id=$p_id;
        $this->quick_code='';
        $this->attribut=array();
        $this->f_enable='1';
        if ($p_id != 0 ) { $this->load();} else {
            $this->fiche_def=0;
        }
        
       
    }
    public function set_fiche_def($p_fiche_def)
    {
        $this->fiche_def=$p_fiche_def;
        return $this;
    }

    public function get_id()
    {
        return $this->id;
    }

    public function get_fiche_def_ref()
    {
        return $this->fiche_def_ref;
    }

    public function get_f_enable()
    {
        return $this->f_enable;
    }

    public function set_id($id)
    {
        $this->id=$id;
        return $this;
    }

    public function set_fiche_def_ref($fiche_def_ref)
    {
        $this->fiche_def_ref=$fiche_def_ref;
        return $this;
    }

    public function set_f_enable($f_enable)
    {
        $this->f_enable=$f_enable;
        return $this;
    }

    /**
     *@brief used with a usort function, to sort an array of Fiche on the name
     */
    static function cmp_name(Fiche $o1,Fiche $o2)
    {
        return strcmp($o1->strAttribut(ATTR_DEF_NAME),$o2->strAttribut(ATTR_DEF_NAME));
    }

  /**
   *@brief get the available bank_account filtered by the security
   *@return array of card
   */
    function get_bk_account()
    {
        global $g_user;
      $sql_ledger=$g_user->get_ledger_sql('FIN',3);
      $avail=$this->cn->get_array("select jrn_def_id,jrn_def_name,"
              . "jrn_def_bank,jrn_def_description,currency_id from jrn_def where jrn_def_type='FIN' and $sql_ledger
                            order by jrn_def_name");

      if ( count($avail) == 0 )
            return null;

      for ($i=0;$i<count($avail);$i++)
        {
            $t=new Fiche($this->cn,$avail[$i]['jrn_def_bank']);
            $t->ledger_name=$avail[$i]['jrn_def_name'];
            $t->ledger_description=$avail[$i]['jrn_def_description'];
            $t->getAttribut();
            $all[$i]=$t;

        }
        return $all;
    }


    /*!   get_by_qcode($p_qcode)
     * \brief Retrieve a card thx his quick_code
     *        complete the object,, set the id member of the object or set it
     *        to 0 if no card is found
     * \param $p_qcode quick_code (ad_id=23)
     * \param $p_all retrieve all the attribut of the card, possible value
     * are true or false. false retrieves only the f_id. By default true
     * \returns 0 success , card found / 1 error card not found
     */
    function get_by_qcode($p_qcode=null,$p_all=true)
    {
        if ( $p_qcode == null )
            $p_qcode=$this->quick_code;
        $p_qcode=trim($p_qcode);
        $sql="select f_id from fiche_detail
             where ad_id=23 and ad_value=upper($1)";
        $this->id=$this->cn->get_value($sql,array($p_qcode));
        if ( $this->cn->count()==0)
        {
            $this->id=0;
            return 1;
        }


        if ( $p_all )
            $this->getAttribut();
        return 0;
    }
    /**
     *@brief set an attribute by a value, if the attribut array is empty
     * a call to getAttribut is performed
     *@param int  AD_ID attr_def.ad_id
     *@param int value value of this attribute
     *@see constant.php table: attr_def
     */
    function setAttribut($p_ad_id,$p_value)
    {
        if ( sizeof($this->attribut)==0 ) $this->getAttribut();
        
        for ($e=0;$e <sizeof($this->attribut);$e++)
        {
            if ( $this->attribut[$e]->ad_id == $p_ad_id )
            {
                $this->attribut[$e]->av_text=$p_value;
                break;
            }
        }
    }
    /**
     *\brief  get all the attribute of a card, add missing ones
     *         and sort the array ($this-\>attribut) by ad_id
     */
    function getAttribut()
    {
        Card_Property::load($this);
    }
    /**
     * @brief find the card with the p_attribut equal to p_value, it is not case sensitive
     * @param int $p_attribut attribute to find see table attr_def
     * @param string $p_value value in attr_value.av_text
     * @return array returns ARRAY OF jft_id,f_id,fd_id,ad_id,av_text
     */
    function seek($p_attribut,$p_value)
    {
        $sql="select jft_id,f_id,fd_id,ad_id,ad_value from fiche join fiche_detail using (f_id)
             where ad_id=$1 and upper(ad_value)=upper($2)";
        $res=$this->cn->get_array($sql,array($p_attribut,$p_value));
        return $res;
    }

    /*!
     **************************************************
     * \brief  Count the nb of card with the reference card id frd_id
     *
     * \param $p_frd_id the fiche_def_ref.frd_id
     * \param $p_search p_search is a filter on the name
     * \param $p_sql extra sql condition
     *
     * \return nb of item found
     */
    function count_by_modele($p_frd_id,$p_search="",$p_sql="")
    {
        // Scan for SQL inject
        $this->cn->search_sql_inject($p_sql);

        if ( $p_search != "" )
        {
            $result = $this->cn->get_value("select count(*) from 
                        vw_fiche_attr 
                        where 
                        frd_id=$1 
                        and vw_name ilike '%'||$2||'%'",
                    [$p_frd_id,$p_search]);
            return $result;
        } else {
            $result = $this->cn->get_value("select count(*)
                                 from
                                 fiche join fiche_Def using (fd_id)
                                 where frd_id=$1 ".$p_sql
                ,[$p_frd_id]);
            return $result;
        }


    }
    /*!
     **************************************************
     * \brief  Return array of card from the frd family deprecated , use insert get_by_category_id
     *
     * \deprecated 
     * \see Fiche::get_by_category
     * \param  $p_frd_id the fiche_def_ref.frd_id NOT USED , $this->fiche_def_ref will be used instead
     * \param  $p_offset
     * \param  $p_search is an optional filter
     *\param $p_order : possible values are name, f_id
     * \return array of fiche object
     */
    function GetByDef($p_frd_id,$p_offset=-1,$p_search="",$p_order='')
    {
           return $this->get_by_category($p_offset,$p_search,$p_order);
    }
    function ShowTable()
    {
        echo "<TR><TD> ".
        $this->id."</TD>".
        "<TR> <TD>".
        $this->attribut_value."</TD>".
        "<TR> <TD>".
        $this->attribut_def."</TD></TR>";
    }
    /***
     * @brief  return the string of the given attribute
     *        (attr_def.ad_id)
     * @param int  $p_ad_id  AD_ID from attr_def.ad_id
     * @param int $p_return 1 return NOTFOUND otherwise an empty string
     * @see constant.php
     * @return string
     */
    function strAttribut($p_ad_id,$p_return=1)
    {
        $return=($p_return==1)?NOTFOUND:"";
        if ( empty ($this->attribut)  )
        {

          $this->getAttribut();
        }

        foreach ($this->attribut as $e)
        {
            if ( $e->ad_id == $p_ad_id )
                return $e->av_text;
        }
        return $return;
    }
    /*!
     * \brief  turn a card into an array , then it can be saved thanks update or insert
     * \see Fiche::insert , Fiche::update
     * 
     */
    function to_array()
    {
        $a_return=[];
        if ( empty ($this->attribut)) {
            $this->getAttribut();
        }
        foreach ($this->attribut as $attr)
        {
            $a_return['av_text'.$attr->ad_id]=$attr->av_text;
        }
        $a_return['f_enable']=$this->get_f_enable();
        return $a_return;
    }
    /*!
     * \brief  insert a new record
     *         show a blank card to be filled
     *
     * \param  $p_fiche_def is the fiche_def.fd_id
     *
     * \return HTML Code
     */
    function blank($p_fiche_def)
    {
        // array = array of attribute object sorted on ad_id
        $fiche_def=new Fiche_Def($this->cn,$p_fiche_def);
        $fiche_def->get();
        $array=$fiche_def->getAttribut();
        $r="";
        $r.='<table style="width:98%;margin:1%">';
        foreach ($array as $attr)
        {
            $table=0;
            $msg="";$bulle='';
            $r.=$attr->input($fiche_def);
           
        }
        $r.= '</table>';
        return $r;
    }

    /**
     *
     * @return string with the category
     */
    function getLabelCategory()
    {
        $type_card=$this->cn->get_value('select fd_label '
            . ' from fiche_def join fiche using (fd_id) where f_id=$1',
            array($this->id));
        return $type_card;
    }

    /*!
     * \brief  Display object instance, getAttribute
     *        sort the attribute and add missing ones
     * \param $p_readonly true= if can not modify, otherwise false
     *\param string $p_in if called from an ajax it is the id of the html 
     * elt containing
     *
     * \return string to display or FNT string for fiche non trouvé
     */
    function Display($p_readonly,$p_in="")
    {
        $this->GetAttribut();
        $attr=$this->attribut;
        $ret="";
        $ret.='<span style="margin-right:5px;float:right;font-size:80%">'.
                _('id').':'.$this->id."</span>";
        $ret.="<table style=\"width:98%;margin:1%\">";
        if (empty($attr))
        {
            return 'FNT';
        }
        
        $fiche_def=new Fiche_Def($this->cn,$this->fiche_def);
        /* for each attribute */
        foreach ($attr as $r)
        {
            $msg="";
            $bulle="";
            if ($p_readonly)
            {
                $ret .= $r->print();
            }
            if ($p_readonly==false)
            {
                $ret .= $r->input($fiche_def);
               
            }
         }
        // Display if the card is enable or not
        $enable_is=new InputSwitch("f_enable");
        $enable_is->value=$this->f_enable;
        $enable_is->readOnly=$p_readonly;
                
        $ret.=tr( 
                td(_("Actif"),'class="input_text"').td($enable_is->input(),'class="input_text"')
                );
        
        
        $ret.="</table>";

        return $ret;
    }

    /*!
     * \brief  Save a card, call insert or update 
     * \see Fiche::insert , Fiche::update
     *
     * \param p_fiche_def (default 0)
     */
    function save($p_fiche_def=0)
    {
        // new card or only a update ?
        if ( $this->id == 0 )
            $this->insert($p_fiche_def);
        else
            $this->update();
    }
    /*!
     * \brief  insert a new record thanks an array , either as parameter or $_POST
     *
     * \param $p_fiche_def fiche_def.fd_id
     * \param $p_array is the array containing the data
     *\param $transation DEPRECATED : if we are in a transaction, we don't commit here , else if not, the
     * then a transaction is started and committed 
     * 
     av_textX where X is the ad_id
     *\verb
    example
    av_text1=>'name'
    \endverb
     */
    function insert($p_fiche_def, $p_array=null, $transaction=true)
    {
        if ($p_array==null)
            $p_array=$_POST;

        $fiche_id=$this->cn->get_next_seq('s_fiche');
        $this->id=$fiche_id;
        $this->fiche_def=$p_fiche_def;
        try
        {

            // by default the card is available
            if (!isset($p_array['f_enable']))
            {
                $p_array['f_enable']=1;
            }
            $Ret=$this->cn->exec_sql("insert into fiche(f_id,f_enable,fd_id) values ($1,$2,$3)",
                    array($fiche_id, $p_array['f_enable'], $p_fiche_def));
            // first we need to save the name , to compute properly the quickcode
            if ( empty ($p_array['av_text'.ATTR_DEF_NAME])) {

                $p_array['av_text'.ATTR_DEF_NAME]=_("Nom vide");
            }
            // the name must be saved first
            $this->cn->exec_sql("insert into fiche_detail (f_id,ad_id,ad_value) values ($1,$2,$3)",
                array($fiche_id,1,$p_array['av_text'.ATTR_DEF_NAME]));

            // compute a quick_code
            if (!isset($p_array["av_text".ATTR_DEF_QUICKCODE]))
            {
                $p_array["av_text".ATTR_DEF_QUICKCODE]="";
            }
            $sql=sprintf("select insert_quick_code(%d,'%s')", $fiche_id,
                    sql_string($p_array['av_text'.ATTR_DEF_QUICKCODE]));
            $this->cn->exec_sql($sql);
            // get the card properties for this card category
            $fiche_def=new Fiche_Def($this->cn, $p_fiche_def);
            
            $this->attribut=$fiche_def->getAttribut();

            if (empty($this->attribut))
            {
                throw new Exception("FICHE.UPDATE02"._("Aucun attribut ")."($p_fiche_def)", EXC_INVALID);
            }
            // for each property set the attribut on the card
            foreach ($this->attribut as $property)
            {
                $key='av_text'.$property->ad_id;
                if (isset($p_array[$key]))
                {
                    $this->setAttribut($property->ad_id, $p_array[$key]);
                }
            }
            // For accounting 
            
            Card_Property::update($this);
            // reread from database
            $this->getAttribut();
        }
        catch (Exception $e)
        {
            record_log("FIC603".$e->getMessage()." ".$e->getTraceAsString());
            $this->cn->rollback();
            throw ($e);
            return;
        }
        return;
    }

    /*!
     * \brief update a card with an array
     * \param $p_array (optional) is the array containing the data , if NULL then $_POST will be uses
     *\param $transation if we want to manage the transaction in this function
     * true for small insert and false for a larger loading, the BEGIN / COMMIT sql
     * must be done into the caller
     av_textX where X is the ad_id
     *\verb
    example
    av_text1=>'name'
    \endverb
     */
    function update($p_array=null)
    {
        if ($p_array==null)
        {
            $p_array=$_POST;
        }
        $this->fiche_def = $this->cn->get_value("select fd_id from fiche where f_id=$1",[$this->id]);
        if ( $this->cn->size()==0) {
            throw new Exception("FICHE.UPDATE01"._("Fiche n'existe pas"),EXC_INVALID);
        }
        
        
        // get the card properties for this card category
        $this->getAttribut();
        
        if ( empty ($this->attribut) ) {
            throw new Exception("FICHE.UPDATE02"._("Aucun attribut ")."($fiche_def)",EXC_INVALID);
        }
        // for each property set the attribut on the card
        foreach($this->attribut as $property) {
            $key='av_text'.$property->ad_id;
            if ( isset($p_array[$key])) {
                $this->setAttribut($property->ad_id, $p_array[$key]);
            }
        }
        if ( isset($p_array['f_enable'])) {
            $this->set_f_enable($p_array["f_enable"]);
        }else {
            $this->set_f_enable(1);
        }
        // save all
        Card_Property::update($this);
        $this->quick_code=$this->strAttribut(ATTR_DEF_QUICKCODE);
    }

    /*!\brief  remove a card
    */
    function remove($silent=false)
    {
        if ( $this->id==0 ) return;
        // verify if that card has not been used is a ledger
        // if the card has its own account in PCMN
        // Get the fiche_def.fd_id from fiche.f_id
        $this->Get();
        $fiche_def=new Fiche_Def($this->cn,$this->fiche_def);
        $fiche_def->get();

        // if the card is used do not removed it
        $qcode=$this->strAttribut(ATTR_DEF_QUICKCODE);

        if ( $this->cn->count_sql("select * from jrnx where j_qcode='".Database::escape_string($qcode)."'") != 0)
        {
            if ( ! $silent ) {
		alert(_('Impossible cette fiche est utilisée dans un journal'));
            }
            return 1;
        }

        $this->delete();
		return 0;
    }


    /*!\brief return the name of a card
     *
     */
    function getName()
    {
        $sql="select ad_value from fiche_detail
             where ad_id=1 and f_id=$1";
        $Res=$this->cn->exec_sql($sql,array($this->id));
        $r=Database::fetch_all($Res);
        if ( empty($r) )
            throw new Exception (_("Fiche n'existe pas"), 1000);
        return $r[0]['ad_value'];
    }

    /*!\brief return the quick_code of a card
     * \return null if not quick_code is found
     */
    function get_quick_code()
    {
        $sql="select ad_value from fiche_detail where ad_id=23 and f_id=$1";
        $Res=$this->cn->exec_sql($sql,array($this->id));
        $r=Database::fetch_all($Res);
        if ( $r == FALSE || sizeof($r) == 0 )
            return null;
        return $r[0]['ad_value'];
    }

    /*!\brief Synonum of fiche::getAttribut
     */
    function Get()
    {
        $this->getAttribut();
    }
    /*!\brief Synonum of fiche::getAttribut
     */
    function load() 
    {
        $this->getAttribut();
    }
    /*!
     * \brief get all the card thanks the fiche_def_ref
     * \param $p_offset (default =-1)
     * \param $p_search sql condition
     * \return array of fiche object
     */
    function get_by_category($p_offset=-1,$p_search="",$p_order='')
    {
       switch($p_order)
        {
        case 'name' :
                $order=' order by name';
            break;
        case 'f_id':
            $order='order by f_id';
            break;
        default:
            $order='';
        }
        if ( $p_offset == -1 )
        {
            $sql="select *
                 from
                 fiche join fiche_Def using (fd_id) join vw_fiche_name using(f_id)
                 where frd_id=".$this->fiche_def_ref." $p_search ".$order;
        }
        else
        {
            $limit=($_SESSION[SESSION_KEY.'g_pagesize']!=-1)?"limit ".$_SESSION[SESSION_KEY.'g_pagesize']:"";
            $sql="select *
                 from
                 fiche join fiche_Def using (fd_id) join vw_fiche_name using(f_id)
                 where frd_id=".$this->fiche_def_ref." $p_search $order  "
                 .$limit." offset ".$p_offset;

        }

        $Ret=$this->cn->exec_sql($sql);
        if ( ($Max=Database::num_row($Ret)) == 0 )
            return [];
        $all[0]=new Fiche($this->cn);

        for ($i=0;$i<$Max;$i++)
        {
            $row=Database::fetch_array($Ret,$i);
            $t=new Fiche($this->cn,$row['f_id']);
            $t->getAttribut();
            $all[$i]=clone $t;

        }
        return $all;
    }
    /*!\brief retrieve the frd_id of the fiche it is the type of the
     *        card (bank, purchase...)
     *        (fiche_def_ref primary key)
     */
    function get_fiche_def_ref_id()
    {
      $result=$this->cn->get_array("select frd_id from fiche join fiche_Def using (fd_id) where f_id=$1",[$this->id]);
        if ( $result == null )
            return null;

        return $result[0]['frd_id'];
    }
    /**
     *@brief fetch and return and array
     *@see get_row get_row_date
     * @deprecated since version 6920
     */
    private function get_row_result_deprecated($res)
    {
        $array=array();
        $tot_cred=0.0;
        $tot_deb=0.0;
        $Max=Database::num_row($res);
        if ( $Max == 0 ) return null;
        bcscale(2);
        for ($i=0;$i<$Max;$i++)
        {
            $array[]=Database::fetch_array($res,$i);
            if ($array[$i]['j_debit']=='t')
            {
                $tot_deb=bcadd($tot_deb, $array[$i]['deb_montant'] );
            }
            else
            {
                $tot_cred=bcadd($tot_cred,$array[$i]['cred_montant'] );
            }
        }
        $this->row=$array;
         $this->tot_deb=$tot_deb;
        $this->tot_cred=$tot_cred;
        return array($array,$tot_deb,$tot_cred);
    }
    /*!
     * \brief  Get data for poste
     *
     * \param  $p_from periode from
     * \param  $p_to   end periode
     *\param $op_let 0 all operation, 1 only lettered one, 2 only unlettered one
     * \return double array (j_date,deb_montant,cred_montant,description,jrn_name,j_debit,jr_internal)
     *         (tot_deb,tot_credit
     *
     */
    function get_row_date($p_from,$p_to,$op_let=0)
    {
        global $g_user;
        if ( $this->id == 0 )
        {
            echo_error("class_fiche",__LINE__,"id is 0");
            return;
        }
        $filter_sql=$g_user->get_ledger_sql('ALL',3);
        $sql_let='';
        switch ($op_let)
        {
        case 0:
            break;
        case 1:
            $sql_let=' and j_id in (select j_id from letter_cred union select j_id from letter_deb)';
            break;
        case '2':
            $sql_let=' and j_id not in (select j_id from letter_cred union select j_id from letter_deb) ';
            break;
        }

        $qcode=$this->strAttribut(ATTR_DEF_QUICKCODE);
        $this->row=$this->cn->get_array("
            with sqlletter as 
            (select j_id,jl_id from letter_cred union all select j_id , jl_id from   letter_deb )
            select distinct substring(jr_pj_number,'[0-9]+$'),j1.j_id,j_date,to_char(j_date,'DD.MM.YYYY') as j_date_fmt,j_qcode,".
                                 "case when j_debit='t' then j_montant else 0 end as deb_montant,".
                                 "case when j_debit='f' then j_montant else 0 end as cred_montant,".
                                 " jr_comment as description,jrn_def_name as jrn_name,j_poste,".
				 " jr_pj_number,".
                                 "j_debit, jr_internal,jr_id,(select distinct jl_id from sqlletter  where sqlletter.j_id=j1.j_id ) as letter , ".
                                "jr_optype , ".
				 " jr_tech_per,p_exercice,jrn_def_name,
                                     (with cred as (select jl_id, sum(j_montant) as amount_cred from letter_cred lc1 left join jrnx as j3 on (j3.j_id=lc1.j_id)  group by jl_id ),
                                    deb as (select jl_id, sum(j_montant) as amount_deb from letter_deb ld1 left join jrnx as j2 on (j2.j_id = ld1.j_id)   group by jl_id )
                                    select amount_deb-amount_cred
                                    from 
                                    cred 
                                    full  join deb using (jl_id) where jl_id=(select distinct jl_id from sqlletter  where sqlletter.j_id=j1.j_id  )) as delta_letter,
								  jrn_def_code,
                                  jrn.currency_rate,
                                 jrn.currency_rate_ref,
                                    jrn.currency_id,
                                    (select cr_code_iso from currency where id=jrn.currency_id) as cr_code_iso,
                                    j_montant,
                                    sum_oc_amount as oc_amount,
                                    sum_oc_vat_amount as oc_vat_amount ,
	case when exists(select 1 from operation_analytique oa where j1.j_id=oa.j_id) then 1 else 0 end as op_analytic
                                  from jrnx as j1 left join jrn_def on jrn_def_id=j_jrn_def 
                                  left join (select j_id,
                                                coalesce(oc_amount,0) as sum_oc_amount ,
                                                coalesce(oc_vat_amount,0) as sum_oc_vat_amount  
                                                from jrnx left join operation_currency using (j_id)
                                             )  as v1 on (v1.j_id=j1.j_id ) 
                                  left join jrn on jr_grpt_id=j_grpt".
				 " left join parm_periode on (p_id=jr_tech_per) ".
                                 " where j_qcode=$1 and ".
                                 " ( to_date($2,'DD.MM.YYYY') <= j_date and ".
                                 "   to_date($3,'DD.MM.YYYY') >= j_date )".
                                 " and $filter_sql $sql_let ".
                                 " order by j_date,substring(jr_pj_number,'[0-9]+$')",array($qcode,$p_from,$p_to));
        
        $res_saldo = $this->cn->exec_sql("select  sum(deb_montant),sum(cred_montant) from 
                    (select case when j_debit='t' then j_montant else 0 end as deb_montant,
                    case when j_debit='f' then j_montant else 0 end as cred_montant
                                  from jrnx 
                                  join jrn_def on (jrn_def_id=j_jrn_def )
                                   join jrn on (jr_grpt_id=j_grpt)
                                   join tmp_pcmn on (j_poste=pcm_val)
				   join parm_periode on (p_id=jr_tech_per) 
                                  where j_qcode=$1 and 
                                  ( to_date($2,'DD.MM.YYYY') <= j_date and 
                                    to_date($3,'DD.MM.YYYY') >= j_date ) 
                                  and $filter_sql  $sql_let ) as m",array($this->id,$p_from,$p_to));
        $this->tot_deb=$this->tot_cred=0;
        
        if ( Database::num_row($res_saldo) > 0 ) {
            $this->tot_deb=Database::fetch_result($res_saldo, 0, 0);
            $this->tot_cred=Database::fetch_result($res_saldo, 0, 1);
        }
        
        return [$this->row,$this->tot_deb,$this->tot_cred];
    }

    /*!
     * \brief  Get data for poste
     *
     * \param  $p_from periode periode.p_id
     * \param  $p_to   end periode periode.p_id
     * \return double array (j_date,deb_montant,cred_montant,description,jrn_name,j_debit,jr_internal)
     *         (tot_deb,tot_credit
     *
     */
    function get_row($p_from,$p_to)
    {
        if ( $this->id == 0 )
        {
            echo_error("class_fiche",__LINE__,"id is 0");
            return;
        }
        $qcode=$this->strAttribut(ATTR_DEF_QUICKCODE);
        $periode=sql_filter_per($this->cn,$p_from,$p_to,'p_id','jr_tech_per');

        $this->row=$this->cn->get_array("select j_date,
                            to_char(j_date,'DD.MM.YYYY') as j_date_fmt,
                            j_qcode,
                            case when j_debit='t' then j_montant else 0 end as deb_montant,
                            case when j_debit='f' then j_montant else 0 end as cred_montant,
                            jr_comment as description,
                            jrn_def_name as jrn_name,
                            j_debit, 
                            jr_internal,
                            jr_id 
                            from jrnx 
                            left join jrn_def on jrn_def_id=j_jrn_def 
                            left join jrn on jr_grpt_id=j_grpt
                            where 
                            j_qcode=$1  and {$periode}
                            order by j_date::date",array(
                               $qcode
                                 ));
         $res_saldo = $this->cn->exec_sql("select  sum(deb_montant),sum(cred_montant) from 
                    (select case when j_debit='t' then j_montant else 0 end as deb_montant,
                    case when j_debit='f' then j_montant else 0 end as cred_montant
                                  from jrnx 
                                    left join jrn_def on jrn_def_id=j_jrn_def 
                                    left join jrn on jr_grpt_id=j_grpt
                                    where 
                                    j_qcode=$1  and {$periode} ) as m",
                 array($this->id));
        $this->tot_deb=$this->tot_cred=0;
        
        if ( Database::num_row($res_saldo) > 0 ) {
            $this->tot_deb=Database::fetch_result($res_saldo, 0, 0);
            $this->tot_cred=Database::fetch_result($res_saldo, 0, 1);
        }
        return array($this->row,$this->tot_deb,$this->tot_cred);

    }
    /*!
     * \brief HtmlTable, display a HTML of a card for the asked period
     *\param $op_let 0 all operation, 1 only lettered one, 2 only unlettered one
     * \return none
     */
    function HtmlTableDetail($p_array=null,$op_let=0)
    {
        if ( $p_array == null)
            $p_array=$_REQUEST;

        $name=$this->getName();

        list($array,$tot_deb,$tot_cred)=$this->get_row_date( $p_array['from_periode'],
                                        $p_array['to_periode'],
                                        $op_let
                                                           );

        if ( count($this->row ) == 0 )
            return;
        $qcode=$this->strAttribut(ATTR_DEF_QUICKCODE);

        $rep="";
        $already_seen=array();
        echo '<h2 class="info">'.$this->id." ".$name.'</h2>';
        echo "<TABLE class=\"result\" style=\"width:100%;border-collapse:separate;border-spacing:5px\">";
        echo "<TR>".
        "<TH>"._("n° de pièce / Code interne")." </TH>".
        "<TH>"._("Date")."</TH>".
        "<TH>"._("Description")." </TH>".
        "<TH>"._('Montant')."  </TH>".
        "<TH> "._('Débit/Crédit')." </TH>".
        "</TR>";

        foreach ( $this->row as $op )
        {
            if ( in_array($op['jr_internal'],$already_seen) )
                continue;
            else
                $already_seen[]=$op['jr_internal'];
            echo "<TR  style=\"text-align:center;background-color:lightgrey\">".
            "<td>".$op['jr_pj_number']." / ".$op['jr_internal']."</td>".
            "<td>".$op['j_date']."</td>".
            "<td>".h($op['description'])."</td>".
            "<td>"."</td>".
            "<td>"."</td>".
            "</TR>";
            $ac=new Acc_Operation($this->cn);
            $ac->jr_id=$op['jr_id'];
            $ac->qcode=$qcode;
            echo $ac->display_jrnx_detail(1);

        }
        $solde_type=($tot_deb>$tot_cred)?_("solde débiteur"):_("solde créditeur");
        $diff=round(abs($tot_deb-$tot_cred),2);
        echo "<TR>".
        "<TD>$solde_type".
        "<TD>$diff</TD>".
        "<TD></TD>".
        "<TD>$tot_deb</TD>".
        "<TD>$tot_cred</TD>".
        "</TR>";

        echo "</table>";

        return;
    }
    /*!
     * \brief HtmlTable, display a HTML of a card for the asked period
     * \param $p_array default = null keys = from_periode, to_periode
     *\param $op_let 0 all operation, 1 only lettered one, 2 only unlettered one
     *\return -1 if nothing is found otherwise 0
     *\see get_row_date
     */
    function HtmlTable($p_array=null,$op_let=0,$from_div=1)
    {
        if ( $p_array == null)
            $p_array=$_REQUEST;
        $progress=0;
        // if from_periode is greater than to periode then swap the values
        if (cmpDate($p_array['from_periode'],$p_array['to_periode']) > 0)
        {
                $tmp=$p_array['from_periode'];
                $p_array['from_periode']=$p_array['to_periode'];
                $p_array['to_periode']=$tmp;

        }
        list($array, $tot_deb, $tot_cred) = $this->get_row_date($p_array['from_periode'], $p_array['to_periode'], $op_let);

        if ( count($this->row ) == 0 )
            return -1;

        $rep="";
	if ( $from_div==1)
	  {
	    echo "<TABLE id=\"tbpopup\" class=\"resultfooter\" style=\"margin:1%;width:98%;;border-collapse:collapse;border-spacing:0px 5px\">";
	  }
	else
	  {
	    echo "<TABLE id=\"tb" . $from_div . "\"class=\"result\" style=\"margin:1%;width:98%;border-collapse:collapse;border-spacing:0px 2px\">";
		}
        echo '<tbody>';
        echo "<TR>".
        "<TH style=\"text-align:left\">"._('Date')."</TH>".
        "<TH style=\"text-align:left\">"._('Pièce')." </TH>".
        "<TH style=\"text-align:left\">"._('Poste')." </TH>".
        "<TH style=\"text-align:left\">"._('Interne')." </TH>".
        "<TH style=\"text-align:left\">"._('Tiers')." </TH>".
        "<TH style=\"text-align:left\">"._('Description')." </TH>".
        "<TH style=\"text-align:left\">"._('Type')."</TH>".
        "<TH style=\"text-align:left\">"._('ISO')."</TH>".
        "<TH style=\"text-align:right\">"._('Dev.')."</TH>".
        "<TH style=\"text-align:right\">"._('Débit')."  </TH>".
        "<TH style=\"text-align:right\">"._('Crédit')." </TH>".
        th('Prog.','style="text-align:right"').
        th('Let.','style="text-align:right"');
        "</TR>"
        ;
	$old_exercice="";$sum_deb=0;$sum_cred=0;
	bcscale(2);
	$idx=0;
        $operation=new Acc_Operation($this->cn);
        foreach ( $this->row as $op )
        {
            $vw_operation = sprintf('<A class="detail" style="text-decoration:underline;color:red" HREF="javascript:modifyOperation(\'%s\',\'%s\')" >%s</A>', $op['jr_id'], dossier::id(), $op['jr_internal']);
            $let = '';
            $html_let = "";
            if ($op['letter'] != "")
            {
                    $let = strtoupper(base_convert($op['letter'], 10, 36));
                    $html_let = HtmlInput::show_reconcile($from_div, $let);
                     if ( $op['delta_letter'] != 0) $html_let='<img src="image/warning.png" onmouseover="displayBulle(\'delta = '.$op['delta_letter'].'\')" onmouseleave="hideBulle()" style="height:12px"/>'.$html_let;
            }
            $tmp_diff=bcsub($op['deb_montant'],$op['cred_montant']);

	    /*
	     * reset prog. balance to zero if we change of exercice
	     */
	    if ( $old_exercice != $op['p_exercice'])
	      {
		if ($old_exercice != '' )
		  {
		    $progress=bcsub($sum_deb,$sum_cred);
			$side="&nbsp;".$this->get_amount_side($progress);
		    echo "<TR class=\"highlight\">".
		       "<TD>$old_exercice</TD>".
		     td('').
		      td('').
		      "<TD></TD>".td().
		      "<TD>Totaux</TD>".
                            td().
                            td().
                            td().
		      "<TD style=\"text-align:right\">".nbm($sum_deb)."</TD>".
		      "<TD style=\"text-align:right\">".nbm($sum_cred)."</TD>".
		      td(nbm(abs($progress)).$side,'style="text-align:right"').
		      td('').
		      "</TR>";
		    $sum_cred=0;
		    $sum_deb=0;
		    $progress=0;
		  }
	      }
            $progress=bcadd($progress,$tmp_diff);
            $side="&nbsp;".$this->get_amount_side($progress);
	    $sum_cred=bcadd($sum_cred,$op['cred_montant']);
	    $sum_deb=bcadd($sum_deb,$op['deb_montant']);
            if ($idx%2 == 0) $class='class="odd"'; else $class=' class="even"';
            $idx++;
            
            $tiers=$operation->find_tiers($op['jr_id'], $op['j_id'], $op['j_qcode']);
            $op_analytic=($op['op_analytic']==1)?'<span style="float:right;background:black;color:white;">&ni;</span>':'';
	    echo "<TR $class name=\"tr_" . $let . "_" . $from_div . "\">" .
			"<TD>".smaller_date(format_date($op['j_date_fmt']))."</TD>".
	      td(h($op['jr_pj_number'])).
               td($op['j_poste']).
            "<TD>".$vw_operation."</TD>".
            td($tiers).
            "<TD>".h($op['description']).$op_analytic."</TD>".
                    td($op['jr_optype']);
            
            /// If the currency is not the default one , then show the amount
            if ( $op['currency_id'] > 0 && $op['oc_amount'] != 0)
            {
             echo   td($op['cr_code_iso']).
                    td(nbm($op['oc_amount'],4),'style="text-align:right;padding-left:10px;"');
            } else {
                echo td().td();
            }
            
            echo "<TD style=\"text-align:right\">".nbm($op['deb_montant'])."</TD>".
	      "<TD style=\"text-align:right\">".nbm($op['cred_montant'])."</TD>".
	      td(nbm(abs($progress)).$side,'style="text-align:right"').
            td($html_let, ' style="text-align:right"') .
			"</TR>";
	    $old_exercice=$op['p_exercice'];

        }
        $solde_type=($sum_deb>$sum_cred)?_("solde débiteur"):_("solde créditeur");
        $solde_side=($sum_deb>$sum_cred)?"D":"C";
        $diff=abs(bcsub($sum_deb,$sum_cred));
        echo '<tfoot>';
       echo "<TR class=\"highlight\">".
               td($op['p_exercice']).
               td().
               td().
               td().
               td().
        td(_('Totaux')).
               td().
               td().
        "<TD></TD>".
	 "<TD  style=\"text-align:right\">".nbm($sum_deb)."</TD>".
	 "<TD  style=\"text-align:right\">".nbm($sum_cred)."</TD>".
	  "<TD style=\"text-align:right\">".nbm($diff)."</TD>".
            td($solde_side).
        "</TR>";
        echo "<TR style=\"font-weight:bold\">".
        "<TD>$solde_type</TD>".
	  "<TD style=\"text-align:right\">".nbm($diff)."</TD>".
        "<TD></TD>".
        "</TR>";
        echo '</tfoot>';
        echo '</tbody>';

        echo "</table>";

        return 0;
    }
    /*!
     * \brief Display HTML Table Header (button)
     *
     * \return none
     */
    function HtmlTableHeader($p_array=null)
    {
        if ( $p_array == null)
            $p_array=$_REQUEST;

        $hid=new IHidden();
        echo '<div class="noprint">';
        echo "<table >";
        echo '<TR>';

        echo '<TD><form method="GET" ACTION="">'.
            HtmlInput::submit('bt_other',_("Autre poste")).
            HtmlInput::array_to_hidden(array('gDossier','ac'), $_REQUEST).
            dossier::hidden().
            $hid->input("type","poste").$hid->input('p_action','impress')."</form></TD>";
        $str_ople=(isset($_REQUEST['ople']))?HtmlInput::hidden('ople',$_REQUEST['ople']):'';

        echo '<TD><form method="GET" ACTION="export.php" ';
        $id=uniqid("export_");
        printf( 'id="%s"  onsubmit="download_document_form(\'%s\')">',$id,$id);
        echo 
            HtmlInput::submit('bt_pdf',_("Export PDF")).
            dossier::hidden().$str_ople.
              HtmlInput::hidden('act','PDF:fichedetail').
            $hid->input("type","poste").
            $hid->input('p_action','impress').
            $hid->input("f_id",$this->id).
            dossier::hidden().
            $hid->input("from_periode",$p_array['from_periode']).
            $hid->input("to_periode",$p_array['to_periode']);
        if (isset($p_array['oper_detail']))
            echo $hid->input('oper_detail','on');

        echo "</form></TD>";

        echo '<TD><form method="GET" ACTION="export.php" ';
        $id=uniqid("export_");
        printf( 'id="%s"  onsubmit="download_document_form(\'%s\')">',$id,$id);

        echo HtmlInput::submit('bt_csv',_("Export CSV")).
	  HtmlInput::hidden('act','CSV:fichedetail').
        dossier::hidden().$str_ople.
        $hid->input("type","poste").
        $hid->input('p_action','impress').
        $hid->input("f_id",$this->id).
        $hid->input("from_periode",$p_array['from_periode']).
        $hid->input("to_periode",$p_array['to_periode']);
        if (isset($p_array['oper_detail']))
            echo $hid->input('oper_detail','on');

        echo "</form></TD>";
		echo "</form></TD>";
		echo '<td style="vertical-align:top">';
		echo HtmlInput::print_window();
		echo '</td>';
        echo "</table>";
        echo '</div>';

    }
    /*!
     * \brief   give the balance of an card
     * \return
     *      balance of the card
     *
     */
    function get_solde_detail($p_cond="")
    {
        if ( $this->id == 0 ) return array('credit'=>0,'debit'=>0,'solde'=>0);
        $qcode=$this->strAttribut(ATTR_DEF_QUICKCODE);

        if ( $p_cond != "") $p_cond=" and ".$p_cond;
        $Res=$this->cn->exec_sql("select coalesce(sum(deb),0) as sum_deb,
                                    coalesce(sum(cred),0) as sum_cred from
                                 ( select j_poste,
                                 case when j_debit='t' then j_montant else 0 end as deb,
                                 case when j_debit='f' then j_montant else 0 end as cred
                                 from jrnx
                                 where
                                 j_qcode = ('$qcode'::text)
                                 $p_cond
                                 ) as m  ");
        $Max=Database::num_row($Res);
        if ($Max==0) return 0;
        $r=Database::fetch_array($Res,0);

        return array('debit'=>$r['sum_deb'],
                     'credit'=>$r['sum_cred'],
                     'solde'=>abs($r['sum_deb']-$r['sum_cred']));
    }
    /**
     * Get the sum in Currency
     * @param string $p_cond
     * @return type
     * @throws Exception
     */
    function get_bk_balance_currency($p_cond="")
    {
        if ( $this->id == 0 ) throw  new Exception('fiche->id est nul');

        if ( $p_cond != "") $p_cond=" and ".$p_cond;
        
        $sql = "
              select sum(sum_oc_amount)
              from 
              v_all_card_currency
              where 
              f_id=$1
                $p_cond";
        $val=$this->cn->get_value($sql,[$this->id]);
        
        return $val;
                
    }
    /**
     *get the bank balance with receipt or not in Euro
     *
     */
    function get_bk_balance($p_cond="")
    {
        if ( $this->id == 0 ) throw  new Exception('fiche->id est nul');
        $qcode=$this->strAttribut(ATTR_DEF_QUICKCODE);

        if ( $p_cond != "") $p_cond=" and ".$p_cond;
	$sql="select sum(deb) as sum_deb, sum(cred) as sum_cred from
                                 ( select j_poste,
                                 case when j_debit='t' then j_montant else 0 end as deb,
                                 case when j_debit='f' then j_montant else 0 end as cred
                                 from jrnx
                                 join jrn on (jr_grpt_id=j_grpt)
                                 where
                                 j_qcode = ('$qcode'::text)
                                 $p_cond
                                 ) as m  ";

        $Res=$this->cn->exec_sql($sql);
        $Max=Database::num_row($Res);
        if ($Max==0) return 0;
        $r=Database::fetch_array($Res,0);

        return array('debit'=>$r['sum_deb'],
                     'credit'=>$r['sum_cred'],
                     'solde'=>abs($r['sum_deb']-$r['sum_cred']));

    }
    /*!\brief check if an attribute is empty
     *\param $p_attr the id of the attribut to check (ad_id)
     *\return return true is the attribute is empty or missing
     */
    function empty_attribute($p_attr)
    {
        $sql="select ad_value
             from fiche_detail
             natural join fiche
             left join attr_def using (ad_id) where f_id=$1 ".
             " and ad_id = $2 ".
             " order by ad_id";
        $res=$this->cn->exec_sql($sql,[$this->id,$p_attr]);
        if ( Database::num_row($res) == 0 ) return true;
        $text=Database::fetch_result($res,0,0);
        return (strlen(trim($text)) > 0)?false:true;


    }
    /*! Summary
     * \brief  show the default screen
     *
     * \param $p_search (filter)
     * \param $p_action used for specific action bank, red if credit < debit
     * \param $p_sql SQL to filter the number of card must start with AND
     * \param $p_amount true : only cards with at least one operation default : false
     * \return: string to display
     */
    function Summary($p_search="",$p_action="",$p_sql="",$p_amount=false)
    {
        global $g_user;
        $http=new HttpInput();
        $bank=new Acc_Parm_Code($this->cn,'BANQUE');
        $cash=new Acc_Parm_Code($this->cn,'CAISSE');
        $cc=new Acc_Parm_Code($this->cn,'COMPTE_COURANT');
        
        bcscale(4);
        $gDossier=dossier::id();
        $p_search=sql_string($p_search);
        $script=$_SERVER['PHP_SELF'];
        // Creation of the nav bar
        // Get the max numberRow
        $filter_amount='';
        global $g_user;

        $filter_year="  j_tech_per in (select p_id from parm_periode ".
                     "where p_exercice='".$g_user->get_exercice()."')";

        if ( $p_amount) $filter_amount=' and f_id in (select f_id from jrnx where  '.$filter_year.')';

        $all_tiers=$this->count_by_modele($this->fiche_def_ref,"",$p_sql.$filter_amount);
        // Get offset and page variable
        $offset=$http->request("offset","number",0);
        $page=$http->request("page","number",1);
        $bar=navigation_bar($offset,$all_tiers,$_SESSION[SESSION_KEY.'g_pagesize'],$page);

        // set a filter ?
        $search=$p_sql;

        $exercice=$g_user->get_exercice();
        $tPeriode=new Periode($this->cn);
        list($max,$min)=$tPeriode->get_limit($exercice);


        if ( trim($p_search) != "" )
        {
            $search.=" and f_id in
                     (select distinct f_id from fiche_detail
                     where
                     ad_id in (1,32,30,23,18,13) and ad_value ilike '%$p_search%')";
        }
        // Get The result Array
        $step_tiers=$this->get_by_category($offset,$search.$filter_amount,'name');
        
        if ( $all_tiers == 0 || empty($step_tiers ) ) { return ""; }
        $r="";
        $r.=$bar;
        
        $r.='<table  id="tiers_tb" class="sortable"  style="">
            <TR >
            <TH>'._('Quick Code').Icon_Action::infobulle(17).'</TH>'.
            '<th>'._('Poste comptable').'</th>'.
            '<th  class="sorttable_sorted">'._('Nom').'</span>'.'</th>
            <th>'._('Adresse').'</th>
            <th style="text-align:right">'._('Total débit').'</th>
            <th style="text-align:right">'._('Total crédit').'</th>
            <th style="text-align:right">'._('Solde').'</th>';
        $r.='</TR>';
        if ( sizeof ($step_tiers ) == 0 )
            return $r;

        $i=0;
		$deb=0;$cred=0;
        foreach ($step_tiers as $tiers )
        {
            $i++;
            
             /* Filter on the default year */
             $amount=$tiers->get_solde_detail($filter_year);

            /* skip the tiers without operation */
            if ( $p_amount && $amount['debit']==0 && $amount['credit'] == 0 && $amount['solde'] == 0 ) continue;

            $odd="";
             $odd  = ($i % 2 == 0 ) ? ' odd ': ' even ';
             $accounting=$tiers->strAttribut(ATTR_DEF_ACCOUNT);
             if ( ! empty($accounting) && $p_action == 'bank' 
                     && $amount['debit'] <  $amount['credit']  
                     &&
                     ( /** the accounting is a financial account *****/
                         (!empty ($bank->value) && strpos($accounting,$bank->p_value)===0 )
                      || (!empty ($cash->value) && strpos($accounting,$cash->p_value)===0 )
                      || ( !empty ($cc->value) && strpos($accounting,$cc->p_value)===0)
                    )
                )
                {
                 //put in red if c>d
                 $odd.=" notice ";
                 }
        
             $odd=' class="'.$odd.'"';
             
            $r.="<TR $odd>";
            $url_detail=$script.'?'.http_build_query(array('sb'=>'detail','sc'=>'sv','ac'=>$_REQUEST['ac'],'f_id'=>$tiers->id,'gDossier'=>$gDossier));
            $e=sprintf('<A HREF="%s" title="Détail" class="line"> ',
                       $url_detail);

            $r.="<TD> $e".$tiers->strAttribut(ATTR_DEF_QUICKCODE)."</A></TD>";
            $r.="<TD sorttable_customkey=\"text{$accounting}\"> $e".$accounting."</TD>";
            $r.="<TD>".h($tiers->strAttribut(ATTR_DEF_NAME))."</TD>";
            $r.="<TD>".h($tiers->strAttribut(ATTR_DEF_ADRESS).
                         " ".$tiers->strAttribut(ATTR_DEF_CP).
                         " ".$tiers->strAttribut(ATTR_DEF_PAYS)).
                "</TD>";
            $str_deb=(($amount['debit']==0)?0:nbm($amount['debit']));
            $str_cred=(($amount['credit']==0)?0:nbm($amount['credit']));
            $str_solde=nbm($amount['solde']);
            $r.='<TD sorttable_customkey="'.$amount['debit'].'" align="right"> '.$str_deb.'</TD>';
            $r.='<TD sorttable_customkey="'.$amount['credit'].'" align="right"> '.$str_cred.'</TD>';
            $side=($amount['debit'] >  $amount['credit'])?'D':'C';
            $side=($amount['debit'] ==  $amount['credit'])?'=':$side;
            $red="";
            if ( $p_action == 'customer' && $amount['debit'] <  $amount['credit']  ){
                 //put in red if d>c
                 $red=" notice ";
             }
             if ( $p_action == 'supplier' && $amount['debit'] >  $amount['credit']  ){
                 //put in red if c>d
                 $red=" notice ";
             }
            $r.='<TD class="'.$red.'" sorttable_customkey="'.$amount['solde'].'" align="right"> '.$str_solde."$side </TD>";
            $deb=bcadd($deb,$amount['debit']);
            $cred=bcadd($cred,$amount['credit']);

            $r.="</TR>";

        }
		$r.="<tfoot >";
		$solde=abs(bcsub($deb,$cred));
                $side=($deb > $cred)?'Débit':'Crédit';
                $r.='<tr class="highlight">';
		$r.=td("").td("").td("").td("Totaux").td(nbm($deb),'class="num"').td(nbm($cred),'class="num"').td(" $side ".nbm($solde),'class="num"');
                $r.='</tr>';
		$r.="</tfoot>";
        $r.="</TABLE>";
        $r.=$bar;
        
        return $r;
    }
    /*!
     * \brief get the fd_id of the card : fd_id, it set the attribute fd_id
     */
    function get_categorie()
    {
        if ( $this->id == 0 ) throw  new Exception('class_fiche : f_id = 0 ');
        $sql='select fd_id from fiche where f_id=$1';
        $R=$this->cn->get_value($sql, array($this->id));
        if ( $R == "" )
            $this->fd_id=0;
        else
            $this->fd_id=$R;
        return $this->fd_id;
    }
    /*!
     ***************************************************
     * \brief   Check if a fiche is used by a jrn
     *  return 1 if the  fiche is in the range otherwise 0, the quick_code
     *  or the id  must be set
     *
     *
     * \param   $p_jrn journal_id
     * \param   $p_type : deb or cred default empty
     *
     * \return 1 if the fiche is in the range otherwise < 1
     *        -1 the card doesn't exist
     *        -2 the ledger has no card to check
     *
     */
    function belong_ledger($p_jrn,$p_type="")
    {
        // check if we have a quick_code or a f_id
        if (($this->quick_code==null || $this->quick_code == "" )
                && $this->id == 0 )
        {
            throw  new Exception( 'erreur ni quick_code ni f_id ne sont donnes');
        }

        //retrieve the quick_code
        if ( $this->quick_code=="")
            $this->quick_code=$this->get_quick_code();


        if ( $this->quick_code==null)
            return -1;

        if ( $this->id == 0 )
            if ( $this->get_by_qcode(null,false) == 1)
                return -1;

        $get="";
        if ( $p_type == 'deb' )
        {
            $get='jrn_def_fiche_deb';
        }
        if ( $p_type == 'cred' )
        {
            $get='jrn_def_fiche_cred';
        }
        if ( $get != "" )
        {
            $Res=$this->cn->exec_sql("select $get as fiche from jrn_def where jrn_def_id=$p_jrn");
        }
        else
        {
            // Get all the fiche type (deb and cred)
            $Res=$this->cn->exec_sql(" select jrn_def_fiche_cred as fiche
                                     from jrn_def where jrn_def_id=$p_jrn
                                     union
                                     select jrn_def_fiche_deb
                                     from jrn_def where jrn_def_id=$p_jrn"
                                    );
        }
        $Max=Database::num_row($Res);
        if ( $Max==0)
        {
            return -2;
        }
        /* convert the array to a string */
        $list=Database::fetch_all($Res);
        $str_list="";
        $comma='';
        foreach ($list as $row)
        {
            if ( $row['fiche'] != '' )
            {
                $str_list.=$comma.$row['fiche'];
                $comma=',';
            }
        }
        // Normally Max must be == 1

        if ( $str_list=="")
        {
            return -3;
        }

        $sql="select *
             from fiche
             where
             fd_id in (".$str_list.") and f_id= ".$this->id;

        $Res=$this->cn->exec_sql($sql);
        $Max=Database::num_row($Res);
        if ($Max==0 )
            return 0;
        else
            return 1;
    }
    /*!\brief  get all the card from a categorie
     *\param $p_cn database connx
     *\param $pFd_id is the category id
     *\param $p_order for the sort, possible values is name_asc,name_desc or nothing
     *\return an array of card, but only the fiche->id is set
     */
    static function get_fiche_def($p_cn,$pFd_id,$p_order='')
    {
        switch ($p_order)
        {
        case 'name_asc':
            $sql='select f_id,ad_value from fiche join fiche_detail using (f_id) where ad_id=1 and fd_id=$1 order by 2 asc';
            break;
        case 'name_desc':
            $sql='select f_id,ad_value from fiche join fiche_detail using (f_id) where ad_id=1 and fd_id=$1 order by 2 desc';
            break;
        default:
            $sql='select f_id from fiche  where fd_id=$1 ';
        }
        $array=$p_cn->get_array($sql,array($pFd_id));

	return $array;
    }
    /*!\brief check if a card is used
     *\return return true is a card is used otherwise false
     */
    function is_used()
    {
        /* retrieve first the quickcode */
        $qcode=$this->strAttribut(ATTR_DEF_QUICKCODE);
        $sql='select count(*) as c from jrnx where j_qcode=$1';
        $count=$this->cn->get_value($sql,array($qcode));
	        if ( $count > 0 ) return TRUE;
        $count=$this->cn->get_value("select count(*) from action_gestion where f_id_dest=$1 or ag_contact=$1 ",
                [$this->id]);
        if ( $count > 0 ) return TRUE;
        $count=$this->cn->get_value("select count(*) from action_person where f_id=$1 ",
                [$this->id]);
        if ( $count > 0 ) return TRUE;
        
        $count=$this->cn->get_value("
                                select count(*) 
                from attr_def
                join fiche_detail using (ad_id)
                where ad_type='card'
                and ad_value=$1"
                ,[$qcode]);
        
        if ( $count > 0 ) return TRUE;
        
        return FALSE;

    }
    /*\brief remove a card without verification */
    function delete()
    {
        $this->cn->start();

        // Remove from attr_value
        $Res=$this->cn->exec_sql("delete from fiche_detail
                                 where
                                   f_id= $1",[ $this->id] );

        // Remove from fiche
        $Res=$this->cn->exec_sql("delete from fiche where f_id=$1",[$this->id]);
        
        $this->cn->commit();


    }
    /*!\brief create the sql statement for retrieving all
     * the card
     *\return string with sql statement
     *\param $array contains the condition
    \verbatim
       [jrn] => 2
       [typecard] => cred / deb / filter or list
       [query] => string
    \endverbatim
     *\note the typecard cred, deb or filter must be used with jrn, the value of list means a list of fd_id
     *\see ajax_card.php cards.js
     */
    function build_sql($array)
    {
        if (!empty($array))
            extract($array, EXTR_SKIP);
        $and='';
        $filter_fd_id='true';
        $filter_query='';
        if (isset($typecard))
        {
            if (strpos($typecard, "sql")==false)
            {
                switch ($typecard)
                {
                    case 'cred':
                        if (!isset($jrn))
                            throw Exception('Erreur pas de valeur pour jrn');
                        $filter_jrn=$this->cn->make_list("select jrn_def_fiche_cred from jrn_Def where jrn_def_id=$1",
                                array($jrn));
                        $filter_fd_id=" fd_id in (".$filter_jrn.")";
                        $and=" and ";
                        break;
                    case 'deb':
                        if (!isset($jrn))
                            throw Exception('Erreur pas de valeur pour jrn');
                        $filter_jrn=$this->cn->make_list("select jrn_def_fiche_deb from jrn_Def where jrn_def_id=$1",
                                array($jrn));
                        $filter_fd_id=" fd_id in (".$filter_jrn.")";
                        $and=" and ";
                        break;
                    case 'filter':
                        if (!isset($jrn))
                            throw Exception('Erreur pas de valeur pour jrn');
                        $filter_jrn=$this->cn->make_list("select jrn_def_fiche_deb from jrn_Def where jrn_def_id=$1",
                                array($jrn));

                        if (trim($filter_jrn)!='')
                            $fp1=" fd_id in (".$filter_jrn.")";
                        else
                            $fp1="fd_id < 0";

                        $filter_jrn=$this->cn->make_list("select jrn_def_fiche_cred from jrn_Def where jrn_def_id=$1",
                                array($jrn));

                        if (trim($filter_jrn)!='')
                            $fp2=" fd_id in (".$filter_jrn.")";
                        else
                            $fp2="fd_id < 0";

                        $filter_fd_id='('.$fp1.' or '.$fp2.')';

                        $and=" and ";
                        break;
                    case 'all':
                        $filter_fd_id=' true';
                        break;
                    default:
                        if (trim($typecard)!='')
                            $filter_fd_id=' fd_id in ('.$typecard.')';
                        else
                            $filter_fd_id=' fd_id < 0';
                }
            }
            else
            {
                $filter_fd_id=str_replace('[sql]', '', $typecard);
            }
        }

        $and=" and ";
        if (isset($query))
        {
            $query=sql_string($query);

            if (strlen(trim($query))>0)
            {
                $query=str_replace(" ", "%", $query);
                $filter_query=$and."(vw_name ilike '%$query%' or quick_code ilike ('%$query%') "
                        ." or vw_description ilike '%$query%' or tva_num ilike '%$query%' or accounting like upper('$query%'))";
            }
            else
            {
                $filter_query='';
            }
        }
        $sql="select * from vw_fiche_attr where ".$filter_fd_id.$filter_query;
        return $sql;
    }

    /**
     *@brief move a card to another cat. The properties will changed
     * and be removed
     *@param $p_fdid the fd_id of destination
     */
    function move_to($p_fdid)
    {
        $this->cn->start();
        $this->cn->exec_sql('update fiche set fd_id=$1 where f_id=$2',array($p_fdid,$this->id));
        // add missing
        $this->cn->exec_sql('select fiche_attribut_synchro($1)',array($p_fdid));
        // add to the destination missing fields
        $this->cn->exec_sql("insert into jnt_fic_attr (fd_id,ad_id,jnt_order) select $1,ad_id,100 from fiche_detail where f_id=$2 and ad_id not in (select ad_id from jnt_fic_attr where fd_id=$3)",array($p_fdid,$this->id,$p_fdid));
        $this->cn->commit();
    }
    /**
     * return the letter C if amount is > 0, D if < 0 or =
     * @param type $p_amount
     * @return string
     */
    function get_amount_side($p_amount)
    {
            if ($p_amount == 0)
                    return "";
            if ($p_amount < 0)
                    return "C";
            if ($p_amount > 0)
                    return "D";
    }
    static function test_me()
    {
        $cn=Dossier::connect();
        $a=new Fiche($cn);
        $select_cat=new ISelect('fd_id');
        $select_cat->value=$cn->make_array('select fd_id,fd_label from fiche_def where frd_id='.
                                           FICHE_TYPE_CLIENT);
        echo '<FORM METHOD="GET"> ';
        echo dossier::hidden();
        echo HtmlInput::hidden('test_select',$_GET['test_select']);
        echo 'Choix de la catégorie';
        echo $select_cat->input();
        echo HtmlInput::submit('go_card','Afficher');
        echo '</form>';
        if ( isset ($_GET['go_card']))
        {
            $empty=$a->to_array($_GET['fd_id']);
            print_r($empty);
        }
    }

	function get_gestion_title()
	{
		$r = "<h2 id=\"gestion_title\">" . h($this->getName()) . " " . h($this->strAttribut(ATTR_DEF_FIRST_NAME,0)) . '[' . $this->get_quick_code() . ']</h2>';
		return $r;
	}
	function get_all_account()
	{

	}
    /**
     * @brief Return a string with the HTML code to display a button to export the 
     * history in CSV
     * @param type $p_from from date (DD.MM.YYYY)
     * @param type $p_to to date (DD.MM.YYYY)
     * @return HTML string
     */
    function button_csv($p_from,$p_to) {
        $href="export.php?".http_build_query(
                array(
                    "gDossier"=>Dossier::id(),
                    "f_id"=>$this->id,
                    "ople"=>0,
                    "type"=>"poste",
                    "from_periode"=>$p_from,
                    "to_periode"=>$p_to,
                    "act"=>"CSV:fichedetail"
                    )
                );
        return '<a class="smallbutton" style="display:inline" href="'.$href.'">'._("Export CSV").'</a>';

    }
    /**
     * @brief Return a string with the HTML code to display a button to export the 
     * history in PDF
     * @param type $p_from from date (DD.MM.YYYY)
     * @param type $p_to to date (DD.MM.YYYY)
     * @return HTML string
     */
    function button_pdf($p_from,$p_to) {
        $href="export.php?".http_build_query(
                array(
                    "gDossier"=>Dossier::id(),
                    "f_id"=>$this->id,
                    "ople"=>0,
                    "type"=>"poste",
                    "from_periode"=>$p_from,
                    "to_periode"=>$p_to,
                    "act"=>"PDF:fichedetail"
                    )
                );
        return '<a class="smallbutton" style="display:inline" href="'.$href.'">'._("Export PDF").'</a>';
        
    }
    /**
     * @brief Filter in javascript the table with the history
     * @param type $p_table_id id of the table containting the data to filter
     * @return html string
     */

    function filter_history($p_table_id) {
        return _('Cherche').' '.HtmlInput::filter_table($p_table_id, '0,1,2,3,4,5,6,7,8,9,10', 1);
    }
    /**
     * Returns the Acc_Ledger_Fin ledger for which the card is the default bank account or null if no ledger is found.
     */
    function get_bank_ledger()
    {
        try {
            $id=$this->cn->get_value("select jrn_def_id from jrn_def where jrn_def_bank = $1 ",[$this->id]);
            if ($id == "") { return NULL;}
            $ledger=new Acc_Ledger_Fin($this->cn,$id);
            $ledger->load();
            return $ledger;
        }        
        catch (Exception $e) {
            record_log(__FILE__.":".__LINE__);
            record_log($e->getMessage());
            throw $e;
        }
    }
    /**
     * @brief display card as a table row , the tag TR must be added
     * 
     * @return HTMLT string starting
     * 
     */
    function display_row()
    {
        $this->getAttribut();
        foreach($this->attribut as $attr) {
            $sort="";

            if ($attr->ad_type!='select') 
            {

                if ($attr->ad_type=="date") {
                // format YYYYMMDD
                $sort='sorttable_customkey="'.format_date($attr->av_text, "DD.MM.YYYY", "YYYYMMDD").'"';
                }
                elseif ($attr->ad_type=="poste"){
                $sort='sorttable_customkey="TEXT'.$attr->av_text.'"';
                }
                echo td($attr->av_text, 'style="padding: 0 10 1 10;white-space:nowrap;" '.$sort);
             }
             else {
                $value=$this->cn->make_array($attr->ad_extra);
                $row_content="";
                for ($e=0; $e<count($value); $e++) {
                    if ( $value[$e]['value']==$attr->av_text) {
                        $row_content=h($value[$e]['label']);
                        break;
                    }
                }
                echo td($row_content, 'style="padding: 0 10 1 10;white-space:nowrap;"');

            }
        }

    }
}

?>
