<?php
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
/**
 *@file
 *@brief Dashboard
 */
require_once NOALYSS_TEMPLATE.'/dashboard.php';


echo \HtmlInput::button_action(_('Personnaliser le tableau de bord'), "widget.manage()");


?>
<script>
var widget=new Widget('<?=\Dossier::id()?>')
</script>
