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
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

/**
 * @file
 * @brief manage the attribute of a card 
 */

/**
 * @class Card_Property
 * @brief contains the attributes of a card , manage them, save them , ...
 * 
 */
class Card_Property
{

    var $ad_id;
    //!< ad_id int id of the attribute attr_def.ad_id
    var $ad_text;
    //!< ad_text string label of the attribute attr_def.ad_def
    var $av_text;
    //!< av_text string value of this attribute 
    var $ad_type;
    //!< ad_type string : type of this attribute (select, text ,...)
    var $ad_size;
    //!< ad_size int size of the attribute
    var $ad_extra;
    //!< ad_extra extra info for a attribute
    var $jnt_order;
    //!< jnt_order order to display
    var $cn;
    //!< cn database connexion
    protected $display_mode;
    //!< display mode values are large , window , display Property depending of this mode.
    function __construct($cn, $ad_id=0)
    {
        $this->cn=$cn;
        $this->ad_id=$ad_id;
        $this->display_mode='window';
    }
    public function __toString(): string
    {
        return "card_property".var_export($this,true);
    }
    public function setDisplayMode($p_mode)
    {
        if ( ! in_array($p_mode,array("window","large"))) {
            throw new Exception("FIC70 invalide display mode");
        }
        $this->display_mode=$p_mode;
        return $this;
    }
    public function getDisplayMode()
    {
        return $this->display_mode;
    }

    public function get_ad_id()
    {
        return $this->ad_id;
    }

    public function get_ad_text()
    {
        return $this->ad_text;
    }

    public function get_av_text()
    {
        return $this->av_text;
    }

    public function get_ad_type()
    {
        return $this->ad_type;
    }

    public function get_ad_size()
    {
        return $this->ad_size;
    }

    public function get_ad_extra()
    {
        return $this->ad_extra;
    }

    public function get_jnt_order()
    {
        return $this->jnt_order;
    }

    public function set_ad_id($ad_id): void
    {
        $this->ad_id=$ad_id;
    }

    public function set_ad_text($ad_text): void
    {
        $this->ad_text=$ad_text;
    }

    public function set_av_text($av_text): void
    {
        $this->av_text=$av_text;
    }

    public function set_ad_type($ad_type): void
    {
        $this->ad_type=$ad_type;
    }

    public function set_ad_size($ad_size): void
    {
        $this->ad_size=$ad_size;
    }

    public function set_ad_extra($ad_extra): void
    {
        $this->ad_extra=$ad_extra;
    }

    public function set_jnt_order($jnt_order): void
    {
        $this->jnt_order=$jnt_order;
    }

