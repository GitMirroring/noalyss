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

/**\file
 * \brief Input HTML for the card show buttons
 *
 */

/**
 * \brief Input HTML for the card show buttons, in the file, you have to add card.js
 * How to use :
 * - label is the label in the button
 * - extra contents the type (all, deb or cred, a list of FD_ID between parent.  or a SQL clause
 * - attribute are the attribute to set (via ajax). The ledger is either a attribute (jrn) or a
 *  hidden field in the document, if none are set, there is no filter on the ledger
 * \note you must in a hidden field gDossier (dossier::hidden)
 * \see ajaxFid
 * \see card.js
 * \see fid.php
 * \see fid_card.php
 * \see ajax_card.php
 *
 * Set the hidden field or input field to be set by javascript with the function set_attribute
 * call the input method. After selecting a value the update_value function is called. If you need
 * to modify the queryString before the request is sent, you'll use the set_callback; the first
 * parameter is the INPUT field and the second the queryString, the function must returns a
 * queryString
 * \code
  // insert all the javascript files
  echo js_include('prototype.js');
  echo js_include('scriptaculous.js');
  echo js_include('effects.js');
  echo js_include('controls.js');

  //
  $W1=new ICard();
  $W1->label="Client ".Icon_Action::infobulle(0) ;
  $W1->name="e_client";
  $W1->tabindex=3;
  $W1->value=$e_client;
  $W1->table=0;
  // If double click call the javascript fill_ipopcard
  $W1->set_dblclick("fill_ipopcard(this);");

  // Type of card : deb, cred or all
  $W1->set_attribute('typecard','deb');

  $W1->extra='deb';
O
  // Add the callback function to filter the card on the jrn
  $W1->set_callback('filter_card');

  // when value selected in the autcomplete
  $W1->set_function('fill_data');

  // when the data change
  $W1->javascript=sprintf(' onchange="fill_data_onchange(\'%s\');" ',
  $W1->name);

  // name of the field to update with the name of the card
  $W1->set_attribute('label','e_client_label');
  $client_label=new ISpan();
  $client_label->table=0;
  $f_client=$client_label->input("e_client_label",$e_client_label);

  $f_client_qcode=$W1->input();

  // Search button for card
  $f_client_bt=$W1->search();
 * \endcode
  For searching a card, you need a popup, the script card.js and set
  the values for card, popup filter_card callback
  @code
  $card=new ICard('acc');
  $card->name="acc";
  $card->extra="all";
  $card->set_attribute('typecard','all');
  $card->set_callback('filter_card');

  echo $card->input();
  echo $card->search();
  // example 2
  $w=new ICard("av_text".$attr->ad_id);
  // filter on frd_id
  $sql=' select fd_id from fiche_def where frd_id in ('.FICHE_TYPE_CLIENT.','.FICHE_TYPE_FOURNISSEUR.','.FICHE_TYPE_ADM_TAX.')';
  $filter=$this->cn->make_list($sql);
  $w->set_attribute('ipopup','ipopcard');
  $w->set_attribute('typecard',$filter);
  $w->set_attribute('inp',"av_text".$attr->ad_id);
  $w->set_attribute('label',"av_text".$attr->ad_id."_label");

  $w->extra=$filter;
  $w->extra2=0;
  $label=new ISpan();
  $label->name="av_text".$attr->ad_id."_label";
  $msg.=td($w->search().$label->input());
  @endcode
 */
require_once NOALYSS_INCLUDE.'/lib/function_javascript.php';

class ICard extends HtmlInput
{
    //!< $fct ,by default it is update_value called BEFORE the querystring is send
    var $fct;
    
    //!< $dblclick action if double click
    var $dblclick;
    
    //!< $callback , 
    var $callback;
    
    //!< $choice 
    var $choice;
    
    //!< $indicator, text displaid when searching
    var $indicator;
    
    //!< $choice_create 1 , display a (+) button , default 1
    var $choice_create;
    
    //!< $autocomplete : 1 enable - 0 disable
    var $autocomplete;
    
    //!< $style supplemental CSS 
    var $style='  ';
    
    //!< $accvis account_visible =1 otherwise 0, default 1
    var $accvis; 
    
    //!< $limit Max of row show
    var $limit;
    
    //!< $amount_from_type : accountancy , sell or purchase price
    var $amount_from_type;
    
    //!< $typecard : type of card
    var $typecard;
    
    //!< $autocomplete_file , ajax file used for autocompletion (see Ajax.Autocompleter)
    private $autocomplete_file;
    
