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
// Copyright (2002-2021) Author Dany De Bontridder <danydb@noalyss.eu>

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

/**
 * @file
 * @brief test Database Core
 */
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;

$cn=Dossier::connect();

echo h2(" Status IDLE");
echo $cn->status();

echo h2(" Status in begin ");
echo $cn->start();
echo $cn->status ();
echo "<br>";
$cn->get_value("select count(*) from jrnx");
echo $cn->status ();
    echo PGSQL_TRANSACTION_INTRANS;    
echo h2(" Status after rollback ");
echo $cn->commit();
echo $cn->status ();