    /**
     * @brief build the needed elements to display
     * @param Object Fiche_Def $p_fiche_def
     * @return array
     *               - $result['msg'] message to display,
     *               - $result['label'] label of the CardProperty (ad_text),
    *                - $result['input' is the HtmlInput object
     *               - $result['class'] is the CSS class to use
     *               - $result['bulle'] is the infobulle
     */
    public function build_input($p_fiche_def=null)
    {
        $result = ['msg' => '', 'input' => null, 'label' => '', 'class' => 'input_text','bulle'=>''];
        if ($this->ad_id == ATTR_DEF_NAME || $this->ad_id == ATTR_DEF_QUICKCODE) {
            $result['class'] = " input_text highlight info";
        }
        if ($this->ad_id == ATTR_DEF_ACCOUNT) {
            if ( $p_fiche_def == null ) {
                throw new \Exception ("CP162.p_fiche_def is null");
            }
            $result['input'] = new IPoste("av_text" . $this->ad_id);
            $result['input']->id = uniqid('accounting');
            $result['input']->set_attribute('ipopup', 'ipop_account');
            $result['input']->set_attribute('jrn', '0');
            $result['input']->set_attribute('account',  $result['input']->id);
            $result['input']->dbl_click_history();
            $result['input']->value = $this->av_text;
            //  account created automatically
            $sql = "select account_auto($p_fiche_def->id)";
            $ret_sql = $this->cn->exec_sql($sql);
            $a = Database::fetch_array($ret_sql, 0);
            $result['label'] = new ISpan();
            $result['label']->name = "av_text" . $this->ad_id . "_label";
            $p_fiche_def->load();
            if ($a['account_auto'] == 't') {
                $result['msg'] .= $result['label']->input() . " <span style=\"color:red;font-size:80%;display:block\">" .
                    _("Rappel: Poste créé automatiquement à partir de ")
                    . $p_fiche_def->class_base . " </span> ";
            } else {
                // if there is a class base in fiche_def_ref, this account will be the
                // the default one
                if (noalyss_strlentrim($p_fiche_def->class_base) != 0) {
                    $result['msg'] .= " <span style=\"color:red;font-size:80%;display:block\">" . _("Rappel: Poste par défaut sera ") .
                        $p_fiche_def->class_base .
                        " !</span> ";
                    $result['input']->value = (empty ($result['input']->value)) ?$p_fiche_def->class_base:$result['input']->value;
                }
            }
            $result['label']=_("Poste comptable");
            $result['class']=" highlight input_text";
            return $result;

        }
        elseif ($this->ad_id == ATTR_DEF_NUMTVA) {
            /// Propose a button to check VAT
            $result['input']=new IVATNumber( "av_text" . $this->ad_id,$this->av_text);
            $result['label']=$this->ad_text;
            return $result;
       }
        elseif ($this->ad_id == ATTR_DEF_TVA) {
            $result['input'] = new ITva_Popup('popup_tva');
            $result['input']->table = 0;
            $result['input']->value = $this->av_text;
            $result['label']=$this->ad_text;
        } else {
            switch ($this->ad_type) {
                case 'text':
                    $result['input'] = new IText();
                    $result['input']->css_size = "100%";
                    $result['input']->value = $this->av_text;
                    break;
                case 'numeric':
                    $result['input'] = new INum();
                    $result['input']->prec = ($this->ad_extra == "") ? 2 : $this->ad_extra;
                    $result['input']->size = $this->ad_size;
                    $result['input']->value = $this->av_text;
                    break;
                case 'date':
                    $result['input'] = new IDate();
                    $result['input']->value = $this->av_text;
                    break;
                case 'zone':
                    $result['input'] = new ITextArea();
                    $result['input']->style = ' class="itextarea" style="margin:0px;width:100%"';
                    $result['input']->value = $this->av_text;
                    break;
                case 'poste':
                    $result['input'] = new IPoste("av_text" . $this->ad_id);
                    $result['input']->set_attribute('ipopup', 'ipop_account');
                    $result['input']->set_attribute('account', "av_text" . $this->ad_id);
                    $result['input']->table = 1;
                    $bulle = Icon_Action::infobulle(14);
                    $result['input']->value = $this->av_text;
                    break;
                case 'check':
                    $result['input'] = new InputSwitch("av_text" . $this->ad_id);
                    $result['input']->value = (empty($this->av_text) ) ? 0 : 1;
                    break;
                case 'select':
                    $result['input'] = new ISelect("av_text" . $this->ad_id);
                    $result['input']->value = $this->cn->make_array($this->ad_extra);
                    $result['input']->style = 'style="width:100%"';
                    $result['input']->selected = $this->av_text;
                    break;
                case 'card':
                    $result['input'] = new ICard("av_text" . $this->ad_id);
                    // filter on frd_id
                    $result['input']->extra = $this->ad_extra;
                    $result['input']->extra2 = 0;
                    $result['input']->id = uniqid();
                    $result['label'] = new ISpan();
                    $filter = $this->ad_extra;
                    $result['input']->width = $this->ad_size;
                    $result['input']->extra = $filter;
                    $result['input']->extra2 = 0;
                    $result['input']->limit = 6;
                    $result['label']->name = "av_text" . $this->ad_id . $result['input']->id . "_label";
                    $result['input']->set_attribute('ipopup', 'ipopcard');
                    $result['input']->set_attribute('typecard', $this->ad_extra);
                    $result['input']->set_attribute('inp', $result['input']->id);
                    $result['input']->set_attribute('label', "av_text" . $this->ad_id . $result['input']->id . "_label");
                    $result['input']->autocomplete = 1;
                    $result['input']->dblclick = "fill_ipopcard(this);";
                    $result['msg'] = $result['input']->search();
                    $result['msg'] .= $result['label']->input();
                    $result['input']->value = $this->av_text;
                    break;
            }
            $result['input']->table = 0;
            $result['label']=$this->ad_text;
        }

        $result['input']->label = $this->ad_text;
        $result['input']->name = "av_text" . $this->ad_id;
        if ($this->ad_id == 21 || $this->ad_id == 22 || $this->ad_id == 20 || $this->ad_id == 31) {
            $result['bulle'] = Icon_Action::infobulle(21);
        }

        // Warning length quickcode
        if ($this->ad_id == ATTR_DEF_QUICKCODE) {
            $result['bulle'] = Icon_Action::warnbulle(76);
        }

        return $result;
    }
    /**
     * @brief Load all the attribute of a card , it modifies the parameter $fiche. Usually called from fiche::insert 
     * and fiche::update . In the same time, it will synchronize the attributes which the database. 
     * The attributes (public.fiche_detail) will be ordered in the member attribute $fiche->attribut
     * @param $fiche Fiche Full fill this card with all the attributes
     * @see  Fiche::update Fiche::insert
     * @note it is not possible to compute the default attributes for a new card without the card
     * category, so it returns nothing
     * 
     */
    static function load(Fiche $fiche)
    {
        // if card is not yet saved then we don't load it from database but all the properties are set to empty
        if ($fiche->id==0 && $fiche->fiche_def !=0 )
        {
            $fiche_def=new Fiche_Def($fiche->cn,$fiche->fiche_def);
            $aProperty=$fiche_def->getAttribut();
            $fiche->attribut=$aProperty;
            return;
        } elseif ($fiche->id==0 && $fiche->fiche_def ==0 )
        {
            return;
//            throw new Exception("CP147. Card category cannot be empty (fiche->set_fiche_def)",EXC_PARAM_VALUE);
        }
        $sql="select *
             from
                   fiche
             natural join fiche_detail
	     join jnt_fic_attr on (jnt_fic_attr.fd_id=fiche.fd_id and fiche_detail.ad_id=jnt_fic_attr.ad_id)
             join attr_def on (attr_def.ad_id=fiche_detail.ad_id) where f_id= $1".
                " order by jnt_order";
        
        $Ret=$fiche->cn->exec_sql($sql, [$fiche->id]);
        if (($Max=Database::num_row($Ret))==0)
            return;
        for ($i=0; $i<$Max; $i++)
        {
            $row=Database::fetch_array($Ret, $i);
            $fiche->fiche_def=$row['fd_id'];
            $fiche->set_f_enable($row['f_enable']);
            $t=new Card_Property($fiche->cn);
            $t->ad_id=$row['ad_id'];
            $t->ad_text=$row['ad_text'];
            $t->av_text=$row['ad_value'];
            $t->ad_type=$row['ad_type'];
            $t->ad_size=$row['ad_size'];
            $t->ad_extra=$row['ad_extra'];
            $t->jnt_order=$row['jnt_order'];
            $fiche->attribut[$i]=$t;
        }
        $e=new Fiche_Def($fiche->cn, $fiche->fiche_def);
        $e->GetAttribut();

        if (sizeof($fiche->attribut)!=sizeof($e->attribut))
        {

            /*
             * !! Missing attribute
             */
            foreach ($e->attribut as $f)
            {
                $flag=0;
                foreach ($fiche->attribut as $g)
                {
                    if ($g->ad_id==$f->ad_id)
                        $flag=1;
                }
                if ($flag==0)
                {
                    // there's a missing one, we insert it
                    $t=new Card_Property($fiche->cn, $f->ad_id);
                    $t->av_text="";
                    $t->ad_text=$f->ad_text;
                    $t->jnt_order=$f->jnt_order;
                    $t->ad_type=$f->ad_type;
                    $t->ad_size=$f->ad_size;
                    $t->ad_id=$f->ad_id;
                    $t->ad_extra=$f->ad_extra;
                    $fiche->attribut[$Max]=$t;
                    $Max++;
                } // if flag == 0
            }// foreach
        }//missing attribut
    }

