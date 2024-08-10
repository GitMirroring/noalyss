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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 27/07/24
/*! 
 * \file
 * \brief  export in CSV detail of tax
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

$http=new \HttpInput();

try {
    $tax_detail=new Tax_Detail(
        $http->get("vat_code"),
        $http->get("from"),
        $http->get("to"),
        $http->get("p_jrn")
    );
} catch (\Exception $e) {
    echo $e->getMessage();
    return;
}

$tax_detail->csv();