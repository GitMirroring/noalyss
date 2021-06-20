<?php


/*
 * Copyright (C) 2018 Dany De Bontridder <dany@alchimerys.be>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


/**
 * @file 
 * @brief Display the tabs 
 * 
 */

/**
 * @brief Display the tabs 
 * @see Html_Tab
 * 
 */

class Output_Html_Tab
{

    private $a_tabs; //!< array of html tabs
    private $class_tab; //!< for normal tab
    private $class_tab_selected; //!< for class_tab_selected
    private $mode; //!< mode default tabs
    private $class_tab_main; //! CSS class for the UL tag
    private $class_anchor; //! CSS class for the A tag (anchor) default empty
    private $class_div; //! CSS class for the DIV containing the UL default empty
    private $class_content_div; //! CSS class for the DIV with content, default empty
    private $class_comment ; //! CSS class for the comment default "tabs"
    
    /**
     *@example html_tab.test.php
     */
    function __construct()
    {
        $this->a_tabs=[];
        $this->class_tab="tabs";
        $this->class_tab_main="tabs";
        $this->class_tab_selected="tabs_selected";
        $this->set_mode("tab");
        $this->class_anchor="";
        $this->class_div="";
        $this->class_content_div="";
        $this->class_comment="tabs";
    }
    /**
     * @brief CSS class for the comment default "tabs"
     * @return type
     */
    public function get_class_comment()
    {
        return $this->class_comment;
    }
    /**
     * @brief CSS class for the comment default "tabs"
     * @return type
     */

    public function set_class_comment($class_comment)
    {
        $this->class_comment=$class_comment;
        return $this;
    }

         /**
      * @brief CSS class for the DIV with content (default empty)
      */
    public function get_class_content_div()
    {
        return $this->class_content_div;
    }
    /**
     *@brief CSS class for the DIV with content (default empty)
     * 
     * @param string $class_content_div
     */
    public function set_class_content_div($class_content_div)
    {
        $this->class_content_div=$class_content_div;
        return $this;
    }

        /**
     * @brief  CSS class for the DIV containing the UL
     * @return string
     */
    public function get_class_div()
    {
        return $this->class_div;
    }
    
    /**
     * @brief  CSS class for the DIV containing the UL
     * @return string
     */

    public function set_class_div($class_div)
    {
        $this->class_div=$class_div;
        return $this;
    }

        /**
     * @brief CSS class for the A tag (anchor)
     * @return type
     */
    public function get_class_anchor()
    {
        return $this->class_anchor;
    }
    /**
     * @brief CSS class for the A tag (anchor)
     * @param type $anchor_class
     */
    public function set_class_anchor($anchor_class)
    {
        $this->class_anchor=$anchor_class;
        $this;
    }

        /**
     * @brief get the CSS class for the UL element
     * @return type
     */
    public function get_class_tab_main()
    {
        return $this->class_tab_main;
    }

    /**
     * @brief set the CSS class for the UL element
     * @return this
     */

    public function set_class_tab_main($class_tab_main)
    {
        $this->class_tab_main=$class_tab_main;
        return $this;
    }

    /**
     * @brief get the mode , possible value are row or tabs , with mode = row , the
     * class_content_div is set to row
     * @return mixed
     */
    public function get_mode()
    {
        return $this->mode;
    }

    /**
     * set the mode , possible values are row or tabs
     * @param mixed $mode
     */
    public function set_mode($mode)
    {
        if ($mode != "row" && $mode != "tab") {
            throw new Exception(_("OUTPUTHTML070 Mode invalide"));
        }
        $this->mode = $mode;
        if ($mode == "row") {
            $this->set_class_tab_selected("tab_row_selected");
            $this->set_class_tab("tab_row");
        }
        if ( $mode == "tab") {
            $this->set_class_tab_selected("tabs_selected");
            $this->set_class_tab("tabs");

        }
        return $this;
    }

    /**
     * Add Html_Tab
     * @param Html_Tab $p_html_tab
     */
    function add(Html_Tab $p_html_tab)
    {
        $this->a_tabs[]=clone $p_html_tab;

    }

    /**
     * get the CSS class of tabs
     * @return mixed
     */
    public function get_class_tab()
    {
        return $this->class_tab;
    }

    /**
     * set the CSS class of tabs, default is tabs
     * @param mixed $class_tab
     */
    public function set_class_tab($class_tab)
    {
        $this->class_tab = $class_tab;
        return $this;
    }

    /**
     * get the CSS class of tabs_selected
     * @return mixed
     */
    public function get_class_tab_selected()
    {
        return $this->class_tab_selected;
    }

