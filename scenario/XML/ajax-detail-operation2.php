<?php 
//@description:FIN Detail operation
$_GET=array (
    'op' => 'ledger',
  'act' => 'de',
  'jr_id' => '147',
  'div' => 'det2',
);
$_POST=array (
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
 $_REQUEST=array_merge($_GET,$_POST);
define ('TEST_UNIT',1);
ob_start();
include 'ajax_misc.php';
$output=ob_get_contents();
ob_clean();
echo unescape_xml($output);
echo htmlentities($output);