    /**
     * @brief input a property of a card
     * @param Fiche_Def $p_fiche_def
     * @return string HTML string with the right input type
     */
    function input($p_fiche_def=null)
    {

        $result=$this->build_input($p_fiche_def);


        $url='<td>'.$this->add_link($this->ad_id,$this->av_text).'</td>';
        $r="<TR>".
             td(_($result["label"]). $result['bulle'],
            ' class="'.$result['class'].'" ').
            td($result["input"]->input().$result["msg"]).
            $url.
            " </TR>";
        return $r;
    }

    /**
     * @brief Compute a HTML string in a TR element with information of this card property
     * 
     * @return string HTML into tr
     */
    function print()
    {
        $w=new IText();
        $w->table=1;
        $w->readOnly=true;
        $w->css_size="100%";
        $msg="";
        $bulle="";
        $ret="";
        $value=$this->av_text;
        if ($this->ad_id==21||$this->ad_id==22||$this->ad_id==20||$this->ad_id==31)
        {
            $bulle=Icon_Action::infobulle(21);
        }

        // Warning length quickcode
        if ($this->ad_id==ATTR_DEF_QUICKCODE)
        {
            $bulle=Icon_Action::warnbulle(76);
        }
        if ($this->ad_id==ATTR_DEF_NAME||$this->ad_id==ATTR_DEF_QUICKCODE)
        {
            $class=" input_text highlight info";
        }
        else
        {
            $class="input_text";
        }
        switch ($this->ad_type)
        {
            case 'select':
                $x=new ISelect();
                $x->value=$this->cn->make_array($this->ad_extra);
                $x->selected=$this->av_text;
                $value=$x->display();
                $w->value=$value;
                break;
            case 'check':
                $w=new InputSwitch("av_text".$this->ad_id);
                $w->value=$this->av_text;
                $w->value=(trim($w->value)=="")?1:$w->value;
                break;
            default:
                $w->value=$this->av_text;
        }
        $url="<td>".$this->add_link($this->ad_id,$this->av_text).'</td>';
        $ret.="<TR>".td(_($this->ad_text)." $bulle", ' class="'.$class.'" ').td($value." $msg",
                        'style="border:1px solid blue"').'<td>'.$url.'</td>'." </TR>";
        return $ret;
    }

