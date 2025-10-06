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

/*
 * Global variables
 */
global $g_connection,$g_parameter,$g_user,$cn;
if (!defined("DOSSIER"))define ("DOSSIER",25);

$_REQUEST['gDossier'] = DOSSIER;
$g_connection=new \Database(DOSSIER);
$cn=&$g_connection;
$g_parameter = new \Noalyss_Parameter_Folder($g_connection);
$_SESSION[SESSION_KEY.'use_name']='unit test';
$_SESSION[SESSION_KEY.'use_first_name']='automatic';
$_SESSION[SESSION_KEY.'g_user']='admin';
$_SESSION[SESSION_KEY.'g_pass']=md5('dany');
$_SESSION[SESSION_KEY.'g_pagesize']='50';
$_SESSION[SESSION_KEY.'csv_fieldsep']='0';
$_SESSION[SESSION_KEY.'csv_decimal']='1';
$_SESSION[SESSION_KEY.'csv_encoding']='utf8';
$_SESSION[SESSION_KEY.'access_mode']='PC';
$g_user=new Noalyss_User($g_connection);
$_ENV['TMP']="/tmp/";
require_once __DIR__.'/facility.class.php';
