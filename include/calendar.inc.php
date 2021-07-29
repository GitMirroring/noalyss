<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt

/**
 * @file
 * @brief show the calendar
 */
if ( ! defined ('ALLOWED') ) die('Appel direct ne sont pas permis');
$http=new HttpInput();
$cal=new Calendar();
$cal->default_periode=(isset ($_GET['in']))?$http->get("in","number"):$g_user->get_periode();

?>
<div id="calendar_zoom_div">
    
<?php echo $cal->display('long',1); ?>
</div>