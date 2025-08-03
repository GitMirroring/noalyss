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

/*!
 * \file
 * \brief Html Input , create a tag <SELECT> ... </SELECT> 
 * if readonly == true then display the label corresponding to the selected value
 * You can use also $this->rowsize  to specify the number of lines to display
 * 
 * @see Database::make_array
 */

/*!
 * \class ISelect
 * \brief Html Input , create a tag <SELECT> ... </SELECT>
 * if readonly == true then display the label corresponding to the selected value
 * You can use also $this->rowsize  to specify the number of lines to display
 *
 * @see Database::make_array
 */
class ISelect extends HtmlInput
{
    /**
     * Constructor , $p_value is supposed to be an array
     * @param string $p_name name of the element
     * @param array $p_value
     * @param DOMID $p_id
     */
    function __construct($p_name="", $p_value="", $p_id="")
    {
        parent::__construct($p_name, $p_value, $p_id);
        if ( $p_value =="" ) 
            { 
                $this->value=[]; 
            } else {
                $this->value=$p_value;
            }
    }
    /*!
     * \brief      show the html  input of the widget
     * \note to use a OPTGROUP, the key "value" must be null , it is important
     * to note that it is needed to use for opening and closing the element
     * \code
      $select->value=array( 
        array ("value"=>null,"label"=>"Groupe 1"),
        array ("value"=>1,"label"=>"Element 1"),
        array ("value"=>2,"label"=>"Element 2"),
        array ("value"=>null,"label"=>"END Groupe 1"), // not displaid
        array ("value"=>null,"label"=>"Groupe 2"),
        array ("value"=>1,"label"=>"Element 1"),
        array ("value"=>2,"label"=>"Element 2"),
        array ("value"=>null,"label"=>"END group Groupe 1")// not displaid
      );
     * 
     * \endcode

     * 
     *      */
    public function input($p_name=null,$p_value=null)
    {
        $this->name=($p_name==null)?$this->name:$p_name;
        $this->value=($p_value==null)?$this->value:$p_value;
        if ( $this->readOnly==true) return $this->display();
        $style=(isset($this->style))?$this->style:"";
		$this->id=($this->id=="")?$this->name:$this->id;

        $disabled=($this->disabled==true)?"disabled":"";
        $rowsize = (isset ($this->rowsize)) ? ' size = "'.$this->rowsize.'"':"";
        
        $r="";

        $a="<SELECT   id=\"$this->id\" NAME=\"$this->name\" $style $this->javascript $disabled $rowsize>";
        if (empty($this->value)) return '';
        // var $start_group boolean , true the element OPTGROUP starts, false, it ends
        $start_group=false;
        for ( $i=0;$i<sizeof($this->value);$i++)
        {
            // open the element optgroup
            if ($this->value[$i]['value']===null && !$start_group) {
                $start_group=true;
                $a.=sprintf('<optgroup label="%s">', htmlspecialchars($this->value[$i]['label']));
                continue;
            }
            // close the element optgroup
            if ($this->value[$i]['value']==null && $start_group) {
                $start_group=false;
                $a.='</optgroup >';
                continue;
            }
            $checked=($this->selected==$this->value[$i]['value'])?"SELECTED":"";
            
            $a.='<OPTION VALUE="'.$this->value[$i]['value'].'" '.$checked.'>';
            $a.=strip_tags($this->value[$i]['label']);
        }
        $a.="</SELECT>";
        if ( $this->table == 1 )		  $a='<td>'.$a.'</td>';

        return $r.$a;
    }
    /*!\brief print in html the readonly value of the widget*/
    public function display()
    {
        $r="";
        if ($this->value == null) {
            $this->value=array();
        }
        for ( $i=0;$i<sizeof($this->value);$i++)
        {
            if ($this->selected==$this->value[$i]['value'] )
            {
                $r=htmlentities($this->value[$i]['label'],ENT_QUOTES|ENT_HTML5,'UTF-8',true);

            }
        }
      // $r='<span class="input_text_ro">'.$r.'</span>';
	if ( $this->table == 1 )		  $r='<td>'.$r.'</td>';
        return $r;
    }
    /*!\brief print in html the readonly value of the widget*/
    public function get_value()
    {
        $r="";
        for ( $i=0;$i<sizeof($this->value);$i++)
        {
            if ($this->selected==$this->value[$i]['value'] )
            {
                $r=$this->value[$i]['label'];

            }
        }
        return $r;
    }
   /**
    * @brief set the value of an ISelect with the array , this array
    * is bidimensional , the first dimension is the code to store and the second
    * is the label to display.
    * Example
    * @code
    * array(array('M'=>'Mister'),array('Ms'=>'Miss'));
    * // will be turned into 
    * array( array("value"=>'M,"label"=>"Mister")...)
    * @endcode
    * @param array $p_array
    */
   public function transform($p_array) {
        if (! is_array($p_array) || count($p_array)==0) return ;
        $a_ret=array();
        foreach ($p_array as $key=>$value) {
            $a_ret['value']=$key;
            $a_ret['label']=$value;
            $this->value[]=$a_ret;
        }
    }
    function set_value($p_string) {
        $this->selected=$p_string;
    }
    static public function test_me()
    {
    }
}
