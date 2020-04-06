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

require_once NOALYSS_INCLUDE.'/lib/html_tab.class.php';

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
    /**
     *@example html_tab.test.php
     */
    function __construct()
    {
        $this->a_tabs=[];
        $this->class_tab="tabs";
        $this->class_tab_selected="tabs_selected";
        $this->set_mode("tab");
    }

    /**
     * get the mode , possible value are row or tabs
     * @return mixed
     */
    public function get_mode()
    {
        return $this->mode;
        return $this;
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
        return $this;
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
        return $this;
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
     * When printing row , a comment is written if not empty
     * @param $p_index
     */
    protected function print_comment($p_index) {
        printf ('<span class="%s"> %s </span>',
            $this->get_class_tab(),
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
        printf ( '<ul class="%s">',$this->class_tab);
        for ($i=0; $i<$nb; $i++)
        {
            printf ('<li id="tab%s" class="%s">',
                    $this->a_tabs[$i]->get_id(),$this->class_tab);
            switch ($this->a_tabs[$i]->get_mode())
            {
                case 'link':
                    printf ('<a id="%s" href="%s">',
                            $this->a_tabs[$i]->get_id(),
                            $this->a_tabs[$i]->get_link());
                    printf ('<span class="title_%s"> %s </span>',
                        $this->get_class_tab(),
                        $this->a_tabs[$i]->get_title()
                        );
                    echo '</a>';

                    break;
                case 'ajax':
                    printf('<a id="%s" onclick="%s">', 
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
                    printf('<a onclick="%s">', $script);
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
