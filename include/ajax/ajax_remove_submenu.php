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

// Copyright 2015 Author Dany De Bontridder danydb@aevalys.eu
/**
*@file
*@brief remove a submenu
*/


if ( ! defined ('ALLOWED') ) die(_('Non autorisé'));
$http=new HttpInput();

// Security 
if ($g_user->check_module('C0PROFL')==0)
    die();
try
{
    
    $p_profile_menu_id=$http->get('p_profile_menu_id', "number");
}
catch (Exception $exc)
{
    record_log($exc);
    return;
}

// Delete menu  + children
$cn->exec_sql('delete from profile_menu where pm_id = $1 or pm_id_dep=$1',array($p_profile_menu_id));

// remove children  without parent
$cn->exec_sql("delete from profile_menu "
        . " where pm_id_dep is not null "
        . "       and pm_id_dep not  in (select pm_id from profile_menu)");
?>        
