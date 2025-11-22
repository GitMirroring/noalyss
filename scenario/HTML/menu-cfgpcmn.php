<?php 
//@description:C0PCMN
$_GET=array (
  'ac' => 'PARAM/C0PCMN',
  'p_start' => '4',
  'gDossier' => '42',
);
$_POST=array (
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
 $_REQUEST=array_merge($_GET,$_POST);
include 'param_pcmn.inc.php';
