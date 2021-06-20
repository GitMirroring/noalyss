<?php

/*
 * * Copyright (C) 2019 Dany De Bontridder <dany@alchimerys.be>
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
 * Aut
 * 
 */

// Copyright Author Dany De Bontridder danydb@noalyss.eu

/**
 * @file
 * @brief modern checkbox, using a javascript to change the icon when clicked and a hidden field, there is always a 
 * value , either 1 or 0 
 */

class InputCheckBox extends HtmlInput
{

    var $icon;
    var $value_container;
    var $value;
    /**
     * Construct an input_checkbox object 
     * 
     * @param string $name of the hidden 
     * @param bool $value Value is 1 or 0
     * @param string $p_id id of the hidden
     */
    function __construct($name='', $value='', $p_id="")
    {
        $this->value_container=$name;
        $this->value=$value;
        
        $this->id_hidden=$p_id;
        $this->id_icon=uniqid($p_id);
        $this->javascript="";
        $this->classrange="";
    }

    function input($p_name=NULL, $p_value=0)
    {
        $r="";
        if ($p_name!=NULL)
            $this->value_container=$p_name;
        if ($p_value!==0)
            $this->value=$p_value;

        
        if  ( trim($this->value_container) ==="" || trim($this->value) === "") {
             throw new Exception(_("Valeur invalide"),1);
        }
        if ($this->readOnly == TRUE) {
            return $this->display();
        }
        $r.= HtmlInput::hidden($this->value_container, $this->value,$this->id_hidden);
        
        $this->javascript=sprintf('toggle_checkbox_onoff(\'%s\',\'%s\');%s;',
                $this->id_icon,
                $this->id_hidden,
                $this->javascript);
        
        if ($this->value=='1') {
            $r.=Icon_Action::checked($this->id_icon,$this->javascript,$this->classrange);
        } else {
            $r.=Icon_Action::unchecked($this->id_icon,$this->javascript,$this->classrange);
        }
        return $r;
    }
    function display()
    {
        if ($this->value=='1') {
            return  Icon_Action::checked($this->id_icon, "");
        } else {
            return Icon_Action::unchecked($this->id_icon, "");
        }
    }
    
    /**
     * click several checkbox when you shift click on that name
     */ 
    function click_range()
    {
        
    }

}