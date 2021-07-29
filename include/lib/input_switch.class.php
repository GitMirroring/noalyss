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
 * @brief show a switch, when you click on it an hidden field is changed, the value is 1 or 0 
 */
/**
 * @class InputSwitch
 * @brief show a switch, when you click on it an hidden field is changed, the value is 1 or 0
 */

class InputSwitch extends HtmlInput
{

    var $icon;
    var $value_container;
    var $value;

    function __construct($name='', $value='', $p_id="")
    {
        $this->value_container=$name;
        $this->value=$value;
        $this->icon=$p_id;
        $this->javascript="";
    }

    function input($p_name=NULL, $p_value=NULL)
    {
        $r="";
        if ($p_name!=NULL)
            $this->value_container=$p_name;
        if ($p_value!==NULL)
            $this->value=$p_value;

        if ( $this->icon=="") $this->icon=uniqid ("inputSwitch");
        
        if  ( trim($this->value_container) ==="" || trim($this->value) === "") {
             throw new Exception(_("Valeur invalide"),1);
        }
        if ($this->readOnly == TRUE) {
            return $this->display();
        }
        $r.= HtmlInput::hidden($this->value_container, $this->value);
        
        $this->javascript=sprintf('toggle_onoff(\'%s\',\'%s\');%s;',$this->icon,$this->value_container,$this->javascript);
        
        if ($this->value=='1') {
            $r.=Icon_Action::iconon($this->icon, $this->javascript);
        } else {
            $r.=Icon_Action::iconoff($this->icon, $this->javascript);
        }
        return $r;
    }
    function display()
    {
        if ($this->value=='1') {
            return  Icon_Action::iconon($this->icon, "");
        } else {
            return Icon_Action::iconoff($this->icon, "");
        }
    }

}