    /*!
     * \brief  update all the data of the card , including f_enable. if we are in a transaction
     * we don't commit here , else if not then a transaction is started and committed . The member attributes 
     * $p_fiche->attribut will be saved into fiche_detail after transforming if needed. 
     * If a transaction is started if there is none, so updating a card is always in a transaction. 
     *
     * 
     */

    static function update(Fiche $p_fiche)
    {
        //transaction in the function or from the caller 
        $commit=false;
        try
        {
            // are we inside a transaction (between BEGIN - COMMIT )
            if ($p_fiche->cn->status()==PGSQL_TRANSACTION_IDLE)
            {
                $p_fiche->cn->start();
                $commit=true;
            }

            $p_fiche->cn->exec_sql("update fiche set f_enable=$1 where f_id=$2",
                    array($p_fiche->get_f_enable(), $p_fiche->id));

            $name = $p_fiche->strAttribut(ATTR_DEF_NAME);

            // parse the attribute
            foreach ($p_fiche->attribut as $value)
            {
                // retrieve jft_id to update table attr_value
                $sql=" select jft_id from fiche_detail where ad_id=$1 and f_id=$2";
                $Ret=$p_fiche->cn->exec_sql($sql, [$value->ad_id, $p_fiche->id]);

                // if attribute doesn't exist, then we insert one, 
                if (Database::num_row($Ret)==0)
                {
                    // we need to insert this new attribut , $jft_id contains the PK of fiche_detail
                    $jft_id=$p_fiche->cn->get_next_seq('s_jnt_fic_att_value');

                    $sql2="insert into fiche_detail(jft_id,ad_id,f_id,ad_value) values ($1,$2,$3,NULL)";

                    $ret2=$p_fiche->cn->exec_sql($sql2, array($jft_id, $value->ad_id, $p_fiche->id));
                }
                else
                {
                    $tmp=Database::fetch_array($Ret, 0);
                    // $jft_id contains the PK of fiche_detail
                    $jft_id=$tmp['jft_id'];
                }
                
                // Special traitement
                // quickcode , if already used in ledger , it cannot be changed
                if ($value->ad_id==ATTR_DEF_QUICKCODE)
                {
                    $used = $p_fiche->cn->get_value("select count(*) from jrnx where j_qcode= $1",[$value->av_text]);
                    if ($used == 0) {
                        $sql=sprintf("select update_quick_code(%d,'%s')", $jft_id, sql_string($value->av_text));
                        $p_fiche->cn->exec_sql($sql);
                    }
                    continue;
                }
                // name
                if ($value->ad_id==ATTR_DEF_NAME && noalyss_strlentrim($value->av_text)==0 )
                {
                        continue;
                }
                // account
                if ($value->ad_id==ATTR_DEF_ACCOUNT)
                {
                    $v=mb_strtoupper($value->av_text??"");
                    // 2 accounts given 
                    if (trim($v)!='')
                    {
                        if (strpos($v, ',')!=0)
                        {
                            $ac_array=explode(",", $v);
                            if (count($ac_array)<>2)
                                throw new Exception('Désolé, il y a trop de virgule dans le poste comptable '.h($v));
                            $part1=$ac_array[0];
                            $part2=$ac_array[1];
                            $part1=$p_fiche->cn->get_value('select format_account($1)', array($part1));
                            $part2=$p_fiche->cn->get_value('select format_account($1)', array($part2));

                            if (mb_strlen($part1)>40)
                                throw new Exception("CP475."._("Poste comptable trop long"), 1);
                            if (mb_strlen($part2)>40)
                                throw new Exception("CP476."._("Poste comptable trop long"), 1);

                            $acc_account1=new Acc_Account($p_fiche->cn, $part1);

                            if ($acc_account1->get_parameter("id")==-1)
                            {
                                $account_name=$name;
                                $acc_account1->set_parameter("pcm_lib", $account_name);
                                $acc_account1->set_parameter('pcm_direct_use', "Y");
                                $parent=$acc_account1->find_parent();
                                $acc_account1->set_parameter("pcm_val_parent", $parent);
                                $acc_account1->save();
                            }
                            // Check that the accounting can be used directly
                            if ($acc_account1->get_parameter('pcm_direct_use')=='N')
                            {
                                throw new Exception("CP493."._("Utilisation directe interdite du poste comptable $part1"));
                            }
                            // Part 2
                            $acc_account2=new Acc_Account($p_fiche->cn, $part2);

                            if ($acc_account2->get_parameter("id")==-1)
                            {
                                $account_name=$name;
                                $acc_account2->set_parameter("pcm_lib", $account_name);
                                $acc_account2->set_parameter('pcm_direct_use', "Y");
                                $parent=$acc_account2->find_parent();
                                $acc_account2->set_parameter("pcm_val_parent", $parent);
                                $acc_account2->save();
                            }

                            // Check that the accounting can be used directly
                            if ($acc_account2->get_parameter('pcm_direct_use')=='N')
                            {
                                throw new Exception("CP511."._("Utilisation directe interdite du poste comptable $part2"));
                            }
                            $v=$part1.','.$part2;
                        }
                        else
                        {
                            if (mb_strlen($v)>40)
                                throw new Exception("CP520."._("Poste comptable trop long"), 1);
                            $acc_account=new Acc_Account($p_fiche->cn, $v);
                            // Set default for new accounting
                            if ($acc_account->get_parameter("id")==-1)
                            {
                                $account_name=$name;
                                $acc_account->set_parameter("pcm_lib", $account_name);
                                // By Default can be used directly
                                $acc_account->set_parameter('pcm_direct_use', "Y");
                                $parent=$acc_account->find_parent();
                                $acc_account->set_parameter("pcm_val_parent", $parent);
                                $acc_account->save();
                            }

                            $acc_account=new Acc_Account($p_fiche->cn, $v);
                            if ($acc_account->get_parameter('pcm_direct_use')=='N')
                            {
                                throw new Exception("CP537."._("Utilisation directe interdite du poste comptable $v"));
                            }
                        }
                        $sql=sprintf("select account_insert(%d,'%s')", $p_fiche->id, $v);
                        try
                        {
                            $p_fiche->cn->exec_sql($sql);
                        }
                        catch (Exception $e)
                        {
                            throw new Exception("CP546."._("opération annulée")." ".$e->getMessage());
                        }
                        continue;
                    }
                    if (noalyss_strlentrim($v)==0)
                    {

                        $sql=sprintf("select account_insert(%d,null)", $p_fiche->id);
                        try
                        {
                            $Ret=$p_fiche->cn->exec_sql($sql);
                        }
                        catch (Exception $e)
                        {
                            throw new Exception("CP560."._("Erreur : Aucun compte parent ")."[$v]");
                        }

                        continue;
                    }
                }
                // TVA
                if ($value->ad_id==ATTR_DEF_TVA)
                {
                    // Verify if the rate exists, if not then do not update
                    if (noalyss_strlentrim($value->av_text)!=0)
                    {
                        if ($p_fiche->cn->get_value("select count(*) from tva_rate where tva_id=$1",[$value->av_text])==0)
                        {
                            continue;
                        }
                    }
                }
                // Normal traitement
                $sql="update fiche_detail set ad_value=$1 where jft_id=$2";
                $p_fiche->cn->exec_sql($sql, array(noalyss_strip_tags($value->av_text), $jft_id));
            }
            if ($commit)
            {
                $p_fiche->cn->commit();
            }
        }
        catch (Exception $e)
        {
            echo '<span class="error">'.
            $e->getMessage().
            '</span>';
            record_log("CP597.".$e->getMessage().$e->getTraceAsString());
           if ($commit) {         $p_fiche->cn->rollback(); }
            return;
        }
       
        return;
    }

    /**
     * @brief add a link
     * @param $p_ad_id
     * @param $p_text
     * @return false|mixed|string
     */
    private function add_link($p_ad_id,$p_text) {
        if ( $this->display_mode=="large" && $p_ad_id == ATTR_DEF_WEBSITE) {
            $url=linkTo($p_text);
        }elseif ( $this->display_mode=="large" && $p_ad_id == ATTR_DEF_EMAIL) {
            $url=mailTo($p_text);
        }elseif ( $this->display_mode=="large" && $p_ad_id == ATTR_DEF_FAX) {
            $url=faxTo($p_text);
        }else {
            return "";
        }
        return $url;
    }

    /**
     * @brief return the Property of an array of property with the right ad_id
     * @param int $attr_def_id the search attr_def_id
     * @param array $a_property array of Card_Property
     * @return mixed|null
     */
    static function findProperty($attr_def_id, $a_property)
    {
        $nb = count($a_property);
        for ($i = 0; $i < $nb; $i++) {
            if ($a_property[$i]->get_ad_id() == $attr_def_id) return $a_property[$i];
        }
        return null;
    }
}
