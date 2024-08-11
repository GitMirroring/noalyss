<?php
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
/**
 *@file
 *@brief Dashboard
 */
require_once NOALYSS_TEMPLATE.'/dashboard.php';


echo \HtmlInput::button_action(_('Elements du tableau de bord'), "widget.manage()");


?>
