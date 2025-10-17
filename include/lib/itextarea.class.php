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
 * \brief Html Input
 */

/**
 * @class ITextarea
 * @brief Manage the TEXTAREA html element
 */
class ITextarea extends HtmlInput
{
    private $enrichText;
    function __construct($p_name = "", $p_value = "", $p_id = "")
    {
        parent::__construct($p_name, $p_value, $p_id);
        $this->style=' class="itextarea" ';
        $this->enrichText="plain";
    }

    /**
     * @brief return if enrichText is set to true or false
     * @return mixed
     */
    public function get_enrichText()
    {
        return $this->enrichText;
    }

    /**
     * @brief set enrichText  to plain or enrich , enrich for WYSIWYG function, plain, plain textarea and choose
     * to display a button to switch to a WYSIWYG
     * @param mixed $enrich plain , enrich (full option) , min (minimum options)
     */
    public function set_enrichText($enrich)
    {
        if ( ! in_array($enrich,['enrich','plain','full',"minimal","no-toolbar"])) {
            throw new \Exception("IT57.Invalid option [$enrich]");
        }
        if ( $enrich == 'enrich') $enrich='full';
        $this->enrichText = $enrich;
        return $this;
    }

    /*!\brief show the html  input of the widget
     * // style example style="height:15em;width: 80vw
    */
    public function input($p_name=null,$p_value=null)
    {
        $this->name=($p_name==null)?$this->name:$p_name;
        $this->value=($p_value==null)?$this->value:$p_value;
        $this->id=($this->id=="")?$this->name:$this->id;
        if ( $this->readOnly==true) return $this->display();
        if ( empty($this->id)) $this->id=$this->name;
        
        if ( $this->enrichText == "plain" ) {
            $r="";
            $r.='<TEXTAREA '.$this->style.'  name="'.$this->name.'" id="'.$this->id.'"';
            $r.='>';
            $r.=$this->value;
            $r.="</TEXTAREA>";
        } else {

            $r=<<<EOF
            <textarea name="{$this->name}" id="{$this->id}" {$this->style}>{$this->value}</textarea>
<script type="text/javascript">
                (function() {
                   noalyss.activate_tinymce('{$this->name}','{$this->enrichText}');
                })();
            </script>
EOF;
        }
   
        return $r;
    }


    /*!\brief print in html the readonly value of the widget*/
    public function display()
    {
        if ( $this->enrichText=="plain")
        {
            $r='<p>';
            $r.=h($this->value);
            $r.=sprintf('<input type="hidden" name="%s" value="%s">',
                        $this->name,h($this->value));
            $r.='</p>';
        } elseif ($this->enrichText=='enrich' )  {
            $r=$this->value;
        }
        return $r;

    }
}