    /**
     * set the CSS class of tabs, default is tabs_selected
     * @param mixed $class_tab_selected
     */
    public function set_class_tab_selected($class_tab_selected)
    {
        $this->class_tab_selected = $class_tab_selected;
        return $this;
    }

    /**
     * Build the javascript to change the class name of the selected tab, hide other div and show the selected one
     * @param string $p_not_hidden id of the showed tab
     * @return javascript string
     */
    function build_js ($p_not_hidden)
    {
        $r="";
        $nb=count($this->a_tabs);
        for ($i =0 ; $i < $nb;$i++)
        {
            if ($this->get_mode()=="tab") {

                if ( $this->a_tabs[$i]->get_id() != $p_not_hidden) {
                    $r .= sprintf("$('div%s').hide();",$this->a_tabs[$i]->get_id() );
                    $r .= sprintf("$('tab%s').className='%s';",$this->a_tabs[$i]->get_id(),$this->class_tab );
                } else {
                    $r .= sprintf("$('div%s').show();",$p_not_hidden );
                    $r .= sprintf("$('tab%s').className='%s';",$p_not_hidden ,$this->class_tab_selected);

                }
            } elseif ($this->get_mode()=="row") {
                if ( $this->a_tabs[$i]->get_id() != $p_not_hidden) {
                    $r .= sprintf("Effect.BlindUp('div%s',{duration : 0.7});",$this->a_tabs[$i]->get_id() );
                    $r .= sprintf("$('tab%s').className='%s';",$this->a_tabs[$i]->get_id(),$this->class_tab );
                } else {
                    $r .= sprintf("Effect.SlideDown('div%s',{duration : 0.7});",$p_not_hidden );
                    $r .= sprintf("$('tab%s').className='%s';",$p_not_hidden ,$this->class_tab_selected);

                }
            }
        }
        return $r;
    }

    /**
     * When printing row , a comment is written if not empty,
     * @param $p_index
     */
    protected function print_comment($p_index) {
        printf ('<span class="%s"> %s </span>',
            $this->get_class_comment(),
            $this->a_tabs[$p_index]->get_comment()
        );

    }
    /**
     * print the html + javascript code of the tabs and the div
     *
     */
    function output()
    {
        $nb=count($this->a_tabs);
        if ($nb==0)
        {
            return;
        }
        printf('<div class="%s">',$this->class_div);
        printf ( '<ul class="%s">',$this->class_tab_main);
        for ($i=0; $i<$nb; $i++)
        {
            printf ('<li id="tab%s" class="%s">',
                    $this->a_tabs[$i]->get_id(),$this->class_tab);
            switch ($this->a_tabs[$i]->get_mode())
            {
                case 'link':
                    printf ('<a class="%s" id="%s" href="%s">',
                            $this->class_anchor,
                            $this->a_tabs[$i]->get_id(),
                            $this->a_tabs[$i]->get_link());
                    printf ('<span class="title_%s"> %s </span>',
                        $this->get_class_tab(),
                        $this->a_tabs[$i]->get_title()
                        );
                    echo '</a>';

                    break;
                case 'ajax':
                    printf('<a class="%s" id="%s" onclick="%s">', 
                            $this->class_anchor,
                            $this->a_tabs[$i]->get_id(),
                            $this->a_tabs[$i]->get_link());
                    printf ('<span class="title_%s"> %s </span>',
                        $this->get_class_tab(),
                        $this->a_tabs[$i]->get_title()
                        );

                    echo $this->a_tabs[$i]->get_title();
                    echo '</a>';
                    break;
                case 'static':
                    // show one , hide other
                    $script=$this->build_js($this->a_tabs[$i]->get_id());
                    printf('<a class="%s" onclick="%s">', $this->class_anchor,$script);
                    printf ('<span class="title_%s"> %s </span>',
                        $this->get_class_tab(),
                        $this->a_tabs[$i]->get_title()
                        );

                    echo '</a>';
                    
                    break;
                default:
                    throw new Exception('OUTPUTHTMLTAB01');
                    break;
            }
            if ( $this->get_mode()=="row") {
                $this->print_comment($i);
            }
            echo '</li>';
            if ( $this->get_mode()=="row") {
                $this->print_div($i);
            }
        }
        echo '</ul>';
        echo '</div>';
        
        if ( $this->get_mode()=="tab" ) {
            for ($i=0;$i<$nb;$i++)
            {
                $this->print_div($i);
            }

        }
    }
    private function print_div($p_index)
    {
        $class="";
        if ( $this->get_mode() == "row") {
            $class="tab_row";
        }

        printf('<div id="div%s" style="display:none;clear:both" class="%s">',
                            $this->a_tabs[$p_index]->get_id(),
                            $class);
        echo $this->a_tabs[$p_index]->get_content();
        echo '</div>';

    }
}
