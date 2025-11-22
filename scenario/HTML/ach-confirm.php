<?php 
//@description:ACH Confirmation Achat
$_GET=array (
  'gDossier' => '42',
  'ac' => 'COMPTA/MENUACH/ACH',
);
$_POST=array (
  'gDossier' => '42',
  'e_client' => 'FOURNI1',
  'nb_item' => '1',
  'p_jrn' => '3',
  'e_comm' => 'Loyer',
  'e_date' => '01.01.2017',
  'e_ech' => '',
  'jrn_type' => 'ACH',
  'e_pj' => 'ACH1',
  'e_pj_suggest' => 'ACH1',
  'mt' => '141599523a8.7737',
  'e_mp' => '0',
  'e_march0' => 'LOYER',
  'e_march0_price' => '2560',
  'e_march0_tva_id' => '4',
  'e_march0_tva_amount' => '0',
  'e_quant0' => '1',
  'bon_comm' => '',
  'other_info' => '',
  'ac' => 'COMPTA/MENUACH/ACH',
  'opd_name' => 'Paiement loyer',
  'od_description' => 'Paiement du loyer',
  'record' => 'Enregistrement',
    'p_currency_rate'=>1 ,
    'p_currency_code'=>'0'
);
$_POST['gDossier']=$gDossierLogInput;
$_GET['gDossier']=$gDossierLogInput;
 $_REQUEST=array_merge($_GET,$_POST);

html_page_start();
include 'compta_ach.inc.php';
