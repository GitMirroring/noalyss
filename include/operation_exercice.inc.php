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
    Operation_Exercice::list_draft();
    return;

}
if ( $http->request("sa","string" ) == "remove" )
{
    Operation_Exercice::delete($http->post("operation_list"));
    Operation_Exercice::input_source();
    Operation_Exercice::list_draft();
    return;
}
if ( $http->request("sa") == "opening")
{

    $operation_exercice_id= $http->get("operation_exercice_id","number",-1);
    $operation_opening=new Operation_Opening($operation_exercice_id);
    try {
        if ( $operation_exercice_id == -1 ) {
            // take data from request
            $operation_opening->from_request();
            $operation_opening->insert();
        }
        // Display result
        $operation_opening->display_result();
        $operation_opening->input_transfer();
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
    return;
}
if ( $http->request("sa") == "closing")
{

    $operation_exercice_id= $http->get("operation_exercice_id","number",-1);
    $operation_closing=new Operation_Closing($operation_exercice_id);
    try {
        if ( $operation_exercice_id == -1 ) {
            // take data from request
            $operation_closing->from_request();
            $operation_closing->insert();
        }
        // Display result
        $operation_closing->display_result();
        $operation_closing->input_transfer();
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
    return;
}
