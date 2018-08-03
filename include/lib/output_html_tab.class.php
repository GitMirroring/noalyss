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

    /**
     *@example html_tab.test.php
     */
    function __construct()
    {
        $this->a_tabs=[];
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
            if ( $this->a_tabs[$i]->get_id() != $p_not_hidden) {
                $r .= sprintf("$('div%s').hide();",$this->a_tabs[$i]->get_id() );
                $r .= sprintf("$('tab%s').className='tabs';",$this->a_tabs[$i]->get_id() );
            } else {
                $r .= sprintf("$('div%s').show();",$p_not_hidden );
                $r .= sprintf("$('tab%s').className='tabs_selected';",$p_not_hidden );
                
            }
        }
        return $r;
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
        echo '<ul class="tabs">';
        for ($i=0; $i<$nb; $i++)
        {
            printf ('<li id="tab%s" class="tabs">',
                    $this->a_tabs[$i]->get_id());
            switch ($this->a_tabs[$i]->get_mode())
            {
                case 'link':
                    printf ('<a id="%s" href="%s">',
                            $this->a_tabs[$i]->get_id(),
                            $this->a_tabs[$i]->get_link());
                    echo $this->a_tabs[$i]->get_title();
                    echo '</a>';

                    break;
                case 'ajax':
                    printf('<a id="%s" onclick="%s">', 
                            $this->a_tabs[$i]->get_id(),
                            $this->a_tabs[$i]->get_link());
                    echo $this->a_tabs[$i]->get_title();
                    echo '</a>';
                    break;
                case 'static':
                    // show one , hide other
                    $script=$this->build_js($this->a_tabs[$i]->get_id());
                    printf('<a onclick="%s">', $script);
                    echo $this->a_tabs[$i]->get_title();
                    echo '</a>';
                    
                    break;
                default:
                    break;
            }
            echo '</li>';
        }
        echo '</ul>';
        for ($i=0;$i<$nb;$i++)
        {
            printf('<div id="div%s" style="display:none;clear:both">',$this->a_tabs[$i]->get_id());
            echo $this->a_tabs[$i]->get_content();
            echo '</div>';
        }
    }

}
