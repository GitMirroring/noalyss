<?php

/*
 *   This file is part of PhpCompta.
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
// Copyright (2016) Author Dany De Bontridder <dany@alchimerys.be>

/**
 * @file
 * @brief 
 */
class HttpInput
{
    private $array;
    function _construct()
    {
        $this->array=null;
    }

    function check_type($p_name, $p_type)
    {
        try
        {
            // no check on string
            if ( $p_type=="string") return;
            if ( $p_type=="number" 
                 && isNumber($this->array[$p_name]) == 0
                 )                     
                throw new Exception(_("Valeur invalide")."[ $p_name ] = {$this->array[$p_name]}"
                    ,EXC_PARAM_TYPE);
            if ( $p_type=="date") return;
        }
        catch (Exception $ex)
        {
            throw $ex;
        }
    }

    function get_value($p_name, $p_type="string", $p_default="")
    {
        try
        {
            if (func_num_args()==3)
            {
                if (isset($this->array[$p_name]))
                {
                    $this->check_type($p_name, $p_type);
                    return $this->array[$p_name];
                }
                else
                {
                    return $p_default;
                }
            }
            if ( ! isset ($this->array[$p_name])) {
                throw new Exception(_('Paramètre invalide')."[$p_name]",EXC_PARAM_VALUE);
            }
            $this->check_type($p_name, $p_type);
            return $this->array[$p_name];
        }
        catch (Exception $e)
        {
            throw $e;
        }
    }

    function get($p_name, $p_type="string", $p_default="")
    {
        try
        {
            $this->array=$_GET;
            if (func_num_args()==1)  return $this->get_value($p_name);
            if (func_num_args()==2)  return $this->get_value($p_name,$p_type);
            if (func_num_args()==3)  return $this->get_value($p_name,$p_type,$p_default);
            
        }
        catch (Exception $exc)
        {
            throw $exc;
        }

    }
    function post($p_name, $p_type="string", $p_default="")
    {
        try
        {
            $this->array=$_POST;
            if (func_num_args()==1)  return $this->get_value($p_name);
            if (func_num_args()==2)  return $this->get_value($p_name,$p_type);
            if (func_num_args()==3)  return $this->get_value($p_name,$p_type,$p_default);
            
        }
        catch (Exception $exc)
        {
            throw $exc;
        }

        
    }

    function request($p_name, $p_type="string", $p_default="")
    {
        try
        {
            $this->array=$_REQUEST;
            if (func_num_args()==1)  return $this->get_value($p_name);
            if (func_num_args()==2)  return $this->get_value($p_name,$p_type);
            if (func_num_args()==3)  return $this->get_value($p_name,$p_type,$p_default);
        }
        catch (Exception $exc)
        {
            throw $exc;
        }
    }
    function extract($p_array,$p_name, $p_type="string", $p_default="")
    {
        try
        {
            $this->array=$p_array;
            if (func_num_args()==1)  return $this->get_value($p_name);
            if (func_num_args()==2)  return $this->get_value($p_name,$p_type);
            if (func_num_args()==3)  return $this->get_value($p_name,$p_type,$p_default);
            
        }
        catch (Exception $exc)
        {
            throw $exc;
        }

    }
}

?>
