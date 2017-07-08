<?php
//This file is part of NOALYSS and is under GPL 
//see licence.txt

if ( !defined ('ALLOWED') )  die('Appel direct ne sont pas permis');

require_once NOALYSS_INCLUDE.'/class/tag.class.php';
$tag=new Tag($cn);
$tag->save($_GET);

?>