    protected $after_clean; //!< javascript to call after cleaning the INPUT TEXT
    function __construct($name="", $value="", $p_id="")
    {
        parent::__construct($name, $value);
        $this->fct='update_value';
        $this->dblclick='';
        $this->callback='null';
        $this->javascript='';
        $this->id=($p_id!="")?$p_id:$name;
        $this->choice=null;
        $this->indicator=null;
        $this->choice_create=1;
        $this->autocomplete=1;
        $this->style='  ';
        $this->accvis=1; //!< account_visible =1 otherwise 0
        $this->limit=12; //!< Max of row show
        $this->amount_from_type=''; //!< in the follow up ,when a card is selected you take Prix Vente or Prix Achat 
        $this->typecard='all';
        $this->autocomplete_file="fid_card.php";
        $this->after_clean="";
    }
    public function getAfter_clean()
    {
        return $this->after_clean;
    }

    public function setAfter_clean($after_clean)
    {
        $this->after_clean = $after_clean;
        return $this;
    }

        /**
     * Function javascript by default it is update_value called BEFORE the querystring is send in ajax
     * @return type
     */
    public function get_fct()
    {
        return $this->fct;
    }
    /**
     * @brief id_of_div_to_populate with the output of the autocomplete_file, it is
     * the autocomplete div presenting a list of possible choices. Default {this->id}_choices
     * 
     * @return html string (
     */
    public function get_choice()
    {
        if ( $this->choice==null) {
            return sprintf("%s_choices", $this->id);
        }
        return $this->choice;
    }
    /**
     * Id of the element to show that it is seaching
     * @return type
     */
    public function get_indicator()
    {
        return $this->indicator;
    }
   /**
     * @brief 1 if you want to create automatically the autocomplete DIV to fill with the the output of 
    * "autocomplete_file"  or 0 if this DIV is created explicitly
     * 
     * @return this
     */
    public function get_choice_create()
    {
        return $this->choice_create;
    }
    /**
     * @brief 1 autocomplete enable ; 0 autocomplete disable
     * @return type
     */
    public function get_autocomplete()
    {
        return $this->autocomplete;
    }
    /**
     * CSS Style  of the INPUT TEXT element
     * @return type
     */
    public function get_style()
    {
        return $this->style;
    }
    /***
     * @brief $accvis account_visible =1 otherwise 0, default 1
     */
    public function get_accvis()
    {
        return $this->accvis;
    }

    public function get_limit()
    {
        return $this->limit;
    }
    /**
     * @brief amount_from_type : accountancy , sell or purchase price
     * 
     */
    public function get_amount_from_type()
    {
        return $this->amount_from_type;
    }

    public function get_typecard()
    {
        return $this->typecard;
    }
    /**
     * @brief php file to call to complete info from the card
     * @param string $autocomplete_file
     * @see fid_card.php
     * @return $this
     */
    public function get_autocomplete_file()
    {
        return $this->autocomplete_file;
    }
    /***
    * @brief Function javascript by default it is update_value called BEFORE the querystring is send in ajax
    *@see afterUpdateElement
    */
    public function set_fct($fct)
    {
        $this->fct=$fct;
        return $this;
    }
   /**
     * @brief id_of_div_to_populate with the output of the autocomplete_file, it is
     * the autocomplete div presenting a list of possible choices. Default {this->id}_choices
     * 
     * @return html string (
     */
    public function set_choice($choice)
    {
        $this->choice=$choice;
        return $this;
    }
    /**
     * @brief Id of the element to show that it is seaching
     */
    public function set_indicator($indicator)
    {
        $this->indicator=$indicator;
        return $this;
    }
   /**
     * @brief 1 if you want to create automatically the autocomplete DIV to fill with the the output of 
    * "autocomplete_file"  or 0 if this DIV is created explicitly
     * 
     * @return this
     */
    public function set_choice_create($choice_create)
    {
        $this->choice_create=$choice_create;
        return $this;
    }
    /**
     * @brief 1 autocomplet enable ; 0 autocomplete disable
     * @return type
     */
    public function set_autocomplete($autocomplete)
    {
        $this->autocomplete=$autocomplete;
        return $this;
    }
    /**
     * CSS Style  of the INPUT TEXT element
     * @return type
     */
    public function set_style($style)
    {
        $this->style=$style;
        return $this;
    }
    /***
     * @brief $accvis account_visible =1 otherwise 0, default 1
     */
    public function set_accvis($accvis)
    {
        $this->accvis=$accvis;
        return $this;
    }

