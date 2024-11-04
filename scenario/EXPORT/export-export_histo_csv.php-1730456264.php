<?php 
//@description: export export_histo_csv.php
$_GET=array (
  'gDossier' => '25',
  'ledger_type' => 'ALL',
  'tag_option' => '0',
  'act' => 'CSV:histo',
  'viewsearch' => 'Export vers CSV',
  'qcode' => '',
);
$_POST=array (
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
 $_REQUEST=array_merge($_GET,$_POST);
ob_start();
include NOALYSS_INCLUDE."/export/export_histo_csv.php";
$result=ob_get_contents();
ob_end_clean();
html_page_start();
if (trim($result) != '') {
    echo p('result ok','class="alert-success"');
} else {
    echo p('result failed','class="notice"');
}
echo h1('Tous les journaux');
csv2table($result);


echo h1('Achat 01.01.2019 - 31.12.2019');

$_GET=array (
    'date_paid_start' => '',
    'date_paid_end' => '',
    'date_start' => '01.01.2019',
    'date_end' => '31.12.2019',
    'desc' => '',
    'amount_min' => '0',
    'amount_max' => '0',
    'operation_filter' => 'all',
    'accounting' => '',
    'gDossier' => '25',
    'ledger_type' => 'ACH',
    'search_optag_option' => '0',
    'p_currency_code' => '-1',
    'tva_id_search' => '',
    'tag_option' => '0',
    'act' => 'CSV:histo',
    'viewsearch' => 'Export vers CSV',
    'qcode' => '',
);

$_POST=array (
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
$_REQUEST=array_merge($_GET,$_POST);
ob_start();
include  NOALYSS_INCLUDE."/export/export_histo_csv.php";
$result=ob_get_contents();
ob_end_clean();
html_page_start();
if (trim($result) != '') {
    echo p('result ok','class="alert-success"');
} else {
    echo p('result failed','class="notice"');
}
csv2table($result);


