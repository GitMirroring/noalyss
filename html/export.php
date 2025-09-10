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

/*!\file
 * \brief manage all the export to CSV or PDF
 *   act can be
 *
 */
define ('ALLOWED',1);
require_once '../include/constant.php';
require_once NOALYSS_INCLUDE.'/lib/ac_common.php';
MaintenanceMode("block.html");

global $g_user,$cn,$g_parameter;
require_once NOALYSS_INCLUDE.'/class/database.class.php';
require_once NOALYSS_INCLUDE . '/class/noalyss_user.class.php';
require_once NOALYSS_INCLUDE.'/lib/http_input.class.php';

//
// for loading javascripts or style-sheet, it is needed to know the user 
// global preference, but the user is not yet connected
// to a folder. So the Database is the repository
if ( defined("noalyss_user") || defined("phpcompta_user")) {
    $g_user=new Noalyss_user(new Database());
    set_language();
}
// load message for javascript
if (isset ($_REQUEST['loadjs']) && $_REQUEST['loadjs']=='message')
{
    header('Content-Type: text/javascript');
    include_once NOALYSS_INCLUDE."/lib/message_javascript.php";
    return;
}
// Connect the user to the current folder and export file
$cn=Dossier::connect();
$g_user=new Noalyss_user($cn);
$gDossier=dossier::id();
$g_parameter=new Noalyss_Parameter_Folder($cn);
mb_internal_encoding("UTF-8");
$g_user->Check();
/**
 * check if 2FA is completed
 */
if ( ! $g_user->is_double_identified()) {
   exit();
}
$action=$g_user->check_dossier($gDossier);

$hi=new HttpInput();
$action=$hi->get("act");

if ( $action=='X'  || $g_user->check_print($action)==0 )
  {
    echo alert(_('Accès interdit'));    
    redirect("do.php?".dossier::get());
    exit();
  }
// get file and execute it

 $prfile=$cn->get_value("select me_file from menu_ref where me_code=$1",array($action));

if ( LOGINPUT)
{
    $file_loginput=fopen($_ENV['TMP'].'/export-'.$prfile.'-'.$_SERVER['REQUEST_TIME'].'.php','a+');
    fwrite ($file_loginput,"<?php \n");
    fwrite ($file_loginput,'//@description: export '.$prfile."\n");
    fwrite($file_loginput, '$_GET='.var_export($_GET,true));
    fwrite($file_loginput,";\n");
    fwrite($file_loginput, '$_POST='.var_export($_POST,true));
    fwrite($file_loginput,";\n");
    fwrite($file_loginput, '$_POST[\'gDossier\']=$gDossierLogInput;');
    fwrite($file_loginput,"\n");
    fwrite($file_loginput, '$_GET[\'gDossier\']=$gDossierLogInput;');
    fwrite($file_loginput,"\n");
    fwrite($file_loginput,' $_REQUEST=array_merge($_GET,$_POST);');
    fwrite($file_loginput,"\n");
    fclose($file_loginput);
}

 if ( $prfile == "" || !file_exists(NOALYSS_INCLUDE."/export/$prfile")) {
     print $action;
     die (_('Export impossible'));
 }
 require_once NOALYSS_INCLUDE."/export/$prfile";
 ?>