    public function set_limit($limit)
    {
        $this->limit=$limit;
        return $this;
    }
    /**
     * @brief amount_from_type : accountancy , sell or purchase price
     * @parameter $amount_from_type ACH VEN or GL
     * 
     */
    public function set_amount_from_type($amount_from_type)
    {
        if ( ! in_array($amount_from_type,array("ACH","VEN","GL"))) {
            throw new Exception('icard.305 '.sprintf("[%s]",$amount_from_type));
        }
        $this->amount_from_type=$amount_from_type;
        return $this;
    }
    /**
     * argument "e" passed to autocomplete_file ,
     * @see fid_card.php
     * @param type $typecard
     * @return $this
     */
    public function set_typecard($typecard)
    {
        $this->typecard=$typecard;
        return $this;
    }
    /**
     * @brief php file to call to complete info from the card
     * @param string $autocomplete_file
     * @see fid_card.php
     * @return $this
     */
    public function set_autocomplete_file($autocomplete_file)
    {
        $this->autocomplete_file=$autocomplete_file;
        return $this;
    }

    /**
     * @brief in the search box, the accounting will be shown it is the default
     */
    function show_accounting() {
        $this->accvis=1;
    }

    /**
     * @brief in the search box, the accounting will be hidden
     */
    function hide_accounting()
    {
        $this->accvis=0;
    }

  
    /**
     * \brief set the javascript callback function
     * by default it is update_value called BEFORE the querystring is send
     *
     * \param $p_name callback function name
     */

    function set_callback($p_name)
    {
        $this->callback=$p_name;
    }

    /**\brief set the javascript callback function
     * by default it is update_value called AFTER an item has been selected
     * \param $p_name callback function name
     */

    function set_function($p_name)
    {
        $this->fct=$p_name;
    }

    /**
     * \brief return the html string for creating the ipopup, this ipopup
     * can be used for adding, modifying or display a card
     * @note ipopup is obsolete, the popin is created by javascript
     * \param $p_name name of the ipopup, must be set after with set_attribute
      \code
      $f_add_button=new IButton('add_card');
      $f_add_button->label='Créer une nouvelle fiche';
      $f_add_button->set_attribute('ipopup','ipop_newcard');
      $f_add_button->set_attribute('filter',$this->get_all_fiche_def ());
      $f_add_button->javascript=" select_card_type(this);";
      $str_add_button=$f_add_button->input();

      \endcode
     * \return html string
     * \note must be one of first instruction on a new page, to avoid problem
     * of position with IE
     */

    static function ipopup($p_name)
    {
        $ip_card=new IPopup($p_name);
        $ip_card->drag=true;
        $ip_card->set_width('45%');
        $ip_card->title='Fiche ';
        $ip_card->value='';

        return $ip_card->input();
    }

    /**\brief set the extra javascript property for a double click on
     *  INPUT field
     * \param $p_action action when a double click happens
     * \note the $p_action cannot contain a double quote
     */

    function set_dblclick($p_action)
    {
        $this->dblclick=$p_action;
    }

    /**\brief show the html  input of the widget */

    public function input($p_name=null, $p_value=null)
    {
        if ($p_name==null&&$this->name=="")
            throw (new Exception(_('Le nom d une icard doit être donne')));
        $this->value=($p_value==null)?$this->value:$p_value;
        if ($this->readOnly==true)
            return $this->display();

        $this->id=($this->id=="")?$this->name:$this->id;
        $this->choice=($this->choice==null)?sprintf("%s_choices", $this->id):$this->choice;
        $this->indicator=($this->indicator==null)?sprintf("%s_ind", $this->id):$this->indicator;
        $attr=$this->get_js_attr();

        $label='';
        if ($this->dblclick!='')
        {
            $e=sprintf(' ondblclick="%s" ', $this->dblclick);
            $this->dblclick=$e;
        }
        
            $input='<div class="d-none d-lg-inline">'.
                Icon_Action::clean_zone(uniqid("remove"),"clean_Fid('{$this->id}');{$this->after_clean}").
                "</div>";
             
        $input.=sprintf('
            <INPUT TYPE="Text"  class="input_text icard"  
                 NAME="%s" ID="%s" VALUE="%s"  %s %s  %s>',
                $this->name, $this->id, $this->value, 
                $this->dblclick, $this->javascript, $this->style
        );
        if ($this->autocomplete==1)
        {
            // --- indicator 
            $this->indicator="ind_".$this->id;
            $ind=sprintf('<span id="%s" class="autocomplete" style="position:absolute;display:none;margin-left:-20px"><img src="image/ajax-loader.gif" alt="Chargement..."/></span>',
                    $this->indicator);
           // $this->indicator="null";
            
            $div=($this->choice_create==1)?sprintf('<div id="%s"  class="autocomplete"></div>',
                            $this->choice):"";

            $query=http_build_query([ 'gDossier'=>Dossier::id(),'e'=>$this->typecard,'limit'=>$this->limit]);
            
            $javascript=sprintf('try { new Ajax.Autocompleter("%s","%s","%s?%s",'.
                    '{paramName:"FID",minChars:1,indicator:%s, '.
                    'callback:%s, '.
                    'limit:%s,'.
                    ' afterUpdateElement:%s});} catch (e){alert(e.message);};',
                    $this->id, $this->choice, $this->autocomplete_file,$query, $this->indicator,
                    $this->callback, $this->limit, $this->fct);

            $javascript=create_script($javascript.$this->dblclick);

            $r=$label.$input.$attr.$ind.$div.$javascript;
        }
        else
        {
            $r=$label.$input;
        }
        if ($this->table==1)
            $r=td($r);
        return $r;
    }

