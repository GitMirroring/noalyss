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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 6/01/24
/*! 
 * \file
 * \brief For opening or closing exercice , operation needed
 */

$http=new HttpInput();
if ( $http->request("sa","string","") == "" )
{
    Operation_Exercice::input_source();
    return;
}
if ( $http->request("sa") == "opening")
{

    $operation_exercice_id= $http->get("operation_exercice_id","number",-1);
    $operation_opening=new Operation_Opening($operation_exercice_id);
    try {
        // take data from request
        $operation_opening->from_request();
        if ( $operation_exercice_id == -1 ) {
            $operation_opening->insert();
        }
        // Display result
        $operation_opening->display_result();

    } catch (\Exception $e) {
        echo $e->getMessage();
    }
    return;
}