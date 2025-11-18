<?php

/*
 *   This file is part of NOALYSS.
 *
 *   NOALYSS is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   NOALYSS is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file
 * @brief    find and select a VATEX code : VAT Exemption code mandatory for PEPPOL
 */
try {
    $select_code=$http->get("select_code","string","");
    $dgbox=$http->get("dgbox");
    
} catch (Exception $exc) {
    echo $exc->getTraceAsString();
}


if ($select_code == "") 
{
   
    require_once NOALYSS_TEMPLATE."/ajax-search_vatex.php";
    
    
    return;
}

//------------------------------------------------
// a VATEX Code has been selected
//------------------------------------------------
if ($select_code != "")
{
    $row=$cn->get_row("select vx_code,vx_code_name,vx_description,vx_remark  from vatex_code  where vx_code=$1",
        [$select_code]);
    $answer=array();
    if ( ! empty ($row))
    {
    $answer['vx_code']=$row['vx_code'];
    $answer['vx_value']=$row['vx_code'];
    $answer['vx_description']=$row['vx_description'].span($row['vx_remark'],' class="text-muted" ');
    }else {
        $answer['vx_code']=$answer['vx_value']=$answer['vx_description']="";
    }
    echo json_response($answer);
    return;
}
