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
 * \brief Html Input Text
 */
/*!
 * \class IText
 * \brief Html Input Text
 * member : 
 *  - placeholder (string) placeholder
 *  - title (string) title of the HTML ELT
 *  - autofocus (bool) true to have an autofocus
 *  - css_size (string) size in the specified unit ex: 20%
 *  - pattern (string) pattern for HTML
 *  - maxlength (int) max length of the fied
 *  - size (int) size without unit 
 *  - require (bool) is element require
 */
class IText extends HtmlInput
{
    var $title;
    var $autofocus;
    var $css_size;
    var $pattern; /*!< $pattern HTML pattern */
    var $maxlength; /*!< HTML maxlength */
    var $require ; /*!< $require (bool)*/
    protected $datalist; /*!< $datalist (double array) 
     * each row has a key (value) and a label used with make_datalist */
     
    function __construct($name='',$value='',$p_id="")
    {
        parent::__construct($name,$value,$p_id);
        $this->title="";
        $this->placeholder="";
        $this->extra="";
        $this->style=' class="input_text" ';
        $this->autofocus=false;
        $this->require=false;
        $this->css_size="";
        $this->pattern="";
        $this->maxlength="";
        $this->datalist=null;
    }
    /*!
    \brief show the html  input of the widget
    */
    public function input($p_name=null,$p_value=null)
    {
        $this->name=($p_name==null)?$this->name:$p_name;
        $this->value=($p_value==null)?$this->value:$p_value;
        if ( $this->readOnly==true) return $this->display();
	$this->id=($this->id=="")?$this->name:$this->id;

	// Double quote makes troubles 
	$this->value=noalyss_str_replace('"','',$this->value);

    // compute attribute used by javascript
        $strAttribute=$this->get_node_attribute();
        $t= 'title="'.$this->title.'" ';
        $autofocus=($this->autofocus)?" autofocus ":"";
        $require=($this->require)?"required":"";
        
         // var $pattern regex to match the INPUT TEXT
        $pattern="";
        if ($this->pattern != "") {
            $pattern= sprintf( 'pattern="%s"',$this->pattern);
        }
        
        // var maxlength HTML attribute 
        $maxlength = "";
        if ($this->maxlength !="" ) {
            $maxlength=sprintf(' maxlength="%s" ',$this->maxlength);
        }
        $datalist=(empty($this->datalist ))?"":sprintf('list="dl_%s"',$this->id);
        if ( ! isset ($this->css_size) || empty ($this->css_size))
        {
            
            $r=  sprintf('<INPUT TYPE="TEXT" %s id="%s" name="%s" value="%s" placeholder="%s" title="%s"
                     size="%s"   %s %s  %s %s %s %s %s %s>
                    ',$this->style,
                    $this->id,
                    $this->name,
                    htmlentities($this->value, ENT_COMPAT|ENT_QUOTES, "UTF-8"),
                    $this->placeholder,
                    $this->title,
                    $this->size,
                    $this->javascript,
                    $this->extra,
                    $autofocus,
                    $require,
                    $strAttribute,
                    $pattern,
                    $maxlength,
                    $datalist
                    );
        } else {
            $r=  sprintf('<INPUT TYPE="TEXT" %s id="%s" name="%s" value="%s" placeholder="%s" title="%s"
                     style="width:%s ;"  %s %s  %s %s %s %s>
                    ',$this->style,
                    $this->id,
                    $this->name,
                     htmlentities($this->value, ENT_COMPAT|ENT_QUOTES, "UTF-8"),
                    $this->placeholder,
                    $this->title,
                    $this->css_size,
                    $this->javascript,
                    $this->extra,
                    $autofocus,
                    $require,
                    $strAttribute,
                    $pattern,
                    $maxlength,
                    $datalist
                    );
        }

        /* add tag for column if inside a table */
        if ( $this->table == 1 )		  $r='<td>'.$r.'</td>';
        $r.=$this->make_datalist();
        return $r;

    }
    public function get_datalist() {
        return $this->datalist;
    }

    public function set_datalist($datalist) {
        $this->datalist = $datalist;
        return $this;
    }

        /*!\brief print in html the readonly value of the widget*/
    public function display()
    {
        $t= ((isset($this->title)))?'title="'.$this->title.'"   ':' ';

        $extra=(isset($this->extra))?$this->extra:"";
        $strAttribute=$this->get_node_attribute();
        $readonly=" readonly ";
        $this->value=htmlentities($this->value??"", ENT_COMPAT|ENT_QUOTES, "UTF-8");
        $this->style=' class="input_text_ro" ';
         if ( ! isset ($this->css_size))
        {
        $r='<INPUT '.$this->style.' TYPE="TEXT" id="'.
           $this->id.'"'.$t.
           'NAME="'.$this->name.'" VALUE="'.$this->value.'"  '.
           'SIZE="'.$this->size.'"  '.$this->javascript." $readonly $this->extra  $strAttribute>";
        } else {
               $r='<INPUT '.$this->style.' TYPE="TEXT" id="'.
           $this->id.'"'.$t.
           'NAME="'.$this->name.'" VALUE="'.$this->value.'"  '.
          ' style="width:'.$this->css_size.'" '.$this->javascript." $readonly  $this->extra $strAttribute>";
        }

        /* add tag for column if inside a table */
        if ($this->table == 1) {
            $r = '<td>' . $r . '</td>';
        }

        return $r;

    }
    function set_require($p_boolean)
    {
        $this->require=$p_boolean;
    }
    static public function test_me()
    {
    }
    /**
     * @brief add a datalist 
     * @param $array (double array) each row has 2 keys 0 => stored value 1=>label
     * @return string
     */
    protected function make_datalist()
    {
        if ( empty($this->datalist)) return "";
        $r=sprintf('<datalist id="dl_%s"">',$this->id);
        foreach ($this->datalist as $item) {
            $r.=sprintf('<option value="%s">%s %s</option>'
                ,$item[0],$item[1]
                ,htmlentities($item[1]));
        }
        $r.='</datalist>';
        return $r;
    }
}
