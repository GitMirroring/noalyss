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
// Copyright Author Dany De Bontridder danydb@aevalys.eu 9/08/24
/*! 
 * \file
 * \brief activate a javascript for a PROFILE  , receive mecode (menu_ref.me_code) , prid , (profile.p_id)
 * and activate(true/false)
 */

if (!defined('ALLOWED'))
    die('Appel direct ne sont pas permis');

global $g_user;

if ($g_user->check_module('CFGPLUGIN') == 0) die();

try {
    $me_code = $http->get("mecode");
    $pr_id = $http->get("prid", "number");
    $activate = $http->get("activate");
    $depend = $http->get("dep",'string','EXT');
    $order = $http->get("ord",'number',0);
} catch (\Exception $e) {
    echo $e->getMessage();
}

$extension=new \Extension($cn,$me_code);
if ( $activate=='true') {
    // if depend does not exist then message error
    try {
        $extension->depend=$depend;
        $extension->order=$order;
        $extension->insert_profile_menu($pr_id);
        echo 'OK';

    } catch (\Exception $e) {
        echo $e->getMessage();
    }
} elseif ($activate=='false') {
    $extension->remove_from_profile_menu($pr_id);
    echo 'OK';
}
