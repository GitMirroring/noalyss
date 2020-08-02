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
/**
 * @file 
 * @brief display a kind of select 
 */

/**
 * Display a kind of select
 * @include select-box-test.php
 */

// Copyright Author Dany De Bontridder danydb@aevalys.eu
class Select_Box
{

    var $id;
    var $item;
    private $cnt;
    private $filter; //!< allow a dynamic not case sensitive search
    var $default_value;
    private $position ; //!< change depending if we are in an absolute block or not
    
    /**
     * Default constructor
     * @param type $p_id javascript DOMid
     * @param type $value Label to display
     * @example select-box-test.php
     */
    function __construct($p_id, $value)
    {
        $this->id=$p_id;
        $this->item=array();
        $this->value=$value;
        $this->cnt=0;
        $this->default_value=-1;
        $this->style_box="";
	$this->filter="";
        $this->position="normal";
    }
    public function get_position()
    {
        return $this->position;
    }

    public function set_position($position)
    {
        if ( ! in_array($position,array("normal","in-absolute") ) ) {
            throw new Exception("SB0005",EXC_PARAM_VALUE);
        }
        $this->position=$position;
        return $this;
    }

   private function compute_position()
    {
        $list_id=sprintf('%s_list', $this->id);
        switch ($this->position)
        {
            case "normal":

                // Show when click
                $javascript=sprintf('$("%s_bt").onclick=function() {
                        try {
                           var newDiv=$("select_box%s");
                           var pos=$("%s_bt").cumulativeOffset();
                           newDiv.setStyle({display:"block",position:"fixed",top:pos.top+25+"px",left:pos.left+5+"px"});

                           if ( $("search_%s") ) { $("search_%s").focus();}

                        } catch(e) {
                             alert(e.message);
                        }
                       }
                        ', $this->id, $this->id, $this->id, $list_id, $list_id);
                // Hide when out of the zone
                $javascript.=sprintf('$("select_box%s").onmouseleave=function() {
                try {
                   var newDiv=$("select_box%s");
                   newDiv.setStyle({display:"none"});
                } catch(e) {
                     alert(e.message);
                }
               }',$this->id,$this->id);
                break;
            case "in-absolute":
                // Show when click
                $javascript=sprintf('
                    $("%s_bt").onclick=function() {
                        try {
                          
                            
                            if (! document.getElementById("select_box_content") ) {
                            
                                var newDiv=new Element("div");
                                newDiv.id="select_box_content";
                                document.body.appendChild(newDiv);
                                newDiv.addClassName("select_box");
                                $("select_box_content").onmouseleave=function() {
                                        try {
                                           var newDiv=$("select_box_content");
                                           newDiv.setStyle({display:"none"});
                                        } catch(e) {
                                             alert(e.message);
                                        }
                                       }
                            } else {
                                 var newDiv=document.getElementById("select_box_content");
                            }
                            newDiv.innerHTML=$("select_box%s").innerHTML;
                            var pos=$("%s_bt").cumulativeOffset();
                            var nTop=pos.top;
                            var viewport = document.viewport.getDimensions();
                            if ( nTop> viewport.height-newDiv.getHeight()-20) { nTop-=newDiv.getHeight()-5}
                            
                            newDiv.setStyle({display:"block",position:"absolute",top:nTop+"px",left:pos.left+5+"px","z-index":999});
                           
                           if ( $("search_%s") ) { $("search_%s").focus();}
                        } catch(e) {
                             alert(e.message);
                        }
                       }
                        ', $this->id, $this->id, $this->id, $list_id, $list_id);
               
                        break;
            
            default:
                break;
        }
        return $javascript;
    }

    function input()
    {
        $list_id=sprintf('%s_list',$this->id);
        
        // Show when click
        $javascript=$this->compute_position();
        
        

        // display the button
        printf('<input type="button" class="smallbutton icon " id="%s_bt" value="%s &#x25BE;" >',
                $this->id, $this->value);
        printf('<input type="hidden" id="%s" name="%s" value="%s">', $this->id,
                $this->id, $this->default_value);
        printf('<div class="select_box " id="select_box%s" style="%s">',
                $this->id, $this->style_box);

	// Show the filter if there is one, 
	if ( $this->filter != "" ) {
            
            echo HtmlInput::filter_list($list_id);
	}
        
        // Print the list of possible options
        printf('<ul id="%s">',$list_id);
        for ($i=0; $i<count($this->item); $i++)
        {
            if ($this->item[$i]['type']=="url")
            {
                printf('<li><a href="%s">%s</a></li>', $this->item[$i]['url'],
                        $this->item[$i]['label']);
            }
            else // For javascript
            if ($this->item[$i]['type']=="javascript")
            {
                printf('<li><a href="javascript:void(0)" onclick="%s">%s</a></li>',
                        $this->item[$i]['javascript'], $this->item[$i]['label']);
            }
            else if ($this->item[$i]['type']=="value")
            {
                printf('<li><a href="javascript:void(0)" onclick="%s">%s</a></li>',
                        $this->item[$i]['javascript'], $this->item[$i]['label']);
            }
            else if ($this->item[$i]['type']=="input") {
                $ok=new IButton("ok");
                $ok->value=$this->item[$i]['label'];
                $ok->javascript=$this->item[$i]['input']->javascript;
                printf('<li> %s %s</li>',
                        $this->item[$i]['input']->input(),
                        $ok->input()
                        );
            }
        }

        echo "</ul>";
        echo "</div>";

        // javascript : onclick on button
        echo "<script>";
        echo $javascript;
        echo "</script>";
    }

    function add_url($label, $url)
    {
        $this->item[$this->cnt]['label']=$label;
        $this->item[$this->cnt]['url']=$url;
        $this->item[$this->cnt]['type']="url";
        $this->cnt++;
    }

    function add_javascript($label, $javascript)
    {
        $this->item[$this->cnt]['label']=$label;
        $this->item[$this->cnt]['javascript']=$javascript.";$('select_box{$this->id}').hide()";
        $this->item[$this->cnt]['type']="javascript";
        $this->cnt++;
    }

    function add_value($label, $value)
    {
        $this->item[$this->cnt]['label']=$label;
        $this->item[$this->cnt]['update']=$value;
        $this->item[$this->cnt]['javascript']=sprintf(" $('%s').value='%s';$('%s_bt').value='%s';$('select_box%s').hide()",
                $this->id, $value, $this->id, $label, $this->id);
        $this->item[$this->cnt]['type']='value';
        $this->cnt++;
    }
    function add_input($p_label,HtmlInput $p_element) {
        /* $this->item[$this->cnt]['label']=$p_element->label;
        $this->item[$this->cnt]['value']=$p_element->value;
        $this->item[$this->cnt]['javascript']=$p_element->javascript;
         * 
         */
        $this->item[$this->cnt]['label']=$p_label;
        $this->item[$this->cnt]['input']=clone $p_element;
        $this->item[$this->cnt]['type']='input';
        $this->cnt++;
    }
    function set_filter($p_filter)
    {
      $this->filter=$p_filter;
    }
    function get_filter() {
      return $this->filter;
    }

}
