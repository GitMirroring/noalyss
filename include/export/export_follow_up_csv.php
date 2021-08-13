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

// Copyright Author Dany De Bontridder danydb@aevalys.eu

/**
 * @file
 * @brief export Action Gestion to csv, taking in account the search
 * 
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');

$http=new HttpInput();
$follow=new Follow_Up($cn);
$array=$_GET;

//- export follow up  simple :no comment
if ( $http->get("export_type") == "simple") { 
    header('Pragma: public');
    header('Content-type: application/csv');
    header('Content-Disposition: attachment;filename="action-gestion.csv"',FALSE); 
    echo $follow->export_csv($_GET); 
}

/*
 * export follow up  full : comments
 * Mantis task #0002035
 */
if ( $http->get("export_type") == "detail") { echo $follow->export_csv_detail($_GET); }

exit();

?>
