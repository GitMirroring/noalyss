<?php
//@description: Test the class Acc_Ledger_Account
/*
 *   This file is part of NOALYSS.
 *
 *   PhpCompta is free software; you can redistribute it and/or modify
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
 *   along with PhpCompta; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
// Copyright (2016) Author Dany De Bontridder <dany@alchimerys.be>

/**
 * @file
 * @brief 
 * @param type $name Descriptionara
 */

 $_GET=array (
);
$_POST=array (
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
$_REQUEST=array_merge($_GET,$_POST);

require_once NOALYSS_INCLUDE."/class/fiche.class.php";

 $_GET=array (
);
$_POST=array (
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
$_REQUEST=array_merge($_GET,$_POST);
$card_count=$cn->get_array("select count(*),f_id ". 
        " from jrnx ".
        " where ". 
        " f_id is not null ".
        "group by f_id order by count(*) desc");
$a=new Fiche($cn,$card_count[0]['f_id']);
$min=$cn->get_value("select p_id from parm_periode order by p_start asc limit 1");
$max=$cn->get_value("select p_id from parm_periode order by p_start desc limit 1");
printf ("Max période %s Min période %s",$max,$min);
$result=$a->get_row($min,$max);
$result_date=$a->get_row_date('01.01.2010','01.01.2090');

if ( count($result_date) != count($result)) {
    echo "Erreur";
}

echo h1("Fiche->get_row");
var_dump($result);

echo h1("Fiche->get_row_date");

var_dump($result_date);

