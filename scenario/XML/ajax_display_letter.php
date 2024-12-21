<?php
/**
 * @brief test ajax_display_letter.php , display detail of a lettering operation
 */
//@description: test ajax_display_letter.php
$_GET=array (
  'gDossier' => '25',
  'j_id' => '343',
  'obj_type' => 'card',
  'search_start' => '01.01.2019',
  'search_end' => '31.12.2019',
  'op' => 'dl',
);
$_POST=array (
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
 $_REQUEST=array_merge($_GET,$_POST);
echo h1('Raw');
include 'ajax_misc.php';

echo h1('Détail');
ob_start();
include 'ajax_misc.php';
$var=ob_get_contents();
ob_end_clean();


$xml=simplexml_load_string($var);

foreach ($xml as $key=>$value) {
    echo ("key is $key<br>");
    echo '<textarea class="w-100" rows="10">';
    print_r( $value);
    echo '</textarea>';
    echo '<hr>';
    print_r( $value);
    echo '<hr>';


}