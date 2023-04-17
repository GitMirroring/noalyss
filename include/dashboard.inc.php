<?php
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
/**
 *@file
 *@brief Dashboard
 */
require_once  NOALYSS_INCLUDE.'/constant.php';
require_once  NOALYSS_INCLUDE.'/lib/ac_common.php';
require_once  NOALYSS_INCLUDE.'/lib/user_menu.php';

echo '<div class="content">';
global $g_user;
/* others report */
$cal=new Calendar();
$cal->get_preference();

$obj=sprintf("{gDossier:%d,invalue:'%s',outdiv:'%s','distype':'%s'}",
        dossier::id(),'per','calendar_zoom_div','cal');


require_once NOALYSS_TEMPLATE.'/dashboard.php';


?>
