<?php
/*
 *   This file is part of NOALYSS.
 *    NOALYSS is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    NOALYSS is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with NOALYSS; if not, write to the Free Software
 *    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *    Copyright Author Dany De Bontridder danydb@noalyss.eu
 *
 */


/**
 *\file
  * \brief Html Input for color
 */


class IColor extends HtmlInput
{
    function __construct($p_name = "", $p_value = "", $p_id = "")
    {
        parent::__construct($p_name, $p_value, $p_id);

    }
    function input()
    {
        $this->id=(empty($this->id))?$this->name:$this->id;
        $ret = sprintf( '<input type="color" id="%s" name="%s" value="%s">',
            $this->id,$this->name,$this->value);
        return $ret;
    }
    function display()
    {
        return "";
    }

}