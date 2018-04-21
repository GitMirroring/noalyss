<?php

/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   PhpCompta is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2018) Author Dany De Bontridder <dany@alchimerys.be>

require_once NOALYSS_INCLUDE.'/lib/noalyss_sql.class.php';
require_once NOALYSS_INCLUDE.'/lib/database.class.php';

/**
 * class_currency_history_sql.php
 *
 * @file
 * @brief abstract of the view public.v_currency_last_value
 */

/**
 * class_currency_history_sql.php
 *
 * @class
 * @brief abstract of the view public.v_currency_last_value
 */
class V_Currency_Last_Value_SQL extends Data_SQL
{

    function __construct(Database $p_cn, $p_id=-1)
    {
        $this->table="public.v_currency_last_value";
        $this->primary_key="currency_id";
        /*
         * List of columns
         */
        $this->name=array(
            "currency_id"=>"currency_id"
            , "cr_name"=>"cr_name"
            , "cr_code_iso"=>"cr_code_iso"
            , "currency_history_id"=>"currency_history_id"
            , "ch_value"=>"ch_value"
            , "str_from"=>"str_from"
        );
        /*
         * Type of columns
         */
        $this->type=array(
            "currency_id"=>"numeric"
            , "cr_name"=>"text"
            , "cr_code_iso"=>"text"
            , "currency_history_id"=>"numeric"
            , "ch_value"=>"numeric"
            , "str_from"=>"text"
        );


        $this->default=array(
            "currency_id"=>"auto"
        );

        $this->date_format="DD.MM.YYYY";
        parent::__construct($p_cn, $p_id);
    }

    public function count($p_where="", $p_array=null)
    {
        $count=$this->cn->get_value("select count(*) from $this->table ".$p_where, $p_array);
        return $count;
    }

    public function delete()
    {
        
    }

    public function exist()
    {
        $pk=$this->primary_key;
        $count=$this->cn->get_value("select count(*) from ".$this->table." where ".$this->primary_key."=$1",
                array($this->$pk));
        return $count;
    }

    public function insert()
    {
        
    }

    public function load()
    {
        $sql=" select ";
        $sep="";
        foreach ($this->name as $key)
        {
            switch ($this->type[$key])
            {
                case "date":
                    $sql.=$sep.'to_char('.$key.",'".$this->date_format."') as ".$key;
                    break;
                default:
                    $sql.=$sep.$key;
            }
            $sep=",";
        }
        $pk=$this->primary_key;
        $sql.=" from ".$this->table;

        $sql.=" where ".$this->primary_key." = $1";

        $result=$this->cn->get_array($sql, array($this->$pk));
        if ($this->cn->count()==0)
        {
            $this->$pk=-1;
            return;
        }

        foreach ($result[0] as $key=> $value)
        {
            $this->$key=$value;
        }
    }

    function seek($cond='', $p_array=null)
    {
        $sql="select * from ".$this->table."  $cond";
        $ret=$this->cn->exec_sql($sql, $p_array);
        return $ret;
    }

    public function update()
    {
        
    }

}
