<?php 
//@description:C0PCMN Plan comptable
$_GET=array (
  'gDossier' => '42',
  'ac' => 'PARAM/C0PCMN',
);
$_POST=array (
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
 $_REQUEST=array_merge($_GET,$_POST);
include 'param_pcmn.inc.php';
