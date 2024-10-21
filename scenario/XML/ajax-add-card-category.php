<?php 
//@description:st montre choix des catégories de fiches 
$_GET=array (
  'gDossier' => '42',
  'ctl' => 'select_card_div',
  'op' => 'card',
  'op2'=>'st',
  'fil' => '-1',
  'ledger' => '2',
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
 $_REQUEST=array_merge($_GET,$_POST);
 define ('TEST_UNIT',1);
ob_start();
 include NOALYSS_HOME.'/ajax_misc.php';
$output=ob_get_clean();
echo '<HR>';
$xml=simplexml_load_string($output);
print "ctl {$xml->ctl}";
echo '<hr>';
print "code ";
print '<div style="width:60%;margin-left:20%">';
print_r($xml->code);
echo '</div>';
echo '<code>';
echo htmlentities($xml->code);
echo '</code>';

echo '<hr>';
print "fiche_cat ";
print_r($xml->fiche_cat);
echo '<hr>';

