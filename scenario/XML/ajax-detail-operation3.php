<?php 
//@description:de Detail VEN
$_GET=array (
    'op' => 'ledger',
  'act' => 'de',
  'jr_id' => '213',
  'div' => 'det2',
);
$_POST=array (
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
ob_start();
include 'ajax_misc.php';
$output=ob_get_contents();
ob_clean();
echo unescape_xml($output);
echo htmlentities($output);
