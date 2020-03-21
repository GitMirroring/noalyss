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
 * @brief Tab element
 *
 */

/**
 * Tab Element
 * @see Output_Html_Tab
 */
class Html_Tab
{
    private $id; //<! DOM ID of the tabs (used for the tab and the DIV)
    private $title; //!< Title of the tab
    private $content ; //!< static content of the tab
    private $mode ; //!< possible values are static if the content is static, ajax for calling an ajax, link for a html link, default static
    private $link; //!<  the javascript or an html link, depending of the $mode
    private $comment; //!< comment to add when we use the row
    /**
     *@example html_tab.test.php
     */
    function __construct($p_id,$p_title)
    {
        $this->id=$p_id;
        $this->title=$p_title;
        $this->mode='static';
        $this->comment="";
    }

    /**
     * @return string
     */
    public function get_comment()
    {
        return $this->comment;
        return $this;
    }

    /**
     * @param string $comment
     */
    public function set_comment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    public function get_id()
    {
        return $this->id;
    }

    public function get_title()
    {
        return $this->title;
    }

    public function get_content()
    {
        return $this->content;
    }

    public function set_id($id)
    {
        $this->id=$id;
        return $this;
    }

    public function set_title($title)
    {
        $this->title=$title;
        return $this;
    }

    public function set_content($content)
    {
        $this->content=$content;
        return $this;
    }
    public function get_link()
    {
        return $this->link;
    }

    public function set_link($link)
    {
        $this->link=$link;
        return $this;
    }

    public function get_mode()
    {
        return $this->mode;
    }

    public function set_mode($mode)
    {
        if ( $mode != 'static' && $mode != 'ajax' && $mode != 'link') {
            throw new Exception(_("Mode invalide"));
        }
        $this->mode=$mode;
        return $this;
    }
    
}
