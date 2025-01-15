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

/**
 * @file
 * @brief only for mobile device
 */

if ( !defined ("ALLOWED") ) { define ('ALLOWED',true); }

require_once '../include/constant.php';
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
MaintenanceMode("block.html");

global $g_user;

$cn=new Database();
$g_user=new \Noalyss_User($cn);
$g_user->check();

//-----------------------------------------------------------------
/// if $_REQUEST['gDossier'] is not set then select the folder
//-----------------------------------------------------------------
$http=new HttpInput();

$dossier_id=$http->request("gDossier","number",-1);
if ($dossier_id === -1) {
    // count the available folder
    $cnt_folder=$g_user->get_available_folder();
    
    // if there is no available folder , then exit
    if ($cnt_folder==0) {
        echo _("Aucun dossier disponible");
        redirect(NOALYSS_URL."/index.php", 3);
        return;
    }
    if (count($cnt_folder ) == 1) {
        // if only one folder available , connect to it
        $dossier_id=$cnt_folder[0]['dos_id'];
        put_global(array(['key'=>'gDossier',"value"=>$dossier_id]));
    } else {
        $mobile=new \Noalyss\Mobile();
        //-----------------------------------------------------------------
        // --- load the javascript and start a page ----------------------
        //-----------------------------------------------------------------
        $mobile->page_start();
        // propose to select the available folder
        echo '<ul class="nav bg-light flex-column">';
        foreach ($cnt_folder as $folder) 
        {
            echo '<li>';
            echo '<a class="nav-item nav-link " href="mobile.php?gDossier='.$folder['dos_id'].'">';
            echo $folder['dos_id']." ".$folder['dos_name']." ".$folder["dos_description"];
            echo '</a>';
            echo '</li>';
        }
        echo '</ul>';
        return;
    }
    
}
// we are connected to a folder 
global $g_user,$cn;
$cn=Dossier::connect();
$g_user->setDb($cn);

global $g_parameter;
$g_parameter=new Noalyss_Parameter_Folder($cn);
$mobile=new \Noalyss\Mobile();

$ac=trim($http->request("ac","string",""));

//-----------------------------------------------------------------
/// If a module is selected the execute it
//-----------------------------------------------------------------
if ( $ac !== "" && $g_user->check_module($ac) == 1) {
    
    // if $ac is in the mobile profile then execute it
    $mobile->execute_menu($ac);
} else {
//-----------------------------------------------------------------
/// inside a folder , propose a menu 
//-----------------------------------------------------------------
     $mobile->page_start();
    $mobile->display_menu();
}
