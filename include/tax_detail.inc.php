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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 26/07/24
/*! 
 * \file
 * \brief detail of VAT
 */
global $cn,$http;

// display a form for periode + tva_code
Tax_Detail::display_form();

// if display
if ( isset ($_GET['display'])) {

    $tax_detail= new Tax_Detail (
                            $http->get("vat_code"),
                            $http->get("from","date"),
                            $http->get("to","date"),
                            $http->get("p_jrn","number"));
    $tax_detail->button_export_csv();
    $tax_detail->html();
    $tax_detail->button_export_csv();

}
