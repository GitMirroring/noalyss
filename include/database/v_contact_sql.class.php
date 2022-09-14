<?php
/*
 * Copyright (C) 2017 Dany De Bontridder <dany@alchimerys.be>
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
* class_user_filter_sql.php
*
* @file
* @brief abstract of the view public.v_contact
*/

/**
* @class User_filter_SQL
* @brief ORM abstract of the view public.v_contact_sql
*/

class V_Contact_SQL extends Data_SQL
{
    function __construct($p_cn, $p_id = -1)
    {
        $this->table="public.v_contact";
        $this->primary_key="f_id";
        $a_name=explode(",","f_id,contact_fname,contact_name,contact_qcode,contact_company,contact_mobile,contact_phone,contact_email,contact_fax,card_category");
        $this->type=[];
        foreach ($a_name as $key) {
            $this->type[trim($key)]="text";
            $this->name[trim($key)]=$key;
        }
        $this->type['f_id']="number";
        $this->type['card_category']="number";

        parent::__construct($p_cn, $p_id);
    }


    function insert()
    {
      throw new Exception("not implemented");
    }

    function delete()
    {
        throw new Exception("not implemented");
    }

    function update()
    {
        throw new Exception("not implemented");
    }

    function load()
    {
       $array=$this->cn->get_row("select * from ".$this->table." where f_id=$1",array($this->f_id));
       if (empty ($array) ) { $this->f_id=-1;return false;}
        $this->to_row($array);
       return true;
    }

    function seek($cond = '', $p_array = null)
    {
        throw new Exception("not implemented");
    }

    function count($p_where = "", $p_array = null)
    {
        throw new Exception("not implemented");
    }

    function exist()
    {
        throw new Exception("not implemented");
    }
}