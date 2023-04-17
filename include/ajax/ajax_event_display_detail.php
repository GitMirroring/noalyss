<?php
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
// Copyright (2023) Author Dany De Bontridder <dany@alchimerys.be>
global $g_user;

if (!defined('ALLOWED'))     die('Appel direct ne sont pas permis');

if  ( $g_user->check_module("DASHBOARD") == 0) die();

global $http,$cn;

try {
    $what=$http->get("what");
} catch (\Exception $e) {
    echo $e->getMessage();
    throw $e;
}

if ( $what == 'main_display')
{
    status_Operation_Event::main_display($cn);
}else {
    $status_operation_event=new Status_Operation_Event($cn);
    $status_operation_event->display($what);
}