    /**
       \brief print in html the readonly value of the widget */

    public function display()
    {
        $r=sprintf('         <INPUT TYPE="hidden" NAME="%s" id="%s" VALUE="%s" SIZE="8">',
                $this->name, $this->name, $this->value
        );
        $r.='<span>'.$this->value.'</span>';
        return $r;
    }

    /**
     * @brief return a string containing the button for displaying
     * a search form. When clicking on the result, update the input text file
     * the common used attribute as
     *   - jrn   the ledger
     *   - label the field to update
     *   - name name of the input text
     *   - price amount
     *   - tvaid
     *   - typecard (deb, cred, filter or list of value)
     * will be set
     * if ICard is in readOnly, the button disappears, so the return string is empty
      \code
      // search ipopup
      $search_card=new IPopup('ipop_card');
      $search_card->title=_('Recherche de fiche');
      $search_card->value='';
      echo $search_card->input();

      $a=new ICard('test');
      $a->search();

      \endcode
     * \see ajax_card.php
     * \note the ipopup id is hard coded : ipop_card
     * @return HTML string with the button
     */
    function search()
    {
        if ($this->readOnly==true)
            return '';
        if (!isset($this->id))
            $this->id=$this->name;
        $a="";
        foreach (array('typecard', 'jrn', 'label', 'price', 'tvaid', 'accvis','amount_from_type') as
                    $att)
        {
            if (isset($this->$att))
                $a.="this.".$att."='".$this->$att."';";
        }
        if (isset($this->id)&&$this->id!="")
            $a.="this.inp='".$this->id."';";
        else
            $a.="this.inp='".$this->name."';";
        $a.="this.popup='ipop_card';";
        $javascript=$a.' search_card(this);return false;';
        
        
        $button=Icon_Action::button_magnifier(uniqid(),$javascript);
        return $button;
    }

    static public function test_me()
    {
        $_SESSION[SESSION_KEY.'isValid']=1;
        $a=new ICard('testme');
        $a->extra="all";
        $a->set_attribute('label', 'ctl_label');
        $a->set_attribute('tvaid', 'ctl_tvaid');
        $a->set_attribute('price', 'ctl_price');
        $a->set_attribute('purchase', 'ctl_purchase');
        $a->set_attribute('type', 'all');
        echo <<<EOF
	  <div id="debug" style="border:solid 1px black;overflow:auto"></div>
	  <script type="text/javascript" language="javascript"  src="js/prototype.js">
	  </script>
	  <script type="text/javascript" language="javascript"  src="js/scriptaculous.js">
	  </script>
	  <script type="text/javascript" language="javascript"  src="js/effects.js">
	  </script>
	  <script type="text/javascript" language="javascript"  src="js/controls.js">
	  </script>
	  <script type="text/javascript" language="javascript"  src="js/ajax_fid.js">
	  </script>
	  <script type="text/javascript" language="javascript"  >
	  function test_value(text,li)
	  {
	    alert("premier"+li.id);

	    str="";
	    str=text.id+'<hr>';
	    if ( text.js_attr1)
	      {
		str+=text.js_attr1;
		str+='<hr>';
	      }
	    if ( text.js_attr2)
	      {
		str+=text.js_attr2;
		str+='<hr>';
	      }
	    if ( text.js_attr3)
	      {
		str+=text.js_attr3;
		str+='<hr>';
	      }
	    for (var i in text)
	      {
		str+=i+'<br>';
	      }

	    // $('debug').innerHTML=str;
	    ajaxFid(text);
	  }
	</script>

EOF;
        echo "<form>";
        $l=new IText('ctl_label');
        $t=new IText('ctl_tvaid');
        $p=new IText('ctl_price');
        $b=new IText('ctl_purchase');

        echo "Label ".$l->input().'<br>';
        echo "Tva id  ".$t->input().'<br>';
        echo "Price ".$p->input().'<br>';
        echo "Purchase ".$b->input().'<br>';

        if (isset($_REQUEST['test_select']))
            echo HtmlInput::hidden('test_select', $_REQUEST['test_select']);
        $a->set_function('test_value');
        $a->javascript=' onchange="alert(\'onchange\');" onblur="alert(\'onblur\');" ';
        echo $a->input();
        echo dossier::hidden();
        echo HtmlInput::submit('Entree', 'entree');
        echo '</form>';
        echo <<<EOF
EOF;
    }

}
