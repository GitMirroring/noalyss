#!/usr/bin/env php

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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 9/08/23
/*! 
 * \file
 * \brief test the sql patch , must be executed in command line
 */
function help()
{
    print <<<EOF
php test-patch.php -d noalyss_folder -b database_id -p file
Let you try a patch to a database
    -d give the full path to noalyss folder
    -b the number of the database (ac_dossier.dos_id)        
    -p SQL filename of the script to execute , path is relative to noalyss directory
    
Example :
	php test-patch.php -d /home/yourself/develop/noalyss -b 25 -p include/sql/patch/upgrade175.sql
    
EOF;
}

define("NOALYSS_URL", "cmd_line");
$option = getopt("d:b:p:x");

//var_dump($option)1;

try {

    $directory = $option['d'];
    if (empty ($directory) || empty($option)) {
        throw new Exception ("No Noalyss directory given");
    }


    require_once "$directory/include/constant.php";
    if (empty($option['b']) || empty($option['p'])) {
        throw new Exception ("missing mandatory paramater");
    }
    $dos_id = $option['b'];
    $_REQUEST['gDossier']=$dos_id;
    $database=new Database($dos_id);
    $dossier=new Dossier($dos_id);

    $sql_file = "$directory".DIRECTORY_SEPARATOR.$option['p'];

    echo "Dossier is ".$dossier->name().PHP_EOL;
    echo "filename is $sql_file".PHP_EOL;

    $database->execute_script($sql_file);

} catch (Exception $e) {
    help();
    echo "error  = ",$e->getMessage().PHP_EOL;
}



